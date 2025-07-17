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
$totalArchived = $pdo->query("SELECT COUNT(*) FROM historico_pedidos")->fetchColumn();
$archivedToday = $pdo->query("SELECT COUNT(*) FROM historico_pedidos WHERE DATE(fecha_completado) = CURDATE()")->fetchColumn();
$revenueArchived = $pdo->query("SELECT SUM(total) FROM historico_pedidos WHERE estado = 'entregado'")->fetchColumn() ?? 0.00;

// Calcular ingresos del mes sumando pedidos entregados activos y archivados
$ingresosMesPedidos = $pdo->query("SELECT SUM(total) FROM pedidos WHERE estado = 'entregado' AND YEAR(fecha_pedido) = YEAR(CURDATE()) AND MONTH(fecha_pedido) = MONTH(CURDATE())")->fetchColumn() ?? 0.00;
$ingresosMesArchivados = $pdo->query("SELECT SUM(total) FROM historico_pedidos WHERE estado = 'entregado' AND YEAR(fecha_pedido) = YEAR(CURDATE()) AND MONTH(fecha_pedido) = MONTH(CURDATE())")->fetchColumn() ?? 0.00;
$ingresosMes = $ingresosMesPedidos + $ingresosMesArchivados;

// Procesar filtros - por defecto mostrar el mes actual
$filtroEstado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtroFechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01'); // Primer día del mes actual
$filtroFechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t'); // Último día del mes actual

// Variables para paginación (se usarán en JavaScript)
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$porPagina = 10;

// Obtener datos iniciales (sin paginación para mostrar estadísticas)
$sqlHistorial = "SELECT hp.id_historico, hp.id_pedido_original, hp.cliente_nombre, hp.domiciliario_nombre, hp.estado, hp.fecha_pedido, hp.fecha_completado, hp.cantidad_paquetes, hp.total, hp.tiempo_estimado, hp.zona_nombre FROM historico_pedidos hp ORDER BY hp.fecha_completado DESC LIMIT 10";
$stmt = $pdo->prepare($sqlHistorial);
$stmt->execute();
$archivedOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Historial de Pedidos</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="stylesheet" href="../componentes/pedidos.css">
    <link rel="stylesheet" href="../componentes/cliente.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
