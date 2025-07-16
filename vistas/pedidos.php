<?php
require_once '../servicios/verificar_permisos.php';
verificarAcceso('pedidos');

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios;charset=utf8mb4", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener datos para las tarjetas del tablero
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente' AND movido_historico = 0")->fetchColumn();
$completedToday = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE() AND movido_historico = 0")->fetchColumn();
// Calcular ingresos del día sumando pedidos entregados activos y archivados
$revenueTodayPedidos = $pdo->query("SELECT SUM(total) FROM pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()")->fetchColumn() ?? 0.00;
$revenueTodayArchivados = $pdo->query("SELECT SUM(total) FROM historico_pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()")->fetchColumn() ?? 0.00;
$revenueToday = $revenueTodayPedidos + $revenueTodayArchivados;

// Obtener pedidos recientes
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

// Obtener clientes, zonas y domiciliarios para el formulario
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
            <?php if (tienePermiso('dashboard')): ?>
            <a href="dashboard.php" class="menu-item"><i class="fas fa-tachometer-alt"></i><span class="menu-text">Inicio</span></a>
            <?php endif; ?>
            <a href="pedidos.php" class="menu-item active"><i class="fas fa-shopping-bag"></i><span class="menu-text">Pedidos</span></a>
            <a href="clientes.php" class="menu-item"><i class="fas fa-users"></i><span class="menu-text">Clientes</span></a>
            <?php if (tienePermiso('domiciliarios')): ?>
            <a href="domiciliarios.php" class="menu-item"><i class="fas fa-motorcycle"></i><span class="menu-text">Domiciliarios</span></a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
            <a href="zonas.php" class="menu-item"><i class="fas fa-map-marked-alt"></i><span class="menu-text">Zonas de Entrega</span></a>
            <?php endif; ?>
            <a href="reportes.php" class="menu-item"><i class="fas fa-chart-bar"></i><span class="menu-text">Reportes</span></a>
            <a href="historial_pedidos.php" class="menu-item"><i class="fas fa-history"></i><span class="menu-text">Historial Pedidos</span></a>
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
                <?php if ($pendingOrders > 0): ?>
                    <div class="notification-badge">
                        <i class="fas fa-bell"></i>
                        <span class="badge-count"><?php echo $pendingOrders; ?></span>
                        <span class="badge-text">Pedidos pendientes</span>
                    </div>
                <?php endif; ?>
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
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Domiciliario</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Tiempo</th>
                            <th>Acciones</th>
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
                <div class="form-group">
                    <label for="numeroDocumento">Cédula:</label>
                    <input type="text" id="numeroDocumento" name="numeroDocumento" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="id_cliente">Cliente:</label>
                    <select id="id_cliente" name="id_cliente" class="form-control" required>
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id_cliente']; ?>" data-documento="<?php echo $client['documento']; ?>"><?php echo htmlspecialchars($client['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_zona">Zona:</label>
                    <select id="id_zona" name="id_zona" class="form-control" required>
                        <option value="">Seleccione una zona</option>
                        <?php foreach ($zones as $zone): ?>
                            <option value="<?php echo $zone['id_zona']; ?>" data-tarifa="<?php echo $zone['tarifa_base']; ?>"><?php echo htmlspecialchars($zone['nombre']); ?> ($<?php echo number_format($zone['tarifa_base'], 2); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_domiciliario">Repartidor:</label>
                    <select id="id_domiciliario" name="id_domiciliario" class="form-control" required>
                        <option value="">Seleccione un repartidor</option>
                        <?php foreach ($domiciliarios as $domiciliario): ?>
                            <option value="<?php echo $domiciliario['id_domiciliario']; ?>"><?php echo htmlspecialchars($domiciliario['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="bolsas">Cantidad de paquetes:</label>
                    <input type="number" id="bolsas" name="bolsas" class="form-control" min="1" required>
                </div>
                <div class="form-group">
                    <label for="total">Total:</label>
                    <input type="number" id="total" name="total" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="tiempo_estimado">Tiempo estimado (minutos):</label>
                    <input type="number" id="tiempo_estimado" name="tiempo_estimado" class="form-control" min="15" max="120" value="30" required>
                </div>
                <button type="submit" class="btn-login">Guardar</button>
                <button type="button" class="btn-login" onclick="cerrarModal('modalNuevoPedido')">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModalNuevoPedido() {
            const modal = document.getElementById('modalNuevoPedido');
            const modalTitle = document.getElementById('modalTitle');
            const form = document.getElementById('formNuevoPedido');
            modalTitle.textContent = 'Nuevo Pedido';
            form.reset();
            document.getElementById('id_pedido').value = '';
            document.getElementById('total').value = '';
            document.getElementById('tiempo_estimado').value = '30';
            modal.classList.add('active');
            setupFormListeners();
        }

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
                    document.getElementById('id_domiciliario').value = data.id_domiciliario || '';
                    // Guardar el estado actual del pedido para enviarlo al actualizar
                    window.estadoPedidoActual = data.estado || 'pendiente';
                    document.getElementById('bolsas').value = data.cantidad_paquetes || 1;
                    document.getElementById('total').value = parseFloat(data.total || 0).toFixed(2);
                    document.getElementById('tiempo_estimado').value = data.tiempo_estimado || 30;
                    modal.classList.add('active');
                    setupFormListeners();
                })
                .catch(error => {
                    if (error !== 'Sesión expirada') {
                        alert('Error al cargar el pedido: ' + error.message);
                    }
                });
        }

        function setupFormListeners() {
            const numeroDocumento = document.getElementById('numeroDocumento');
            const idCliente = document.getElementById('id_cliente');
            const idZona = document.getElementById('id_zona');
            numeroDocumento.addEventListener('input', () => {
                const selectedOption = Array.from(idCliente.options).find(opt => opt.dataset.documento === numeroDocumento.value);
                idCliente.value = selectedOption ? selectedOption.value : '';
            });
            idCliente.addEventListener('change', () => {
                const selectedOption = idCliente.options[idCliente.selectedIndex];
                numeroDocumento.value = selectedOption.dataset.documento || '';
            });
            idZona.addEventListener('change', () => {
                const selectedOption = idZona.options[idZona.selectedIndex];
                const tarifa = selectedOption.dataset.tarifa || 0;
                document.getElementById('total').value = parseFloat(tarifa).toFixed(2);
            });
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        document.querySelectorAll('.close').forEach(btn => {
            btn.onclick = () => cerrarModal('modalNuevoPedido');
        });

        window.onclick = function(event) {
            const modal = document.getElementById('modalNuevoPedido');
            if (event.target === modal) {
                cerrarModal('modalNuevoPedido');
            }
        };

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
                        const tiempoEstimado = order.tiempo_estimado || 30;
                        let tiempoHtml = '';
                        if (order.estado === 'pendiente') {
                            tiempoHtml = `<span class='tiempo-pendiente'>⏳ ${tiempoEstimado} min</span>`;
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
                            <td>#${order.id_pedido}</td>
                            <td>${order.cliente}</td>
                            <td>${order.domiciliario || 'No asignado'}</td>
                            <td><span class="estado-${order.estado.toLowerCase()} estado">${order.estado.charAt(0).toUpperCase() + order.estado.slice(1)}</span></td>
                            <td>${new Date(order.fecha_pedido).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })}</td>
                            <td>${tiempoHtml}</td>
                            <td>${botonesHtml}</td>
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

        document.getElementById('formNuevoPedido').onsubmit = function(e) {
            e.preventDefault();
            const idCliente = document.getElementById('id_cliente').value;
            const idZona = document.getElementById('id_zona').value;
            const idDomiciliario = document.getElementById('id_domiciliario').value;
            const bolsas = document.getElementById('bolsas').value;
            const total = document.getElementById('total').value;
            if (!idCliente || !idZona || !idDomiciliario || !bolsas || !total) {
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
                    return response.json().then(data => ({ status: response.status, body: data }));
                })
                .then(({ status, body }) => {
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

        // PAGINACIÓN PROFESIONAL
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

        function renderPedidos(pedidos) {
            const tbody = document.getElementById('ordersTableBody');
            tbody.innerHTML = '';
            pedidos.forEach(order => {
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
                    <td>#${order.id_pedido}</td>
                    <td>${order.cliente}</td>
                    <td>${order.domiciliario || 'No asignado'}</td>
                    <td><span class="estado-${order.estado.toLowerCase()} estado">${order.estado.charAt(0).toUpperCase() + order.estado.slice(1)}</span></td>
                    <td>${new Date(order.fecha_pedido).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })}</td>
                    <td>${tiempoHtml}</td>
                    <td>${botonesHtml}</td>
                `;
                tbody.appendChild(tr);
            });
        }

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
    </script>
</body>
</html>