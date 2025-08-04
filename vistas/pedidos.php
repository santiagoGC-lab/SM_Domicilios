<?php
// --- Verificación de permisos y conexión a la base de datos ---
require_once '../servicios/verificar_permisos.php';
verificarAcceso('pedidos');

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios;charset=utf8mb4", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// --- Estadísticas para las tarjetas del tablero ---
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente' AND movido_historico = 0")->fetchColumn();
$completedTodayPedidos = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE() AND movido_historico = 0")->fetchColumn() ?? 0;
$completedTodayArchivados = $pdo->query("SELECT COUNT(*) FROM historico_pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()")->fetchColumn() ?? 0;
$completedToday = $completedTodayPedidos + $completedTodayArchivados;
// Calcular ingresos del día sumando pedidos entregados activos y archivados
$revenueTodayPedidos = $pdo->query("SELECT SUM(total) FROM pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()")->fetchColumn() ?? 0.00;
$revenueTodayArchivados = $pdo->query("SELECT SUM(total) FROM historico_pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()")->fetchColumn() ?? 0.00;
$revenueToday = $revenueTodayPedidos + $revenueTodayArchivados;

// --- Obtener pedidos recientes para mostrar en la tabla ---
$stmt = $pdo->query("
    SELECT p.id_pedido, c.nombre AS cliente, c.documento, d.nombre AS domiciliario, p.estado, p.fecha_pedido, p.id_cliente, p.id_domiciliario, p.id_zona, p.cantidad_paquetes, p.total, p.tiempo_estimado
    FROM pedidos p
    LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
    LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
    WHERE p.movido_historico = 0
    ORDER BY p.fecha_pedido DESC
    LIMIT 5
");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Obtener clientes, zonas y domiciliarios para el formulario de pedidos ---
$clients = $pdo->query("SELECT id_cliente, nombre, documento FROM clientes WHERE estado = 'activo'")->fetchAll(PDO::FETCH_ASSOC);
$zones = $pdo->query("SELECT id_zona, nombre, tarifa_base FROM zonas WHERE estado = 'activo'")->fetchAll(PDO::FETCH_ASSOC);
$domiciliarios = $pdo->query("SELECT id_domiciliario, nombre FROM domiciliarios WHERE estado = 'disponible'")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Gestión de Pedidos</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="stylesheet" href="../componentes/pedidos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <?php // Menú lateral, muestra opciones según permisos del usuario 
            ?>
            <?php if (tienePermiso('dashboard')): ?>
                <a href="dashboard.php" class="menu-item"><i class="fas fa-tachometer-alt"></i><span class="menu-text">Inicio</span></a>
            <?php endif; ?>
            <?php if (tienePermiso('pedidos')): ?>
                <a href="pedidos.php" class="menu-item active">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="menu-text">Pedidos</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('coordinador')): ?>
                <a href="coordinador.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span class="menu-text">Coordinador</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('clientes')): ?>
                <a href="clientes.php" class="menu-item"><i class="fas fa-users"></i><span class="menu-text">Clientes</span></a>
            <?php endif; ?>
            <?php if (tienePermiso('domiciliarios')): ?>
                <a href="domiciliarios.php" class="menu-item"><i class="fas fa-motorcycle"></i><span class="menu-text">Domiciliarios</span></a>
            <?php endif; ?>
            <?php if (tienePermiso('vehiculos')): ?>
                <a href="vehiculos.php" class="menu-item">
                    <i class="fas fa-car"></i>
                    <span class="menu-text">Vehiculos</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
                <a href="zonas.php" class="menu-item"><i class="fas fa-map-marked-alt"></i><span class="menu-text">Zonas de Entrega</span></a>
            <?php endif; ?>
            <?php if (tienePermiso('reportes')): ?>
                <a href="reportes.php" class="menu-item"><i class="fas fa-chart-bar"></i><span class="menu-text">Reportes</span></a>
            <?php endif; ?>
            <?php if (esAdmin()): ?>
                <a href="tabla_usuarios.php" class="menu-item"><i class="fas fa-users-cog"></i><span class="menu-text">Gestionar Usuarios</span></a>
            <?php endif; ?>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2>Gestión de Pedidos</h2>
            </div>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar pedidos..." id="searchInput">
            </div>
            <div class="action-buttons">
                <button class="btn-login" id="btnAddPedido" onclick="abrirModalNuevoPedido()">
                    <i class="fas fa-plus"></i> Nuevo Pedido
                </button>
            </div>
        </div>

        <div class="dashboard-cards">
            <!-- Tarjetas de resumen: pendientes, completados hoy e ingresos del día -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-clock"></i></div>
                    <h3 class="card-title">Pedidos Pendientes</h3>
                </div>
                <div class="card-value"><?php echo $pendingOrders; ?></div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                    <h3 class="card-title">Completados Hoy</h3>
                </div>
                <div class="card-value"><?php echo $completedToday; ?></div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
                    <h3 class="card-title">Ingresos del Día</h3>
                </div>
                <div class="card-value">$<?php echo number_format($revenueToday, 2); ?></div>
            </div>
        </div>

        <div class="recent-activity">
            <div class="orders-table">
                <table id="ordersTable">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Domiciliario</th>
                            <th>Estado</th>
                            <th>Hora</th>
                            <th>Direccion</th>
                            <th>Envio inmediato</th>
                            <th>Alistamiento</th>
                        </tr>
                    </thead>
                    <tbody id="ordersTableBody">
                        <!-- Aquí se llenarán los pedidos paginados -->
                    </tbody>
                </table>
                <div id="paginationPedidos" class="pagination"></div>
            </div>
        </div>
    </div>

    <!-- Modal de Nuevo/Editar Pedido -->
    <div id="modalNuevoPedido" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2 id="modalTitle">Nuevo Pedido</h2>
            <form id="formNuevoPedido">
                <input type="hidden" id="id_pedido" name="id_pedido">
                <input type="hidden" id="id_cliente" name="id_cliente">

                <div class="form-container">
                    <div class="form-column">
                        <div class="form-group">
                            <label for="numeroDocumento">Cédula:</label>
                            <input type="text" id="numeroDocumento" name="numeroDocumento" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="nombreCliente">Nombre:</label>
                            <input type="text" id="nombreCliente" name="nombreCliente" class="form-control" required readonly>
                        </div>
                        <div class="form-group">
                            <label for="telefonoCliente">Teléfono:</label>
                            <input type="number" id="telefonoCliente" name="telefonoCliente" class="form-control" required min="0" step="1" pattern="[0-9]+">
                        </div>
                        <div class="form-group">
                            <label for="direccionCliente">Dirección:</label>
                            <input type="text" id="direccionCliente" name="direccionCliente" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="form-group">
                            <label for="barrioCliente">Barrio:</label>
                            <input type="text" id="barrioCliente" name="barrioCliente" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="zonaAuto">Zona:</label>
                            <input type="text" id="zonaAuto" name="zonaAuto" class="form-control" readonly required>
                            <input type="hidden" id="id_zona" name="id_zona">
                        </div>
                        <div class="form-group">
                            <label for="bolsas">Cantidad de paquetes:</label>
                            <input type="number" id="bolsas" name="bolsas" class="form-control" min="1" required>
                        </div>

                        <div class="form-group">
                            <label for="hora">Hora estimada:</label>
                            <div style="display: flex; gap: 5px; align-items: center;">
                                <input type="time" id="hora" name="hora" class="form-control" required>
                                <button type="button" onclick="calcularHoraAuto()" class="btn-login" style="padding: 5px 8px; font-size: 11px;">Auto</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="checkbox-container">
                    <div class="checkbox-item">
                        <label for="envio_inmediato">Envío inmediato:</label>
                        <select name="envio_inmediato" id="envio_inmediato" class="form-control">
                            <option value="no">No</option>
                            <option value="si">Sí</option>
                        </select>
                    </div>
                    <div class="checkbox-item">
                        <label for="alistamiento">Alistamiento:</label>
                        <select name="alistamiento" id="alistamiento" class="form-control">
                            <option value="no">No</option>
                            <option value="si">Sí</option>
                        </select>
                    </div>
                </div>

                <div class="form-group form-full-width">
                    <label for="total">Total:</label>
                    <input type="number" id="total" name="total" class="form-control" step="0.01" required>
                </div>

                <div class="form-row">
                    <button type="submit" class="btn-login">Guardar</button>
                    <button type="button" class="btn-login" onclick="cerrarModal('modalNuevoPedido')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para crear cliente -->
    <div id="modalCrearCliente" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('modalCrearCliente')">×</span>
            <h2>Crear Cliente</h2>
            <form id="formCrearCliente">
                <div class="form-group">
                    <label for="crearDocumento">Cédula:</label>
                    <input type="text" id="crearDocumento" name="crearDocumento" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="crearNombre">Nombre:</label>
                    <input type="text" id="crearNombre" name="crearNombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="crearTelefono">Teléfono:</label>
                    <input type="text" id="crearTelefono" name="crearTelefono" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="crearDireccion">Dirección:</label>
                    <input type="text" id="crearDireccion" name="crearDireccion" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="crearBarrio">Barrio:</label>
                    <input type="text" id="crearBarrio" name="crearBarrio" class="form-control" required>
                </div>
                <button type="submit" class="btn-login">Guardar Cliente</button>
                <button type="button" class="btn-login" onclick="cerrarModal('modalCrearCliente')">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        // --- Función para abrir el modal de nuevo pedido ---
        function abrirModalNuevoPedido() {
            const modal = document.getElementById('modalNuevoPedido');
            const modalTitle = document.getElementById('modalTitle');
            const form = document.getElementById('formNuevoPedido');
            modalTitle.textContent = 'Nuevo Pedido';
            form.reset();
            document.getElementById('id_pedido').value = '';
            document.getElementById('total').value = '';
            modal.classList.add('active');
            autocompletarClientePorCedula();
            autocompletarZonaPorBarrio();
            setupFormListeners();
        }

        // --- Función para editar un pedido existente ---
        function editarPedido(id) {
            const formData = new FormData();
            formData.append('accion', 'obtener');
            formData.append('id', id);
            fetch('../servicios/pedidos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (response.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    if (!response.ok) {
                        throw new Error(`Error en la solicitud: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data || data.error) {
                        throw new Error(data && data.error ? data.error : 'No se pudo cargar el pedido');
                    }
                    const modal = document.getElementById('modalNuevoPedido');
                    const modalTitle = document.getElementById('modalTitle');
                    modalTitle.textContent = 'Editar Pedido';
                    document.getElementById('id_pedido').value = data.id_pedido || '';
                    document.getElementById('numeroDocumento').value = data.documento || '';
                    document.getElementById('id_cliente').value = data.id_cliente || '';
                    document.getElementById('id_zona').value = data.id_zona || '';
                    // Guardar el estado actual del pedido para enviarlo al actualizar
                    window.estadoPedidoActual = data.estado || 'pendiente';
                    document.getElementById('bolsas').value = data.cantidad_paquetes || 1;
                    document.getElementById('total').value = parseFloat(data.total || 0).toFixed(2);
                    document.getElementById('tiempo_estimado').value = data.tiempo_estimado || 30;

                    // Manejar checkboxes
                    document.getElementById('envio_inmediato').checked = data.envio_inmediato == 1;
                    document.getElementById('alistamiento').checked = data.alistamiento == 1;
                    modal.classList.add('active');
                    setupFormListeners();
                })
                .catch(error => {
                    if (error !== 'Sesión expirada') {
                        alert('Error al cargar el pedido: ' + error.message);
                    }
                });
        }

        // --- Sincroniza los campos del formulario de pedido (solo zona y total) ---
        function setupFormListeners() {
            const idZona = document.getElementById('id_zona');
            const inputTotal = document.getElementById('total');
            // Si el campo zona cambia (por autocompletado manual), actualiza el total si hay tarifa
            if (idZona) {
                idZona.addEventListener('change', () => {
                    // Si el campo zona es hidden, normalmente no cambiará manualmente, pero dejamos la lógica por si acaso
                    // El total ya se autocompleta desde autocompletarZonaPorBarrio
                });
            }
            // No hay más campos a sincronizar
        }

        // --- Cierra el modal especificado por id ---
        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        document.querySelectorAll('.close').forEach(btn => {
            btn.onclick = () => cerrarModal('modalNuevoPedido');
        });
        // --- Buscar pedidos por texto en tiempo real ---
        function buscarPedido() {
            const searchInput = document.getElementById('searchInput').value;
            const formData = new FormData();
            formData.append('accion', 'buscar');
            formData.append('query', encodeURIComponent(searchInput));
            fetch('../servicios/pedidos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la solicitud: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    const tbody = document.querySelector('.orders-table tbody');
                    tbody.innerHTML = '';
                    data.forEach(order => {
                        const tr = document.createElement('tr');
                        let tiempoHtml = '';
                        if (order.estado === 'pendiente') {
                            tiempoHtml = `<span class='tiempo-pendiente'>⏳ ${order.tiempo_estimado || 30} min</span>`;
                        } else if (order.estado === 'entregado') {
                            tiempoHtml = `<span class='tiempo-entregado'>✅ Entregado</span>`;
                        } else {
                            tiempoHtml = `<span class='tiempo-cancelado'>❌ Cancelado</span>`;
                        }
                        let botonesHtml = '<div class="action-buttons">';
                        if (order.estado === 'pendiente') {
                            botonesHtml += `<button class="btn btn-entregar" onclick="cambiarEstado(${order.id_pedido}, 'entregado')" title="Marcar como entregado"><i class="fas fa-check"></i></button>`;
                        }
                        if (['pendiente'].includes(order.estado)) {
                            botonesHtml += `<button class="btn btn-cancelar" onclick="cambiarEstado(${order.id_pedido}, 'cancelado')" title="Cancelar pedido"><i class="fas fa-times"></i></button>`;
                        }
                        botonesHtml += `<button class="btn btn-editar" onclick="editarPedido(${order.id_pedido})" title="Editar"><i class="fas fa-edit"></i></button>`;
                        botonesHtml += `<button class="btn btn-eliminar" onclick="eliminarPedido(${order.id_pedido})" title="Eliminar"><i class="fas fa-trash"></i></button>`;
                        if (['entregado', 'cancelado'].includes(order.estado)) {
                            botonesHtml += `<button class="btn btn-archivar" onclick="archivarPedido(${order.id_pedido})" title="Archivar"><i class="fas fa-archive"></i></button>`;
                        }
                        botonesHtml += '</div>';
                        tr.innerHTML = `
                            <td>${order.cliente}</td>
                            <td>${order.domiciliario || 'No asignado'}</td>
                            <td><span class="estado-${order.estado.toLowerCase()} estado">${order.estado.charAt(0).toUpperCase() + order.estado.slice(1)}</span></td>
                            <td>${new Date(order.fecha_pedido).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al buscar pedidos: ' + error.message);
                });
        }

        // Agregar evento de búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            buscarPedido();
        });

        // --- Elimina un pedido por su id ---
        function eliminarPedido(id) {
            if (confirm('¿Está seguro de que desea eliminar este pedido?')) {
                const formData = new FormData();
                formData.append('accion', 'eliminar');
                formData.append('id', id);
                fetch('../servicios/pedidos.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la solicitud: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Pedido eliminado exitosamente');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al eliminar el pedido: ' + error.message);
                    });
            }
        }

        // --- Cambia el estado de un pedido (entregado/cancelado) ---
        function cambiarEstado(id, nuevoEstado) {
            const estados = {
                'entregado': 'marcar como entregado',
                'cancelado': 'cancelar'
            };
            if (confirm(`¿Está seguro de que desea ${estados[nuevoEstado]} este pedido?`)) {
                const formData = new FormData();
                formData.append('accion', 'cambiar_estado');
                formData.append('id', id);
                formData.append('estado', nuevoEstado);
                fetch('../servicios/pedidos.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la solicitud: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Estado actualizado exitosamente');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al cambiar el estado: ' + error.message);
                    });
            }
        }

        // --- Archiva un pedido (lo mueve al historial) ---
        function archivarPedido(id) {
            if (confirm('¿Está seguro de que desea archivar este pedido?')) {
                const formData = new FormData();
                formData.append('accion', 'mover_historial');
                formData.append('id', id);
                fetch('../servicios/pedidos.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la solicitud: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Pedido archivado exitosamente');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al archivar el pedido: ' + error.message);
                    });
            }
        }

        // Manejo global de errores de sesión para todos los fetch
        (function() {
            const originalFetch = window.fetch;
            window.fetch = function() {
                return originalFetch.apply(this, arguments).then(response => {
                    if (response.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    return response;
                });
            };
        })();

        // --- Envío del formulario de nuevo/editar pedido ---
        document.getElementById('formNuevoPedido').onsubmit = function(e) {
            e.preventDefault();
            // Solo valida los campos que existen
            const numeroDocumento = document.getElementById('numeroDocumento').value;
            const nombreCliente = document.getElementById('nombreCliente').value;
            const telefonoCliente = document.getElementById('telefonoCliente').value;
            const direccionCliente = document.getElementById('direccionCliente').value;
            const barrioCliente = document.getElementById('barrioCliente').value;
            const idZona = document.getElementById('id_zona').value;
            const bolsas = document.getElementById('bolsas').value;
            const total = document.getElementById('total').value;
            if (!numeroDocumento || !nombreCliente || !telefonoCliente || !direccionCliente || !barrioCliente || !idZona || !bolsas || !total) {
                alert('Por favor, complete todos los campos obligatorios.');
                return;
            }
            const formData = new FormData(this);
            // Si es nuevo pedido, agregamos el estado pendiente por defecto
            if (!document.getElementById('id_pedido').value) {
                formData.append('estado', 'pendiente');
            } else {
                // Si es edición, enviamos el estado actual del pedido (lo obtenemos del backend al editar)
                if (window.estadoPedidoActual) {
                    formData.append('estado', window.estadoPedidoActual);
                } else {
                    formData.append('estado', 'pendiente'); // fallback
                }
            }
            const url = document.getElementById('id_pedido').value ? '../servicios/pedidos.php' : '../servicios/pedidos.php';
            formData.append('accion', document.getElementById('id_pedido').value ? 'actualizar' : 'procesar');
            fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Mostrar el mensaje real del backend si hay error
                    return response.json().then(data => ({
                        status: response.status,
                        body: data
                    }));
                })
                .then(({
                    status,
                    body
                }) => {
                    if (status !== 200) {
                        alert(body.error || 'Error desconocido al procesar el pedido.');
                        return;
                    }
                    if (body.success) {
                        alert(document.getElementById('id_pedido').value ? 'Pedido actualizado exitosamente' : 'Pedido creado exitosamente');
                        cerrarModal('modalNuevoPedido');
                        location.reload();
                    } else {
                        alert('Error: ' + (body.message || body.error));
                    }
                })
                .catch(error => {
                    if (error !== 'Sesión expirada') {
                        console.error('Error:', error);
                        alert('Error al procesar el pedido: ' + error.message);
                    }
                });
        };

        // --- Paginación profesional para la tabla de pedidos ---
        const rowsPerPage = 5;
        let currentPage = 1;
        let totalPedidos = 0;

        function cargarPedidos(page = 1) {
            fetch('../servicios/pedidos.php', {
                    method: 'POST',
                    body: (() => {
                        const fd = new FormData();
                        fd.append('accion', 'paginar');
                        fd.append('pagina', page);
                        fd.append('por_pagina', rowsPerPage);
                        return fd;
                    })()
                })
                .then(res => res.json())
                .then(data => {
                    totalPedidos = data.total;
                    renderPedidos(data.pedidos);
                    renderPagination(page);
                })
                .catch(err => {
                    alert('Error al cargar pedidos');
                    console.error(err);
                });
        }

        // Renderiza la tabla de pedidos paginados
        // Función corregida para renderizar todos los campos de la tabla de pedidos
        function renderPedidos(pedidos) {
            const tbody = document.getElementById('ordersTableBody');
            tbody.innerHTML = '';

            pedidos.forEach(order => {
                const tr = document.createElement('tr');

                // Formatear la hora del pedido
                const fechaPedido = new Date(order.fecha_pedido);
                const horaFormateada = fechaPedido.toLocaleTimeString('es-ES', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                // Obtener dirección del cliente (necesitarás incluir esto en la consulta SQL)
                const direccion = order.direccion || 'No especificada';

                // Determinar el estado de envío inmediato
                const envioInmediato = order.envio_inmediato === 'si' ?
                    '<span class="badge-si">Sí</span>' :
                    '<span class="badge-no">No</span>';

                // Determinar el estado de alistamiento
                const alistamiento = order.alistamiento === 'si' ?
                    '<span class="badge-si">Sí</span>' :
                    '<span class="badge-no">No</span>';

                // Llenar todas las columnas de la tabla
                tr.innerHTML = `
            <td>${order.cliente}</td>
            <td>${order.domiciliario || 'No asignado'}</td>
            <td><span class="estado-${order.estado.toLowerCase()} estado">${order.estado.charAt(0).toUpperCase() + order.estado.slice(1)}</span></td>
            <td>${horaFormateada}</td>
            <td>${direccion}</td>
            <td>${envioInmediato}</td>
            <td>${alistamiento}</td>
        `;

                tbody.appendChild(tr);
            });
        }
        // Renderiza los controles de paginación
        function renderPagination(page) {
            const pagination = document.getElementById('paginationPedidos');
            const totalPages = Math.ceil(totalPedidos / rowsPerPage) || 1;
            let html = '';
            if (totalPages > 1) {
                html += `<button onclick="cargarPedidos(1)" ${page===1?'disabled':''}>Primera</button>`;
                html += `<button onclick="cargarPedidos(${page-1})" ${page===1?'disabled':''}>Anterior</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    html += `<button onclick="cargarPedidos(${i})" ${page===i?'class=active':''}>${i}</button>`;
                }
                html += `<button onclick="cargarPedidos(${page+1})" ${page===totalPages?'disabled':''}>Siguiente</button>`;
                html += `<button onclick="cargarPedidos(${totalPages})" ${page===totalPages?'disabled':''}>Última</button>`;
            }
            pagination.innerHTML = html;
        }

        // Inicializar pedidos paginados al cargar la página
        window.addEventListener('DOMContentLoaded', function() {
            cargarPedidos(1);
        });

        // --- Autocompletar datos del cliente al escribir la cédula ---
        function autocompletarClientePorCedula() {
            const inputCedula = document.getElementById('numeroDocumento');
            const inputNombre = document.getElementById('nombreCliente');
            const inputTelefono = document.getElementById('telefonoCliente');
            const inputDireccion = document.getElementById('direccionCliente');
            const inputBarrio = document.getElementById('barrioCliente');
            let btnCrearCliente = document.getElementById('btnCrearCliente');
            if (!btnCrearCliente) {
                btnCrearCliente = document.createElement('button');
                btnCrearCliente.type = 'button';
                btnCrearCliente.id = 'btnCrearCliente';
                btnCrearCliente.className = 'btn-login';
                btnCrearCliente.style.marginTop = '10px';
                btnCrearCliente.textContent = 'Crear cliente';
                btnCrearCliente.onclick = function() {
                    abrirModalCrearCliente(inputCedula.value);
                };
                inputCedula.parentNode.appendChild(btnCrearCliente);
            }
            btnCrearCliente.style.display = 'none';

            inputCedula.addEventListener('blur', function() {
                const cedula = inputCedula.value.trim();
                if (cedula.length < 4) return;

                console.log('Buscando cliente con cédula:', cedula);

                fetch('../servicios/clientes.php', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body: new URLSearchParams({
                            accion: 'obtener_por_documento',
                            documento: cedula
                        })
                    })
                    .then(res => {
                        console.log('Respuesta del servidor:', res.status);
                        return res.json();
                    })
                    .then(data => {
                        console.log('Datos recibidos:', data);
                        if (data && !data.error) {
                            inputNombre.value = data.nombre || '';
                            inputTelefono.value = data.telefono || '';
                            inputDireccion.value = data.direccion || '';
                            inputBarrio.value = data.barrio || '';
                            document.getElementById('id_cliente').value = data.id_cliente || '';
                            btnCrearCliente.style.display = 'none';
                            console.log('Cliente encontrado y autocompletado');
                            // Disparar autocompletado de zona si el barrio se autocompleta
                            inputBarrio.dispatchEvent(new Event('blur'));
                        } else {
                            inputNombre.value = '';
                            inputTelefono.value = '';
                            inputDireccion.value = '';
                            inputBarrio.value = '';
                            btnCrearCliente.style.display = 'inline-block';
                            console.log('Cliente no encontrado');
                        }
                    })
                    .catch(error => {
                        console.error('Error en autocompletado:', error);
                        inputNombre.value = '';
                        inputTelefono.value = '';
                        inputDireccion.value = '';
                        inputBarrio.value = '';
                        btnCrearCliente.style.display = 'inline-block';
                    });
            });
        }

        function abrirModalCrearCliente(cedula) {
            const modal = document.getElementById('modalCrearCliente');
            document.getElementById('formCrearCliente').reset();
            document.getElementById('crearDocumento').value = cedula || '';
            modal.classList.add('active');
        }
        document.getElementById('formCrearCliente').onsubmit = function(e) {
            e.preventDefault();
            const datos = {
                accion: 'guardar',
                nombre: document.getElementById('crearNombre').value.trim(),
                documento: document.getElementById('crearDocumento').value.trim(),
                telefono: document.getElementById('crearTelefono').value.trim(),
                direccion: document.getElementById('crearDireccion').value.trim(),
                barrio: document.getElementById('crearBarrio').value.trim()
            };
            fetch('/SM_Domicilios/servicios/clientes.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: new URLSearchParams(datos)
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.success) {
                        // Autocompletar los campos en el formulario de pedido principal
                        document.getElementById('numeroDocumento').value = datos.documento;
                        document.getElementById('nombreCliente').value = datos.nombre;
                        document.getElementById('telefonoCliente').value = datos.telefono;
                        document.getElementById('direccionCliente').value = datos.direccion;
                        document.getElementById('barrioCliente').value = datos.barrio;
                        cerrarModal('modalCrearCliente');
                        alert('Cliente creado exitosamente');
                    } else {
                        alert(data.error || 'No se pudo crear el cliente');
                    }
                })
                .catch(() => {
                    alert('Error al crear el cliente');
                });
        };

        function autocompletarZonaPorBarrio() {
            const inputBarrio = document.getElementById('barrioCliente');
            const inputZona = document.getElementById('zonaAuto');
            const inputIdZona = document.getElementById('id_zona');
            const inputTotal = document.getElementById('total');
            inputBarrio.addEventListener('blur', function() {
                const barrio = inputBarrio.value.trim();
                if (!barrio) return;
                fetch('/SM_Domicilios/servicios/zonas.php', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body: new URLSearchParams({
                            accion: 'buscar_por_barrio',
                            barrio: barrio
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.log('Respuesta zona:', data); // Log para depuración
                        if (data && !data.error) {
                            inputZona.value = data.nombre;
                            inputIdZona.value = data.id_zona;
                            inputTotal.value = parseFloat(data.tarifa_base).toFixed(2);
                        } else {
                            inputZona.value = '';
                            inputIdZona.value = '';
                            inputTotal.value = '';
                        }
                    })
                    .catch(() => {
                        inputZona.value = '';
                        inputIdZona.value = '';
                        inputTotal.value = '';
                    });
            });
        }

        // Función para calcular hora automática
        function calcularHoraAuto() {
            const ahora = new Date();
            const envioInmediato = document.getElementById('envio_inmediato').checked;
            const alistamiento = document.getElementById('alistamiento').checked;

            let minutos = 30; // Tiempo base

            if (envioInmediato) minutos = 20;
            if (alistamiento) minutos += 15;

            ahora.setMinutes(ahora.getMinutes() + minutos);

            const horas = ahora.getHours().toString().padStart(2, '0');
            const mins = ahora.getMinutes().toString().padStart(2, '0');

            document.getElementById('hora').value = horas + ':' + mins;
        }
    </script>
</body>

</html>