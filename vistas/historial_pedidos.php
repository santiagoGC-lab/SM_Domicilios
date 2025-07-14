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

// Obtener pedidos archivados
$stmt = $pdo->query("
    SELECT hp.id_historico, hp.id_pedido_original, hp.cliente_nombre, hp.domiciliario_nombre, hp.estado, 
           hp.fecha_pedido, hp.fecha_completado, hp.cantidad_paquetes, hp.total, hp.tiempo_estimado
    FROM historico_pedidos hp
    ORDER BY hp.fecha_completado DESC
    LIMIT 10
");
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
            <div class="search-bar">
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
                    <div class="card-icon"><i class="fas fa-calendar-day"></i></div>
                    <h3 class="card-title">Archivados Hoy</h3>
                </div>
                <div class="card-value"><?php echo $archivedToday; ?></div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
                    <h3 class="card-title">Ingresos Archivados</h3>
                </div>
                <div class="card-value">$<?php echo number_format($revenueArchived, 2); ?></div>
            </div>
        </div>

        <div class="recent-activity">
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Domiciliario</th>
                            <th>Estado</th>
                            <th>Fecha Pedido</th>
                            <th>Fecha Archivado</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivedOrders as $pedido): ?>
                            <tr>
                                <td>#<?php echo $pedido['id_pedido_original']; ?></td>
                                <td><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['domiciliario_nombre'] ?? 'No asignado'); ?></td>
                                <td><span class="estado-<?php echo strtolower($pedido['estado']); ?> estado"><?php echo ucfirst($pedido['estado']); ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_completado'])); ?></td>
                                <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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
                        window.location.href = '../login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    return response;
                });
            };
        })();
    </script>
</body>
</html>