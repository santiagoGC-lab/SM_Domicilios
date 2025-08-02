<?php
// --- Verificación de permisos y conexión a la base de datos ---
require_once '../servicios/verificar_permisos.php';
verificarAcceso('dashboard');

require_once '../servicios/conexion.php';

// --- Obtener estadísticas en tiempo real para el dashboard ---
try {
    // Estadísticas principales
    $pedidosHoy = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()")->fetchColumn();
    $pedidosEntregadosHoy = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE() AND estado = 'entregado'")->fetchColumn();
    $pedidosPendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();

    // Ingresos de hoy: sumar pedidos entregados en ambas tablas
    $ingresosHoyPedidos = $pdo->query("SELECT SUM(total) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE() AND estado = 'entregado'")->fetchColumn() ?? 0;
    $ingresosHoyHistorico = $pdo->query("SELECT SUM(total) FROM historico_pedidos WHERE DATE(fecha_completado) = CURDATE() AND estado = 'entregado'")->fetchColumn() ?? 0;
    $ingresosHoy = $ingresosHoyPedidos + $ingresosHoyHistorico;

    // Domiciliarios
    $domiciliariosActivos = $pdo->query("SELECT COUNT(*) FROM domiciliarios WHERE estado IN ('disponible', 'ocupado')")->fetchColumn();
    $domiciliariosDisponibles = $pdo->query("SELECT COUNT(*) FROM domiciliarios WHERE estado = 'disponible'")->fetchColumn();
    $domiciliariosOcupados = $pdo->query("SELECT COUNT(*) FROM domiciliarios WHERE estado = 'ocupado'")->fetchColumn();

    // Clientes activos
    $clientesActivos = $pdo->query("SELECT COUNT(*) FROM clientes WHERE estado = 'activo'")->fetchColumn();

    // Zonas activas
    $zonasActivas = $pdo->query("SELECT COUNT(*) FROM zonas WHERE estado = 'activo'")->fetchColumn();

    // Actividad reciente (últimos 10 pedidos)
    $actividadReciente = $pdo->query("
        SELECT p.id_pedido, c.nombre as cliente, d.nombre as domiciliario, p.estado, p.fecha_pedido, p.total
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
        ORDER BY p.fecha_pedido DESC
        LIMIT 10
    ")->fetchAll();

    // Top domiciliarios del día
    $topDomiciliarios = $pdo->query("
        SELECT d.nombre, COUNT(p.id_pedido) as entregas
        FROM domiciliarios d
        LEFT JOIN pedidos p ON d.id_domiciliario = p.id_domiciliario 
        AND p.estado = 'entregado' 
        AND DATE(p.fecha_pedido) = CURDATE()
        WHERE d.estado IN ('disponible', 'ocupado')
        GROUP BY d.id_domiciliario, d.nombre
        ORDER BY entregas DESC
        LIMIT 5
    ")->fetchAll();

    // Pedidos por estado (para gráfico)
    $pedidosPorEstado = $pdo->query("
        SELECT estado, COUNT(*) as total
        FROM pedidos
        WHERE DATE(fecha_pedido) = CURDATE()
        GROUP BY estado
        ORDER BY total DESC
    ")->fetchAll();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM Domicilios - Dashboard</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <?php if (tienePermiso('dashboard')): ?>
                <a href="dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">Inicio</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('pedidos')): ?>
                <a href="pedidos.php" class="menu-item">
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
                <a href="clientes.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Clientes</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('domiciliarios')): ?>
                <a href="domiciliarios.php" class="menu-item">
                    <i class="fas fa-motorcycle"></i>
                    <span class="menu-text">Domiciliarios</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('vehiculos')): ?>
                <a href="vehiculos.php" class="menu-item">
                    <i class="fas fa-car"></i>
                    <span class="menu-text">Vehiculos</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
                <a href="zonas.php" class="menu-item">
                    <i class="fas fa-map-marked-alt"></i>
                    <span class="menu-text">Zonas de Entrega</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('reportes')): ?>
                <a href="reportes.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">Reportes</span>
                </a>
            <?php endif; ?>
            <?php if (esAdmin()): ?>
                <a href="tabla_usuarios.php" class="menu-item">
                    <i class="fas fa-users-cog"></i>
                    <span class="menu-text">Gestionar Usuarios</span>
                </a>
            <?php endif; ?>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Panel de Control - SM Domicilios</h2>
            <div class="header-actions">
                <button class="btn-login" onclick="actualizarDashboard()">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
                <span class="last-update">Última actualización: <?php echo date('H:i:s'); ?></span>
                <span class="user-role" style="margin-left:20px; color:#007B55; font-weight:500;">Rol: <?php echo htmlspecialchars($_SESSION['rol']); ?></span>
            </div>
        </div>

        <!-- Tarjetas principales -->
        <div class="dashboard-cards">
            <!-- Tarjeta: Pedidos de hoy -->
            <div class="card" onclick="navigateTo('pedidos.php')">
                <div class="card-header">
                    <h3 class="card-title">Pedidos Hoy</h3>
                    <div class="card-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
                <div class="card-value"><?php echo number_format($pedidosHoy); ?></div>
                <div class="card-footer"><?php echo $pedidosEntregadosHoy; ?> entregados</div>
            </div>

            <!-- Tarjeta: Domiciliarios activos -->
            <div class="card" onclick="navigateTo('domiciliarios.php')">
                <div class="card-header">
                    <h3 class="card-title">Domiciliarios Activos</h3>
                    <div class="card-icon">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                </div>
                <div class="card-value"><?php echo number_format($domiciliariosActivos); ?></div>
                <div class="card-footer"><?php echo $domiciliariosDisponibles; ?> disponibles</div>
            </div>

            <!-- Tarjeta: Pedidos pendientes -->
            <div class="card" onclick="navigateTo('pedidos.php?estado=pendiente')">
                <div class="card-header">
                    <h3 class="card-title">Pedidos Pendientes</h3>
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="card-value"><?php echo number_format($pedidosPendientes); ?></div>
                <div class="card-footer">Requieren atención</div>
            </div>

            <!-- Tarjeta: Ingresos de hoy -->
            <div class="card" onclick="navigateTo('reportes.php')">
                <div class="card-header">
                    <h3 class="card-title">Ingresos Hoy</h3>
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="card-value">$<?php echo number_format($ingresosHoy, 2); ?></div>
                <div class="card-footer">Pedidos entregados</div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="dashboard-content">
            <!-- Gráfico y estadísticas -->
            <div class="dashboard-left">
                <!-- Gráfico de pedidos por estado -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Pedidos por Estado (Hoy)</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="estadosChart"></canvas>
                    </div>
                </div>

                <!-- Top domiciliarios del día -->
                <div class="stats-card">
                    <div class="stats-header">
                        <h3>Top Domiciliarios del Día</h3>
                    </div>
                    <div class="stats-list">
                        <?php if (empty($topDomiciliarios)): ?>
                            <p class="no-data">No hay entregas registradas hoy</p>
                        <?php else: ?>
                            <?php foreach ($topDomiciliarios as $index => $domiciliario): ?>
                                <div class="stat-item">
                                    <div class="stat-rank">#<?php echo $index + 1; ?></div>
                                    <div class="stat-info">
                                        <div class="stat-name"><?php echo htmlspecialchars($domiciliario['nombre']); ?></div>
                                        <div class="stat-value"><?php echo $domiciliario['entregas']; ?> entregas</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Actividad reciente -->
            <div class="dashboard-right">
                <div class="activity-card">
                    <div class="activity-header">
                        <h3>Actividad Reciente</h3>
                        <button class="btn-refresh" onclick="actualizarActividad()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="activity-list">
                        <?php if (empty($actividadReciente)): ?>
                            <p class="no-data">No hay actividad reciente</p>
                        <?php else: ?>
                            <?php foreach ($actividadReciente as $actividad): ?>
                                <div class="activity-item" onclick="verPedido(<?php echo $actividad['id_pedido']; ?>)">
                                    <div class="activity-icon">
                                        <?php
                                        switch ($actividad['estado']) {
                                            case 'entregado':
                                                echo '<i class="fas fa-check-circle" style="color: #2ed573;"></i>';
                                                break;
                                            case 'pendiente':
                                                echo '<i class="fas fa-clock" style="color: #ffa502;"></i>';
                                                break;
                                            case 'cancelado':
                                                echo '<i class="fas fa-times-circle" style="color: #ff4757;"></i>';
                                                break;
                                            default:
                                                echo '<i class="fas fa-shopping-bag" style="color: #747d8c;"></i>';
                                        }
                                        ?>
                                    </div>
                                    <div class="activity-details">
                                        <div class="activity-title">
                                            Pedido #<?php echo $actividad['id_pedido']; ?> -
                                            <?php echo htmlspecialchars($actividad['cliente']); ?>
                                        </div>
                                        <div class="activity-subtitle">
                                            <?php echo ucfirst($actividad['estado']); ?> -
                                            $<?php echo number_format($actividad['total'], 2); ?>
                                        </div>
                                        <div class="activity-time">
                                            <?php echo date('H:i', strtotime($actividad['fecha_pedido'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Resumen rápido de clientes y zonas activas -->
                <div class="quick-stats">
                    <div class="quick-stat">
                        <div class="quick-stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="quick-stat-info">
                            <div class="quick-stat-value"><?php echo number_format($clientesActivos); ?></div>
                            <div class="quick-stat-label">Clientes Activos</div>
                        </div>
                    </div>
                    <div class="quick-stat">
                        <div class="quick-stat-icon">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="quick-stat-info">
                            <div class="quick-stat-value"><?php echo number_format($zonasActivas); ?></div>
                            <div class="quick-stat-label">Zonas Activas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- Datos para el gráfico de pedidos por estado ---
        const estadosData = <?php echo json_encode($pedidosPorEstado); ?>;

        // Gráfico de pedidos por estado usando Chart.js
        const ctx = document.getElementById('estadosChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: estadosData.map(item => item.estado.charAt(0).toUpperCase() + item.estado.slice(1)),
                datasets: [{
                    data: estadosData.map(item => item.total),
                    backgroundColor: ['#2ed573', '#ffa502', '#ff4757', '#747d8c'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });

        // Funciones de navegación y actualización
        function navigateTo(url) {
            window.location.href = url;
        }

        function verPedido(id) {
            window.location.href = `pedidos.php?pedido=${id}`;
        }

        function actualizarDashboard() {
            location.reload();
        }

        function actualizarActividad() {
            // Aquí se podría implementar una actualización AJAX
            location.reload();
        }

        // Actualizar automáticamente la hora de última actualización cada 5 minutos
        setInterval(() => {
            const lastUpdate = document.querySelector('.last-update');
            if (lastUpdate) {
                lastUpdate.textContent = 'Última actualización: ' + new Date().toLocaleTimeString();
            }
        }, 300000); // 5 minutos
    </script>
</body>

</html>