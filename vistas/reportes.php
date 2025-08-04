<?php
// --- Verificación de permisos y conexión a la base de datos ---
require_once '../servicios/verificar_permisos.php';
verificarAcceso('reportes');

require_once '../servicios/conexion.php';

// Redirigir automáticamente con mes y año actual si no están en la URL
if (!isset($_GET['mes']) || !isset($_GET['anio'])) {
    $mesActual = date('n');
    $anioActual = date('Y');
    header("Location: reportes.php?mes=$mesActual&anio=$anioActual");
    exit;
}

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

    // Domiciliarios más activos (solo del historial de hoy)
    $domiciliariosActivos = $pdo->query("
        SELECT 
            domiciliario_nombre as nombre,
            COUNT(*) as entregas, 
            SUM(total) as ingresos
        FROM historico_pedidos
        WHERE DATE(fecha_completado) = CURDATE() 
            AND estado = 'entregado'
            AND domiciliario_nombre IS NOT NULL
        GROUP BY domiciliario_nombre
        ORDER BY entregas DESC
        LIMIT 5
    ")->fetchAll();

    // Clientes más frecuentes (solo del historial de hoy)
    $clientesFrecuentes = $pdo->query("
        SELECT 
            cliente_nombre as nombre,
            COUNT(*) as pedidos,
            SUM(total) as total_gastado
        FROM historico_pedidos
        WHERE DATE(fecha_completado) = CURDATE()
            AND estado = 'entregado'
        GROUP BY cliente_nombre
        ORDER BY pedidos DESC
        LIMIT 5
    ")->fetchAll();

    // Pedidos por estado (combinar pedidos activos + historial de hoy)
    $pedidosActivos = $pdo->query("
        SELECT estado, COUNT(*) as total
        FROM pedidos
        WHERE DATE(fecha_pedido) = CURDATE()
        GROUP BY estado
    ")->fetchAll();

    $pedidosHistorial = $pdo->query("
        SELECT estado, COUNT(*) as total
        FROM historico_pedidos
        WHERE DATE(fecha_completado) = CURDATE()
        GROUP BY estado
    ")->fetchAll();

    // Combinar ambos arrays
    $pedidosPorEstado = [];
    $estadosCount = [];

    foreach ($pedidosActivos as $pedido) {
        $estadosCount[$pedido['estado']] = ($estadosCount[$pedido['estado']] ?? 0) + $pedido['total'];
    }

    foreach ($pedidosHistorial as $pedido) {
        $estadosCount[$pedido['estado']] = ($estadosCount[$pedido['estado']] ?? 0) + $pedido['total'];
    }

    foreach ($estadosCount as $estado => $total) {
        $pedidosPorEstado[] = ['estado' => $estado, 'total' => $total];
    }

    // Detalle por zona (solo del historial de hoy)
    // Detalle por zona - Zonas donde más se pidieron domicilios (todos los tiempos)
    // Detalle por zona - Últimos 30 días (más relevante)
    // Detalle por zona (versión simple y funcional)
    // Detalle por zona con datos reales del historial
    $zonaDetalle = $pdo->query("
        SELECT 
            zona_nombre as zona,
            COUNT(*) as pedidos,
            SUM(total) as ingresos
        FROM historico_pedidos
        WHERE estado = 'entregado'
        GROUP BY zona_nombre
        
        UNION ALL
        
        SELECT 
            z.nombre as zona,
            0 as pedidos,
            0 as ingresos
        FROM zonas z
        WHERE z.estado = 'activo'
            AND z.nombre NOT IN (
                SELECT DISTINCT zona_nombre 
                FROM historico_pedidos 
                WHERE estado = 'entregado'
            )
        
        ORDER BY pedidos DESC, zona ASC
    ")->fetchAll();

    // Pedidos de los últimos 7 días (solo historial)
    $pedidosUltimos7Dias = $pdo->query("
        SELECT 
            DATE(fecha_completado) as fecha, 
            COUNT(*) as total, 
            SUM(total) as ingresos
        FROM historico_pedidos
        WHERE fecha_completado >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            AND estado = 'entregado'
        GROUP BY DATE(fecha_completado)
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
            <?php // Menú lateral, muestra opciones según permisos del usuario 
            ?>
            <?php if (tienePermiso('dashboard')): ?>
                <a href="dashboard.php" class="menu-item">
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
                <a href="reportes.php" class="menu-item active">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">Reportes</span>
                </a>
            <?php endif; ?>
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
        <!-- Tarjetas de resumen de KPIs solicitados -->
        <?php
        // --- Cálculos para los KPIs del reporte (ESTADÍSTICAS DIARIAS) ---

        // 1. Total de pedidos creados HOY (siguen en tabla pedidos hasta ser procesados)
        $totalPedidosHoy = $pdo->query("
            SELECT COUNT(*) 
            FROM pedidos 
            WHERE DATE(fecha_pedido) = CURDATE()
        ")->fetchColumn();

        // 2. Pedidos entregados HOY (están en historial)
        $pedidosEntregadosHoy = $pdo->query("
            SELECT hp.hora_salida, hp.hora_llegada, hp.tiempo_estimado
            FROM historico_pedidos hp
            WHERE DATE(hp.fecha_completado) = CURDATE() AND hp.estado = 'entregado'
        ")->fetchAll();

        // 3. Domicilios enviados HOY (en camino + entregados)
        $enCaminoHoy = $pdo->query("
            SELECT COUNT(*) 
            FROM pedidos 
            WHERE DATE(fecha_pedido) = CURDATE() AND estado = 'en_camino'
        ")->fetchColumn();

        $entregadosHoy = $pdo->query("
            SELECT COUNT(*) 
            FROM historico_pedidos 
            WHERE DATE(fecha_completado) = CURDATE() AND estado = 'entregado'
        ")->fetchColumn();

        $domiciliosEnviadosHoy = $enCaminoHoy + $entregadosHoy;

        // 4. Valor total de domicilios entregados HOY (solo del historial)
        $valorDomiciliosHoy = $pdo->query("
            SELECT COALESCE(SUM(total), 0) 
            FROM historico_pedidos 
            WHERE DATE(fecha_completado) = CURDATE() AND estado = 'entregado'
        ")->fetchColumn();

        // 5. Pedidos cancelados HOY (están en historial)
        $canceladosHoy = $pdo->query("
            SELECT COUNT(*) 
            FROM historico_pedidos 
            WHERE DATE(fecha_completado) = CURDATE() AND estado = 'cancelado'
        ")->fetchColumn();

        // 6. Cálculo de tiempo promedio y cumplimiento HOY
        $totalTiempo = 0;
        $numEntregas = 0;
        $cumplidos = 0;

        foreach ($pedidosEntregadosHoy as $pedido) {
            if (!empty($pedido['hora_salida']) && !empty($pedido['hora_llegada']) && !empty($pedido['tiempo_estimado'])) {
                $salida = strtotime($pedido['hora_salida']);
                $llegada = strtotime($pedido['hora_llegada']);
                $tiempoReal = ($llegada - $salida) / 60; // minutos
                $totalTiempo += $tiempoReal;
                $numEntregas++;

                if ($tiempoReal <= $pedido['tiempo_estimado']) {
                    $cumplidos++;
                }
            }
        }

        $tiempoPromedio = $numEntregas > 0 ? round($totalTiempo / $numEntregas, 2) : 0;
        $cumplimiento = $numEntregas > 0 ? round(($cumplidos / $numEntregas) * 100, 2) : 0;
        ?>
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-content">
                    <h3><?php echo $tiempoPromedio; ?> min</h3>
                    <p>Tiempo Promedio Entregas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                <div class="stat-content">
                    <h3><?php echo $cumplimiento; ?>%</h3>
                    <p>% Cumplimiento de Entregas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-truck"></i></div>
                <div class="stat-content">
                    <h3><?php echo $domiciliosEnviadosHoy; ?></h3>
                    <p>Domicilios Enviados Hoy</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-content">
                    <h3>$<?php echo number_format($valorDomiciliosHoy, 2); ?></h3>
                    <p>Valor Domicilios Hoy</p>
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
                            <?php endforeach;
                            // Detalle por zona (mostrar todas las zonas activas)
                            // **Versión más segura (recomendada):**
                            // Detalle por zona - versión segura
                            try {
                                $zonaDetalle = $pdo->query("
                                    SELECT 
                                        z.nombre as zona,
                                        COALESCE(stats.pedidos, 0) as pedidos,
                                        COALESCE(stats.ingresos, 0) as ingresos
                                    FROM zonas z
                                    LEFT JOIN (
                                        SELECT 
                                            zona_nombre,
                                            COUNT(*) as pedidos,
                                            SUM(total) as ingresos
                                        FROM historico_pedidos
                                        WHERE estado = 'entregado'
                                        GROUP BY zona_nombre
                                    ) stats ON z.nombre = stats.zona_nombre
                                    WHERE z.estado = 'activo'
                                    ORDER BY pedidos DESC, z.nombre ASC
                                ")->fetchAll();
                            } catch (Exception $e) {
                                // Si hay error, mostrar zonas vacías
                                $zonaDetalle = $pdo->query("
                                    SELECT 
                                        nombre as zona,
                                        0 as pedidos,
                                        0 as ingresos
                                    FROM zonas
                                    WHERE estado = 'activo'
                                    ORDER BY nombre ASC
                                ")->fetchAll();
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Tabla de detalle por zona -->
            <div class="report-card">
                <div class="report-header">
                    <h3>Detalle por Zona</h3>
                    <button class="btn-export" onclick="exportarReporte('zonas')">
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
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        ?>
        <div class="report-card" style="max-width:100%;margin-top:30px;">
            <div class="report-header">
                <h3>Pedidos Mensuales Archivados</h3>
            </div>
            <form method="GET" style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
                <label for="mes">Mes:</label>
                <?php
                $mesActual = date('n');
                $anioActual = date('Y');
                $mesSeleccionado = isset($_GET['mes']) ? intval($_GET['mes']) : $mesActual;
                $anioSeleccionado = isset($_GET['anio']) ? intval($_GET['anio']) : $anioActual;
                ?>
                <select name="mes" id="mes" required class="select-mes">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php if ($mesSeleccionado == $m) echo 'selected'; ?>>
                            <?php echo $meses_es[$m]; ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <label for="anio">Año:</label>
                <select name="anio" id="anio" required class="select-anio">
                    <?php for ($a = $anioActual; $a >= $anioActual - 5; $a--): ?>
                        <option value="<?php echo $a; ?>" <?php if ($anioSeleccionado == $a) echo 'selected'; ?>><?php echo $a; ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn-login"><i class="fas fa-search"></i> Consultar</button>
            </form>
            <?php
            $pedidosMensuales = [];
            if (isset($_GET['mes']) && isset($_GET['anio'])) {
                $mes = intval($_GET['mes']);
                $anio = intval($_GET['anio']);
                $db = new mysqli('localhost', 'root', 'root', 'sm_domicilios');
                $db->set_charset('utf8mb4');
                // Consultar pedidos entregados del mes en historico_pedidos
                $sqlHistorico = "SELECT id_pedido_original, cliente_nombre, domiciliario_nombre, zona_nombre, estado, fecha_pedido, hora_salida, hora_llegada, total
                FROM historico_pedidos
                WHERE YEAR(fecha_pedido) = $anio AND MONTH(fecha_pedido) = $mes
                ORDER BY fecha_pedido DESC";
                $resultHistorico = $db->query($sqlHistorico);
                while ($row = $resultHistorico->fetch_assoc()) {
                    $pedidosMensuales[] = $row;
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
                            <th>Hora Salida</th>
                            <th>Hora Llegada</th>
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
                                    <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                                    <td><?php echo !empty($pedido['hora_salida']) ? date('H:i', strtotime($pedido['hora_salida'])) : '-'; ?></td>
                                    <td><?php echo !empty($pedido['hora_llegada']) ? date('H:i', strtotime($pedido['hora_llegada'])) : '-'; ?></td>
                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align:center;">No hay pedidos para este mes.</td>
                            </tr>
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

        // Función para asignar colores según el estado
        function getColorByEstado(estado) {
            switch(estado.toLowerCase()) {
                case 'entregado':
                case 'completado':
                    return '#28a745'; // Verde para entregado
                case 'pendiente':
                case 'en_camino':
                case 'despachado':
                    return '#ffc107'; // Naranja para pendiente/en proceso
                case 'cancelado':
                case 'rechazado':
                    return '#dc3545'; // Rojo para cancelado
                default:
                    return '#6c757d'; // Gris para otros estados
            }
        }

        // Gráfico de pedidos por estado usando Chart.js
        const estadosCtx = document.getElementById('estadosChart').getContext('2d');
        new Chart(estadosCtx, {
            type: 'doughnut',
            data: {
                labels: estadosData.map(item => item.estado.charAt(0).toUpperCase() + item.estado.slice(1)),
                datasets: [{
                    data: estadosData.map(item => item.total),
                    backgroundColor: estadosData.map(item => getColorByEstado(item.estado)),
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

        // Función para exportar reportes en PDF o descargar tablas
        function exportarReporte(tipo) {
            const formData = new FormData();
            formData.append('accion', 'exportar');
            formData.append('tipo', tipo); // Agregar el tipo de reporte
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
        .select-mes,
        .select-anio {
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

        .select-mes:focus,
        .select-anio:focus {
            border-color: #007B55;
            background: #fff;
        }
    </style>
</body>

</html>