.filtros-historial {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(1,89,56,0.07);
    padding: 10px 16px 6px 16px;
    margin-bottom: 14px;
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    min-height: 0;
}
.filtros-historial label {
    font-weight: 500;
    color: #015938;
    margin-right: 2px;
    font-size: 14px;
}
.filtros-historial .input-form, .filtros-historial select {
    border: 1px solid #b2b2b2;
    border-radius: 5px;
    padding: 4px 8px;
    font-size: 14px;
    background: #f9f9f9;
    color: #015938;
    font-family: 'Poppins', sans-serif;
    transition: border-color 0.2s;
    min-height: 28px;
    height: 28px;
}
.filtros-historial .input-form:focus, .filtros-historial select:focus {
    border-color: #007B55;
    background: #fff;
}
.filtros-historial .btn-login {
    padding: 5px 14px;
    border-radius: 5px;
    font-size: 14px;
    background: #007B55;
    color: #fff;
    border: none;
    font-family: 'Poppins', sans-serif;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    gap: 5px;
    min-height: 28px;
    height: 28px;
}
.filtros-historial .btn-login:hover {
    background: #015938;
}
</style>
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
            <a href="pedidos.php" class="menu-item"><i class="fas fa-shopping-bag"></i><span class="menu-text">Pedidos</span></a>
            <a href="clientes.php" class="menu-item"><i class="fas fa-users"></i><span class="menu-text">Clientes</span></a>
            <?php if (tienePermiso('domiciliarios')): ?>
            <a href="domiciliarios.php" class="menu-item"><i class="fas fa-motorcycle"></i><span class="menu-text">Domiciliarios</span></a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
            <a href="zonas.php" class="menu-item"><i class="fas fa-map-marked-alt"></i><span class="menu-text">Zonas de Entrega</span></a>
            <?php endif; ?>
            <a href="reportes.php" class="menu-item"><i class="fas fa-chart-bar"></i><span class="menu-text">Reportes</span></a>
            <a href="historial_pedidos.php" class="menu-item active"><i class="fas fa-history"></i><span class="menu-text">Historial Pedidos</span></a>
            <?php if (esAdmin()): ?>
            <a href="tabla_usuarios.php" class="menu-item"><i class="fas fa-users-cog"></i><span class="menu-text">Gestionar Usuarios</span></a>
            <?php endif; ?>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2>Historial de Pedidos</h2>
                <p style="margin: 0; color: #666; font-size: 14px;">Manejo por meses - Los registros del mes anterior se mueven a Reportes</p>
            </div>
            <div class="search-bar" style="margin-top:10px;">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar en historial..." id="searchInput">
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-archive"></i></div>
                    <h3 class="card-title">Total Archivados</h3>
                </div>
                <div class="card-value"><?php echo $totalArchived; ?></div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
                    <h3 class="card-title">Ingresos del Mes</h3>
                </div>
                <div class="card-value">$<?php echo number_format($ingresosMes, 2); ?></div>
            </div>
        </div>

        <!-- Filtros arriba de la tabla -->
        <form method="GET" class="filtros-historial">
            <label for="fecha_inicio">Desde:</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo htmlspecialchars($filtroFechaInicio); ?>" class="input-form" style="min-width:130px;">
            <label for="fecha_fin">Hasta:</label>
            <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo htmlspecialchars($filtroFechaFin); ?>" class="input-form" style="min-width:130px;">
            <label for="estado">Estado:</label>
            <select name="estado" id="estado" class="input-form" style="min-width:150px;">
                <option value="">Todos</option>
                <option value="entregado" <?php if($filtroEstado == 'entregado') echo 'selected'; ?>>Entregado</option>
                <option value="cancelado" <?php if($filtroEstado == 'cancelado') echo 'selected'; ?>>Cancelado</option>
            </select>
            <button type="submit" class="btn-login"><i class="fas fa-filter"></i> Filtrar</button>
        </form>

        <div class="recent-activity">
            <div class="orders-table">
                <table id="historialTable">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Domiciliario</th>
                            <th>Zona</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="historialTableBody">
                        <?php foreach ($archivedOrders as $pedido): ?>
                            <tr>
                                <td>#<?php echo $pedido['id_pedido_original']; ?></td>
                                <td><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['domiciliario_nombre'] ?? 'No asignado'); ?></td>
                                <td><?php echo htmlspecialchars($pedido['zona_nombre'] ?? ''); ?></td>
                                <td><span class="estado-<?php echo strtolower($pedido['estado']); ?> estado"><?php echo ucfirst($pedido['estado']); ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                <td>
                                    <button class="btn btn-info" onclick="verDetalleHistorial(<?php echo $pedido['id_historico']; ?>)"><i class="fas fa-eye"></i> Ver</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="paginationHistorial" class="pagination"></div>
            </div>
        </div>
    </div>

    <!-- Modal para detalles del historial -->
    <div id="modalDetalleHistorial" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalDetalleHistorial()">&times;</span>
            <h2>Detalle del Pedido Archivado</h2>
            <div id="detalleHistorialContent" style="margin-top: 10px;"></div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let totalPages = 1;
        let currentFilters = {};

        let allPedidos = []; // Almacenar todos los pedidos
        let filteredPedidos = []; // Pedidos filtrados
        const itemsPerPage = 5; // Items por página

        // Función para cargar historial completo
        function cargarHistorial(filtros = {}) {
            const formData = new FormData();
            formData.append('accion', 'obtener');
            
            if (filtros.fecha_inicio) formData.append('fecha_inicio', filtros.fecha_inicio);
            if (filtros.fecha_fin) formData.append('fecha_fin', filtros.fecha_fin);
            if (filtros.estado) formData.append('estado', filtros.estado);

            fetch('../servicios/historial_pedidos.php', {
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
                
                allPedidos = data;
                filteredPedidos = data;
                currentPage = 1;
                mostrarPagina(1);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar historial: ' + error.message);
            });
        }

        // Función para mostrar una página específica
        function mostrarPagina(pagina) {
            const inicio = (pagina - 1) * itemsPerPage;
            const fin = inicio + itemsPerPage;
            const pedidosPagina = filteredPedidos.slice(inicio, fin);
            
            // Actualizar tabla
            const tbody = document.getElementById('historialTableBody');
            tbody.innerHTML = '';
            
            pedidosPagina.forEach(pedido => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>#${pedido.id_pedido_original}</td>
                    <td>${pedido.cliente_nombre}</td>
                    <td>${pedido.domiciliario_nombre || 'No asignado'}</td>
                    <td>${pedido.zona_nombre || ''}</td>
                    <td><span class="estado-${pedido.estado.toLowerCase()} estado">${pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1)}</span></td>
                    <td>${new Date(pedido.fecha_pedido).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })}</td>
                    <td>$${parseFloat(pedido.total).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-info" onclick="verDetalleHistorial(${pedido.id_historico})"><i class="fas fa-eye"></i> Ver</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            
            // Actualizar paginación
            currentPage = pagina;
            totalPages = Math.ceil(filteredPedidos.length / itemsPerPage);
            renderPagination();
        }

        // Función para renderizar paginación
        function renderPagination() {
            const pagination = document.getElementById('paginationHistorial');
            let html = '';
            
            if (totalPages > 1) {
                html += `<button onclick="cambiarPagina(1)" ${currentPage === 1 ? 'disabled' : ''}>Primera</button>`;
                html += `<button onclick="cambiarPagina(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Anterior</button>`;
                
                for (let i = 1; i <= totalPages; i++) {
                    html += `<button onclick="cambiarPagina(${i})" ${currentPage === i ? 'class="active"' : ''}>${i}</button>`;
                }
                
                html += `<button onclick="cambiarPagina(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Siguiente</button>`;
                html += `<button onclick="cambiarPagina(${totalPages})" ${currentPage === totalPages ? 'disabled' : ''}>Última</button>`;
            }
            
            pagination.innerHTML = html;
        }

        // Función para cambiar página
        function cambiarPagina(pagina) {
            if (pagina < 1 || pagina > totalPages) return;
            mostrarPagina(pagina);
        }

        // Función para buscar historial
        function buscarHistorial() {
            const searchInput = document.getElementById('searchInput').value;
            if (searchInput.trim() === '') {
                filteredPedidos = allPedidos;
                mostrarPagina(1);
                return;
            }

            // Filtrar localmente
            filteredPedidos = allPedidos.filter(pedido => 
                pedido.cliente_nombre.toLowerCase().includes(searchInput.toLowerCase()) ||
                (pedido.domiciliario_nombre && pedido.domiciliario_nombre.toLowerCase().includes(searchInput.toLowerCase())) ||
                (pedido.zona_nombre && pedido.zona_nombre.toLowerCase().includes(searchInput.toLowerCase())) ||
                pedido.estado.toLowerCase().includes(searchInput.toLowerCase()) ||
                pedido.id_pedido_original.toString().includes(searchInput)
            );
            
            mostrarPagina(1);
        }

        // Evento de búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            buscarHistorial();
        });

        // Evento para filtros
        document.querySelector('.filtros-historial').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            currentFilters = {
                fecha_inicio: formData.get('fecha_inicio'),
                fecha_fin: formData.get('fecha_fin'),
                estado: formData.get('estado')
            };
            cargarHistorial(currentFilters);
        });

        // Manejo global de errores de sesión
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

        // Cargar historial inicial
        document.addEventListener('DOMContentLoaded', function() {
            // Por defecto cargar el mes actual
            const fechaInicio = document.getElementById('fecha_inicio').value;
            const fechaFin = document.getElementById('fecha_fin').value;
            cargarHistorial({
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin
            });
        });
    </script>
