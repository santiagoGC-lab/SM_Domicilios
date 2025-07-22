<?php
// --- Verificación de permisos y conexión a la base de datos ---
require_once '../servicios/verificar_permisos.php';
verificarAcceso('reportes');

require_once '../servicios/conexion.php';

// --- Obtener estadísticas para los reportes ---
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
            <?php // Menú lateral, muestra opciones según permisos del usuario ?>
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
        <!-- Tarjetas de resumen de estadísticas principales -->
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

        <!-- Pedidos Mensuales Archivados (ahora ocupa todo el ancho, debajo de los reportes) -->
        <!-- Tabla de pedidos mensuales archivados por mes y año -->
        <?php
        $meses_es = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        ?>
        <div class="report-card" style="max-width:100%;margin-top:30px;">
            <div class="report-header">
                <h3>Pedidos Mensuales Archivados</h3>
            </div>
            <form method="GET" style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
                <label for="mes">Mes:</label>
                <select name="mes" id="mes" required class="select-mes">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php if(isset($_GET['mes']) && $_GET['mes'] == $m) echo 'selected'; ?>>
                            <?php echo $meses_es[$m]; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <label for="anio">Año:</label>
                <select name="anio" id="anio" required class="select-anio">
                    <?php $anioActual = date('Y');
                    for ($a = $anioActual; $a >= $anioActual-5; $a--): ?>
                        <option value="<?php echo $a; ?>" <?php if(isset($_GET['anio']) && $_GET['anio'] == $a) echo 'selected'; ?>><?php echo $a; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn-login"><i class="fas fa-search"></i> Consultar</button>
            </form>
            <?php
            $pedidosMensuales = [];
            if (isset($_GET['mes']) && isset($_GET['anio'])) {
                $mes = intval($_GET['mes']);
                $anio = intval($_GET['anio']);
                $mesActual = intval(date('n'));
                $anioActual = intval(date('Y'));
                $db = new mysqli('localhost', 'root', 'root', 'sm_domicilios');
                $db->set_charset('utf8mb4');
                // Si es el mes y año actual, consultar ambos: activos y archivados
                if ($mes === $mesActual && $anio === $anioActual) {
                    // Pedidos activos de este mes
                    $sqlActivos = "SELECT p.id_pedido AS id_pedido_original, c.nombre AS cliente_nombre, d.nombre AS domiciliario_nombre, z.nombre AS zona_nombre, p.estado, p.fecha_pedido, p.total
                    FROM pedidos p
                    LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                    LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
                    LEFT JOIN zonas z ON p.id_zona = z.id_zona
                    WHERE YEAR(p.fecha_pedido) = $anio AND MONTH(p.fecha_pedido) = $mes
                    ORDER BY p.fecha_pedido DESC";
                    $resultActivos = $db->query($sqlActivos);
                    while ($row = $resultActivos->fetch_assoc()) {
                        $pedidosMensuales[] = $row;
                    }
                    // Pedidos archivados de este mes
                    $sqlArchivados = "SELECT * FROM pedidos_mensuales WHERE mes = $mes AND anio = $anio ORDER BY fecha_pedido DESC";
                    $resultArchivados = $db->query($sqlArchivados);
                    while ($row = $resultArchivados->fetch_assoc()) {
                        $pedidosMensuales[] = $row;
                    }
                } else {
                    // Solo pedidos archivados
                    $sql = "SELECT * FROM pedidos_mensuales WHERE mes = $mes AND anio = $anio ORDER BY fecha_pedido DESC";
                    $result = $db->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        $pedidosMensuales[] = $row;
                    }
                }
                $db->close();
            }
            ?>
            <div class="table-container" style="width:100%;">
                <table class="data-table" style="width:100%;">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Domiciliario</th>
                            <th>Zona</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pedidosMensuales) > 0): ?>
                            <?php foreach ($pedidosMensuales as $pedido): ?>
                                <tr>
                                    <td>#<?php echo $pedido['id_pedido_original']; ?></td>
                                    <td><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($pedido['domiciliario_nombre'] ?? 'No asignado'); ?></td>
                                    <td><?php echo htmlspecialchars($pedido['zona_nombre'] ?? ''); ?></td>
                                    <td><span class="estado-<?php echo strtolower($pedido['estado']); ?> estado"><?php echo ucfirst($pedido['estado']); ?></span></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center;">No hay pedidos para este mes.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // --- Datos para los gráficos de reportes ---
        const estadosData = <?php echo json_encode($pedidosPorEstado); ?>;
        const zonasData = <?php echo json_encode($pedidosPorZona); ?>;
        const ingresosData = <?php echo json_encode($pedidosUltimos7Dias); ?>;

        // Gráfico de pedidos por estado usando Chart.js
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

        // Gráfico de pedidos por zona usando Chart.js
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

        // Gráfico de ingresos últimos 7 días usando Chart.js
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

        // Función para exportar reportes en PDF o descargar tablas
        function exportarReporte(tipo) {
            const formData = new FormData();
            formData.append('accion', 'exportar');
            fetch('../servicios/reportes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reporte_${tipo}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            })
            .catch(error => {
                console.error('Error al exportar el reporte:', error);
                alert('Error al exportar el reporte.');
            });
        }
    </script>
    <style>
        .select-mes, .select-anio {
            padding: 8px 12px;
            border: 1px solid #b2b2b2;
            border-radius: 6px;
            font-size: 15px;
            background: #f9f9f9;
            color: #015938;
            margin-right: 8px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.2s;
        }
        .select-mes:focus, .select-anio:focus {
            border-color: #007B55;
            background: #fff;
        }
    </style>
</body>

</html>