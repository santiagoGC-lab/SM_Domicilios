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

// Calcular ingresos del día sumando pedidos entregados activos y archivados
$ingresosHoyPedidos = $pdo->query("SELECT SUM(total) FROM pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()")->fetchColumn() ?? 0.00;
$ingresosHoyArchivados = $pdo->query("SELECT SUM(total) FROM historico_pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()")->fetchColumn() ?? 0.00;
$ingresosHoy = $ingresosHoyPedidos + $ingresosHoyArchivados;

// Obtener zonas activas para el filtro
$zonasFiltro = $pdo->query("SELECT id_zona, nombre FROM zonas WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

// Procesar filtros
$filtroZona = isset($_GET['zona']) ? intval($_GET['zona']) : '';
$filtroFechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$filtroFechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Construir consulta dinámica para el historial
$where = [];
$params = [];
if ($filtroZona) {
    $where[] = 'hp.zona_nombre = (SELECT nombre FROM zonas WHERE id_zona = ?)';
    $params[] = $filtroZona;
}
if ($filtroFechaInicio) {
    $where[] = 'DATE(hp.fecha_pedido) >= ?';
    $params[] = $filtroFechaInicio;
}
if ($filtroFechaFin) {
    $where[] = 'DATE(hp.fecha_pedido) <= ?';
    $params[] = $filtroFechaFin;
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sqlHistorial = "SELECT hp.id_historico, hp.id_pedido_original, hp.cliente_nombre, hp.domiciliario_nombre, hp.estado, hp.fecha_pedido, hp.fecha_completado, hp.cantidad_paquetes, hp.total, hp.tiempo_estimado, hp.zona_nombre FROM historico_pedidos hp $whereSQL ORDER BY hp.fecha_completado DESC LIMIT 100";
$stmt = $pdo->prepare($sqlHistorial);
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->execute($params);
} else {
    $stmt->execute();
}
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
                    <h3 class="card-title">Ingresos del Día</h3>
                </div>
                <div class="card-value">$<?php echo number_format($ingresosHoy, 2); ?></div>
            </div>
        </div>

        <!-- Filtros arriba de la tabla -->
        <form method="GET" class="filtros-historial">
            <label for="fecha_inicio">Desde:</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" value="<?php echo htmlspecialchars($filtroFechaInicio); ?>" class="input-form" style="min-width:130px;">
            <label for="fecha_fin">Hasta:</label>
            <input type="date" name="fecha_fin" id="fecha_fin" value="<?php echo htmlspecialchars($filtroFechaFin); ?>" class="input-form" style="min-width:130px;">
            <label for="zona">Zona:</label>
            <select name="zona" id="zona" class="input-form" style="min-width:150px;">
                <option value="">Todas</option>
                <?php foreach ($zonasFiltro as $zona): ?>
                    <option value="<?php echo $zona['id_zona']; ?>" <?php if($filtroZona == $zona['id_zona']) echo 'selected'; ?>><?php echo htmlspecialchars($zona['nombre']); ?></option>
                <?php endforeach; ?>
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
        function buscarHistorial() {
            const searchInput = document.getElementById('searchInput').value;
            fetch('../servicios/buscar_historial_pedidos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'query=' + encodeURIComponent(searchInput)
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
                    tr.innerHTML = `
                        <td>#${order.id_pedido_original}</td>
                        <td>${order.cliente_nombre}</td>
                        <td>${order.domiciliario_nombre || 'No asignado'}</td>
                        <td><span class="estado-${order.estado.toLowerCase()} estado">${order.estado.charAt(0).toUpperCase() + order.estado.slice(1)}</span></td>
                        <td>${new Date(order.fecha_pedido).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })}</td>
                        <td>${new Date(order.fecha_completado).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })}</td>
                        <td>$${parseFloat(order.total).toFixed(2)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al buscar historial: ' + error.message);
            });
        }

        // Agregar evento de búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            buscarHistorial();
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
    </script>
    <script>
// Paginación de historial de pedidos (frontend)
(function() {
    const rowsPerPage = 10;
    const table = document.getElementById('historialTable');
    const tbody = document.getElementById('historialTableBody');
    const pagination = document.getElementById('paginationHistorial');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    let currentPage = 1;
    const totalPages = Math.ceil(rows.length / rowsPerPage);

    function showPageHistorial(page) {
        currentPage = page;
        rows.forEach((row, i) => {
            row.style.display = (i >= (page-1)*rowsPerPage && i < page*rowsPerPage) ? '' : 'none';
        });
        renderPaginationHistorial();
    }

    function renderPaginationHistorial() {
        let html = '';
        if (totalPages > 1) {
            html += `<button onclick=\"showPageHistorial(1)\" ${currentPage===1?'disabled':''}>Primera</button>`;
            html += `<button onclick=\"showPageHistorial(${currentPage-1})\" ${currentPage===1?'disabled':''}>Anterior</button>`;
            for (let i = 1; i <= totalPages; i++) {
                html += `<button onclick=\"showPageHistorial(${i})\" ${currentPage===i?'class=active':''}>${i}</button>`;
            }
            html += `<button onclick=\"showPageHistorial(${currentPage+1})\" ${currentPage===totalPages?'disabled':''}>Siguiente</button>`;
            html += `<button onclick=\"showPageHistorial(${totalPages})\" ${currentPage===totalPages?'disabled':''}>Última</button>`;
        }
        pagination.innerHTML = html;
    }

    window.showPageHistorial = showPageHistorial;
    showPageHistorial(1);
})();
</script>
<script>
function verDetalleHistorial(id) {
    fetch('../servicios/buscar_historial_pedidos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_historico=' + encodeURIComponent(id)
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert('Error: ' + data.error);
            return;
        }
        // Construir el HTML con los datos usando estilos del sistema
        let html = `<table class='data-table' style='width:100%;margin-bottom:0;'>`;
        html += `<tr><th>ID Pedido</th><td>#${data.id_pedido_original}</td></tr>`;
        html += `<tr><th>Cliente</th><td>${data.cliente_nombre} (${data.cliente_documento})</td></tr>`;
        html += `<tr><th>Teléfono</th><td>${data.cliente_telefono || ''}</td></tr>`;
        html += `<tr><th>Dirección</th><td>${data.cliente_direccion || ''}</td></tr>`;
        html += `<tr><th>Domiciliario</th><td>${data.domiciliario_nombre || 'No asignado'}</td></tr>`;
        html += `<tr><th>Zona</th><td>${data.zona_nombre || ''}</td></tr>`;
        html += `<tr><th>Estado</th><td><span class='estado-${data.estado.toLowerCase()} estado'>${data.estado.charAt(0).toUpperCase() + data.estado.slice(1)}</span></td></tr>`;
        html += `<tr><th>Fecha Pedido</th><td>${data.fecha_pedido}</td></tr>`;
        html += `<tr><th>Fecha Archivado</th><td>${data.fecha_completado}</td></tr>`;
        html += `<tr><th>Cantidad Paquetes</th><td>${data.cantidad_paquetes}</td></tr>`;
        html += `<tr><th>Total</th><td>$${parseFloat(data.total).toFixed(2)}</td></tr>`;
        html += `<tr><th>Tiempo Estimado</th><td>${data.tiempo_estimado} min</td></tr>`;
        html += `</table>`;
        document.getElementById('detalleHistorialContent').innerHTML = html;
        document.getElementById('modalDetalleHistorial').classList.add('active');
    })
    .catch(error => {
        alert('Error al cargar detalles: ' + error);
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