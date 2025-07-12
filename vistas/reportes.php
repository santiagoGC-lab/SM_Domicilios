<?php
require_once '../servicios/verificar_permisos.php';
verificarAcceso('reportes');

require_once '../servicios/conexion.php';

// Obtener estadísticas para los reportes
try {
    // Estadísticas generales
    $totalPedidos = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
    $pedidosHoy = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE()")->fetchColumn();
    $ingresosHoy = $pdo->query("SELECT SUM(total) FROM pedidos WHERE DATE(fecha_pedido) = CURDATE() AND estado = 'entregado'")->fetchColumn() ?? 0;
    $pedidosPendientes = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();
    
    // Pedidos por zona
    $pedidosPorZona = $pdo->query("
        SELECT z.nombre, COUNT(p.id_pedido) as total, SUM(p.total) as ingresos
        FROM zonas z
        LEFT JOIN pedidos p ON z.id_zona = p.id_zona
        WHERE z.estado = 'activo'
        GROUP BY z.id_zona, z.nombre
        ORDER BY total DESC
    ")->fetchAll();
    
    // Domiciliarios más activos
    $domiciliariosActivos = $pdo->query("
        SELECT d.nombre, COUNT(p.id_pedido) as entregas, SUM(p.total) as ingresos
        FROM domiciliarios d
        LEFT JOIN pedidos p ON d.id_domiciliario = p.id_domiciliario AND p.estado = 'entregado'
        WHERE d.estado IN ('disponible', 'ocupado')
        GROUP BY d.id_domiciliario, d.nombre
        ORDER BY entregas DESC
        LIMIT 5
    ")->fetchAll();
    
    // Clientes más frecuentes
    $clientesFrecuentes = $pdo->query("
        SELECT c.nombre, COUNT(p.id_pedido) as pedidos, SUM(p.total) as total_gastado
        FROM clientes c
        LEFT JOIN pedidos p ON c.id_cliente = p.id_cliente
        WHERE c.estado = 'activo'
        GROUP BY c.id_cliente, c.nombre
        HAVING pedidos > 0
        ORDER BY pedidos DESC
        LIMIT 5
    ")->fetchAll();
    
    // Pedidos por estado
    $pedidosPorEstado = $pdo->query("
        SELECT estado, COUNT(*) as total
        FROM pedidos
        GROUP BY estado
        ORDER BY total DESC
    ")->fetchAll();
    
    // Pedidos de los últimos 7 días
    $pedidosUltimos7Dias = $pdo->query("
        SELECT DATE(fecha_pedido) as fecha, COUNT(*) as total, SUM(total) as ingresos
        FROM pedidos
        WHERE fecha_pedido >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(fecha_pedido)
        ORDER BY fecha DESC
    ")->fetchAll();
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SM - Reportes</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/reportes.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <?php if (tienePermiso('dashboard')): ?>
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <?php endif; ?>
            <a href="pedidos.php" class="menu-item">
                <i class="fas fa-shopping-bag"></i>
                <span class="menu-text">Pedidos</span>
            </a>
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Clientes</span>
            </a>
            <?php if (tienePermiso('domiciliarios')): ?>
            <a href="domiciliarios.php" class="menu-item">
                <i class="fas fa-motorcycle"></i>
                <span class="menu-text">Domiciliarios</span>
            </a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
            <a href="zonas.php" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                <span class="menu-text">Zonas de Entrega</span>
            </a>
            <?php endif; ?>
            <a href="reportes.php" class="menu-item active">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <?php if (esAdmin()): ?>
            <a href="tabla_usuarios.php" class="menu-item"><i class="fas fa-users-cog"></i><span class="menu-text">Gestionar Usuarios</span></a>
            <?php endif; ?>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Reportes y Estadísticas</h2>
            <div class="header-actions">
                <button class="btn-login" onclick="exportarReporte('general')">
                    <i class="fas fa-file-pdf"></i> Exportar General PDF
                </button>
                <button class="btn-login" onclick="exportarReporte('detallado')">
                    <i class="fas fa-file-pdf"></i> Exportar Detallado PDF
                </button>
            </div>
        </div>

        <!-- Estadísticas principales -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($totalPedidos); ?></h3>
                    <p>Total Pedidos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($pedidosHoy); ?></h3>
                    <p>Pedidos Hoy</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3>$<?php echo number_format($ingresosHoy, 2); ?></h3>
                    <p>Ingresos Hoy</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?php echo number_format($pedidosPendientes); ?></h3>
                    <p>Pendientes</p>
                </div>
            </div>
        </div>

        <!-- Gráficos y reportes -->
        <div class="reports-grid">
            <!-- Gráfico de pedidos por estado -->
            <div class="report-card">
                <div class="report-header">
                    <h3>Pedidos por Estado</h3>
                    <button class="btn-export" onclick="exportarReporte('estados')">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                </div>
                <div class="chart-container">
                    <canvas id="estadosChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de pedidos por zona -->
            <div class="report-card">
                <div class="report-header">
                    <h3>Pedidos por Zona</h3>
                    <button class="btn-export" onclick="exportarReporte('zonas')">
                        <i class="fas fa-file-pdf"></i>
                    </button>
                </div>
                <div class="chart-container">
                    <canvas id="zonasChart"></canvas>
                </div>
            </div>

            <!-- Gráfico de ingresos últimos 7 días -->
            <div class="report-card">
                <div class="report-header">
                    <h3>Ingresos Últimos 7 Días</h3>
                    <button class="btn-export" onclick="exportarReporte('ingresos')">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <div class="chart-container">
                    <canvas id="ingresosChart"></canvas>
                </div>
            </div>

            <!-- Tabla de domiciliarios más activos -->
            <div class="report-card">
                <div class="report-header">
                    <h3>Domiciliarios Más Activos</h3>
                    <button class="btn-export" onclick="exportarReporte('domiciliarios')">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Domiciliario</th>
                                <th>Entregas</th>
                                <th>Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($domiciliariosActivos as $domiciliario): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($domiciliario['nombre']); ?></td>
                                    <td><?php echo $domiciliario['entregas']; ?></td>
                                    <td>$<?php echo number_format($domiciliario['ingresos'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabla de clientes más frecuentes -->
            <div class="report-card">
                <div class="report-header">
                    <h3>Clientes Más Frecuentes</h3>
                    <button class="btn-export" onclick="exportarReporte('clientes')">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Pedidos</th>
                                <th>Total Gastado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientesFrecuentes as $cliente): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                    <td><?php echo $cliente['pedidos']; ?></td>
                                    <td>$<?php echo number_format($cliente['total_gastado'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tabla de pedidos por zona -->
            <div class="report-card">
                <div class="report-header">
                    <h3>Detalle por Zona</h3>
                    <button class="btn-export" onclick="exportarReporte('detalle_zonas')">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Zona</th>
                                <th>Pedidos</th>
                                <th>Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidosPorZona as $zona): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($zona['nombre']); ?></td>
                                    <td><?php echo $zona['total']; ?></td>
                                    <td>$<?php echo number_format($zona['ingresos'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Datos para los gráficos
        const estadosData = <?php echo json_encode($pedidosPorEstado); ?>;
        const zonasData = <?php echo json_encode($pedidosPorZona); ?>;
        const ingresosData = <?php echo json_encode($pedidosUltimos7Dias); ?>;

        // Gráfico de pedidos por estado
        const estadosCtx = document.getElementById('estadosChart').getContext('2d');
        new Chart(estadosCtx, {
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
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de pedidos por zona
        const zonasCtx = document.getElementById('zonasChart').getContext('2d');
        new Chart(zonasCtx, {
            type: 'bar',
            data: {
                labels: zonasData.map(item => item.nombre),
                datasets: [{
                    label: 'Pedidos',
                    data: zonasData.map(item => item.total),
                    backgroundColor: '#015938',
                    borderColor: '#007B55',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Gráfico de ingresos últimos 7 días
        const ingresosCtx = document.getElementById('ingresosChart').getContext('2d');
        new Chart(ingresosCtx, {
            type: 'line',
            data: {
                labels: ingresosData.map(item => new Date(item.fecha).toLocaleDateString('es-ES', {day: '2-digit', month: '2-digit'})),
                datasets: [{
                    label: 'Ingresos ($)',
                    data: ingresosData.map(item => item.ingresos || 0),
                    borderColor: '#2ed573',
                    backgroundColor: 'rgba(46, 213, 115, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Función para exportar reportes
        function exportarReporte(tipo) {
            const url = `../servicios/generar_reporte_pdf.php?tipo=${tipo}`;
            window.open(url, '_blank');
        }
    </script>
</body>

</html>