<script>
function verDetalleHistorial(id) {
    console.log('Ver detalles del pedido:', id); // Debug
    
    const formData = new FormData();
    formData.append('accion', 'detalle');
    formData.append('id_historico', id);
    
    fetch('../servicios/historial_pedidos.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status); // Debug
        if (!response.ok) {
            throw new Error('Error en la solicitud: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Data recibida:', data); // Debug
        
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        
        // Construir el HTML con los datos usando estilos del sistema
        let html = `<table class='data-table' style='width:100%;margin-bottom:0;border-collapse:collapse;'>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>ID Pedido</th><td style='padding:8px;'>#${data.id_pedido_original}</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Cliente</th><td style='padding:8px;'>${data.cliente_nombre} (${data.cliente_documento})</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Teléfono</th><td style='padding:8px;'>${data.cliente_telefono || 'No disponible'}</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Dirección</th><td style='padding:8px;'>${data.cliente_direccion || 'No disponible'}</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Domiciliario</th><td style='padding:8px;'>${data.domiciliario_nombre || 'No asignado'}</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Zona</th><td style='padding:8px;'>${data.zona_nombre || ''}</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Estado</th><td style='padding:8px;'><span class='estado-${data.estado.toLowerCase()} estado'>${data.estado.charAt(0).toUpperCase() + data.estado.slice(1)}</span></td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Fecha Pedido</th><td style='padding:8px;'>${new Date(data.fecha_pedido).toLocaleString('es-ES')}</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Fecha Archivado</th><td style='padding:8px;'>${new Date(data.fecha_completado).toLocaleString('es-ES')}</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Cantidad Paquetes</th><td style='padding:8px;'>${data.cantidad_paquetes}</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Total</th><td style='padding:8px;'>$${parseFloat(data.total).toFixed(2)}</td></tr>`;
        html += `<tr style='border-bottom:1px solid #ddd;'><th style='padding:8px;text-align:left;background:#f5f5f5;'>Tiempo Estimado</th><td style='padding:8px;'>${data.tiempo_estimado} min</td></tr>`;
        html += `</table>`;
        
        document.getElementById('detalleHistorialContent').innerHTML = html;
        document.getElementById('modalDetalleHistorial').classList.add('active');
        console.log('Modal abierto'); // Debug
    })
    .catch(error => {
        console.error('Error completo:', error); // Debug
        alert('Error al cargar detalles: ' + error.message);
    });
}
function cerrarModalDetalleHistorial() {
    document.getElementById('modalDetalleHistorial').classList.remove('active');
}
window.onclick = function(event) {
    const modal = document.getElementById('modalDetalleHistorial');
    if (event.target === modal) {
        cerrarModalDetalleHistorial();
    }
};
</script>
</body>
</html>