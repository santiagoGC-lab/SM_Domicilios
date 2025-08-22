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

// Obtener mes y año de la URL
$mes = (int)$_GET['mes'];
$anio = (int)$_GET['anio'];

// --- Obtener estadísticas para los reportes ---
try {
    // Estadísticas generales del mes
    $totalPedidos = $pdo->prepare("SELECT COUNT(*) FROM historico_pedidos WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ?");
    $totalPedidos->execute([$mes, $anio]);
    $totalPedidos = $totalPedidos->fetchColumn();
    
    $pedidosEntregados = $pdo->prepare("SELECT COUNT(*) FROM historico_pedidos WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ? AND estado = 'entregado'");
    $pedidosEntregados->execute([$mes, $anio]);
    $pedidosEntregados = $pedidosEntregados->fetchColumn();
    
    $ingresosMes = $pdo->prepare("SELECT SUM(total) FROM historico_pedidos WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ? AND estado = 'entregado'");
    $ingresosMes->execute([$mes, $anio]);
    $ingresosMes = $ingresosMes->fetchColumn() ?? 0;
    
    $pedidosCancelados = $pdo->prepare("SELECT COUNT(*) FROM historico_pedidos WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ? AND estado = 'cancelado'");
    $pedidosCancelados->execute([$mes, $anio]);
    $pedidosCancelados = $pedidosCancelados->fetchColumn();

    // Domiciliarios más activos del mes
    $domiciliariosActivos = $pdo->prepare("
        SELECT 
            domiciliario_nombre as nombre,
            COUNT(*) as entregas, 
            SUM(total) as ingresos
        FROM historico_pedidos
        WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ?
            AND estado = 'entregado'
            AND domiciliario_nombre IS NOT NULL
        GROUP BY domiciliario_nombre
        ORDER BY entregas DESC
        LIMIT 5
    ");
    $domiciliariosActivos->execute([$mes, $anio]);
    $domiciliariosActivos = $domiciliariosActivos->fetchAll();

    // Clientes más frecuentes del mes
    $clientesFrecuentes = $pdo->prepare("
        SELECT 
            cliente_nombre as nombre,
            COUNT(*) as pedidos,
            SUM(total) as total_gastado
        FROM historico_pedidos
        WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ?
            AND estado = 'entregado'
        GROUP BY cliente_nombre
        ORDER BY pedidos DESC
        LIMIT 5
    ");
    $clientesFrecuentes->execute([$mes, $anio]);
    $clientesFrecuentes = $clientesFrecuentes->fetchAll();

    // Eliminar consulta de pedidos por estado del mes
    // CÓDIGO ELIMINADO: consulta $pedidosPorEstado

    // Detalle por zona del mes
    $zonaDetalle = $pdo->prepare("
        SELECT 
            zona_nombre as zona,
            COUNT(*) as pedidos,
            SUM(total) as ingresos
        FROM historico_pedidos
        WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ?
            AND estado = 'entregado' 
            AND zona_nombre IS NOT NULL 
            AND zona_nombre != ''
        GROUP BY zona_nombre
        ORDER BY pedidos DESC, zona ASC
    ");
    $zonaDetalle->execute([$mes, $anio]);
    $zonaDetalle = $zonaDetalle->fetchAll();

    // Pedidos diarios del mes
    $pedidosDiariosMes = $pdo->prepare("
        SELECT 
            DAY(fecha_completado) as dia,
            COUNT(*) as total, 
            SUM(total) as ingresos
        FROM historico_pedidos
        WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ?
            AND estado = 'entregado'
        GROUP BY DAY(fecha_completado)
        ORDER BY dia ASC
    ");
    $pedidosDiariosMes->execute([$mes, $anio]);
    $pedidosDiariosMes = $pedidosDiariosMes->fetchAll();

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

        <?php
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
                    <p>Tiempo Promedio (Hoy)</p>

                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-percentage"></i></div>
                <div class="stat-content">
                    <h3><?php echo $cumplimiento; ?>%</h3>
                    <p>% De cumplimiento (Hoy)</p>

                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-truck"></i></div>
                <div class="stat-content">
                    <h3><?php echo $domiciliosEnviadosHoy; ?></h3>
                    <p>Domicilios Enviados (Hoy)</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-content">
                    <h3>$<?php echo number_format($valorDomiciliosHoy, 2); ?></h3>
                    <p>Total Domicilios (Hoy)</p>

                </div>
            </div>
        </div>

        <!-- Gráficos y reportes -->
        <div class="reports-grid">

            <!-- Tabla de domiciliarios más activos -->
            <div class="report-card">
                <div class="report-header">
                    <h3>Domiciliarios Más Activos (MES)</h3>
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
                    <h3>Clientes Más Frecuentes (MES)</h3>
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

            <!-- Tabla de detalle por zona -->
            <div class="report-card">
                <div class="report-header">
                    <h3>Detalle por Zona (MES)</h3>
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
                            <?php foreach ($zonaDetalle as $zona): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($zona['zona']); ?></td>
                                    <td><?php echo $zona['pedidos']; ?></td>
                                    <td>$<?php echo number_format($zona['ingresos'] ?? 0, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php
        // Cambiar las estadísticas principales para mostrar datos del mes
        $meses_es = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        $mesNombre = $meses_es[$mes];
        
        // Calcular estadísticas del mes
        $pedidosEntregadosMes = $pdo->prepare("
            SELECT COUNT(*) 
            FROM historico_pedidos 
            WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ? AND estado = 'entregado'
        ");
        $pedidosEntregadosMes->execute([$mes, $anio]);
        $entregadosMes = $pedidosEntregadosMes->fetchColumn();
        
        $valorDomiciliosMes = $pdo->prepare("
            SELECT COALESCE(SUM(total), 0) 
            FROM historico_pedidos 
            WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ? AND estado = 'entregado'
        ");
        $valorDomiciliosMes->execute([$mes, $anio]);
        $valorMes = $valorDomiciliosMes->fetchColumn();
        
        $canceladosMes = $pdo->prepare("
            SELECT COUNT(*) 
            FROM historico_pedidos 
            WHERE MONTH(fecha_completado) = ? AND YEAR(fecha_completado) = ? AND estado = 'cancelado'
        ");
        $canceladosMes->execute([$mes, $anio]);
        $canceladosMes = $canceladosMes->fetchColumn();
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
                // Consultar pedidos entregados del mes en historico_pedidos con información adicional
                $sqlHistorico = "SELECT hp.id_pedido_original, hp.cliente_nombre, hp.domiciliario_nombre, hp.zona_nombre, hp.estado, hp.fecha_pedido, hp.hora_salida, hp.hora_llegada, hp.total,
                               hp.cliente_telefono, hp.cliente_direccion, hp.cantidad_paquetes, hp.tiempo_estimado,
                               z.barrio
                FROM historico_pedidos hp
                LEFT JOIN zonas z ON hp.id_zona = z.id_zona
                WHERE YEAR(hp.fecha_pedido) = $anio AND MONTH(hp.fecha_pedido) = $mes
                ORDER BY hp.fecha_pedido DESC";
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
                            <th>Cliente</th>
                            <th>Domiciliario</th>
                            <th>Zona</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pedidosMensuales) > 0): ?>
                            <?php foreach ($pedidosMensuales as $pedido): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pedido['cliente_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($pedido['domiciliario_nombre'] ?? 'No asignado'); ?></td>
                                    <td><?php echo htmlspecialchars($pedido['zona_nombre'] ?? ''); ?></td>
                                    <td><span class="estado-<?php echo strtolower($pedido['estado']); ?> estado"><?php echo ucfirst($pedido['estado']); ?></span></td>
                                    <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                    <td>
                                        <button class="btn btn-info btn-sm" onclick="verDetallesPedido(<?php echo htmlspecialchars(json_encode($pedido), ENT_QUOTES, 'UTF-8'); ?>)">
                                            <i class="fas fa-eye"></i> Ver Detalles
                                        </button>
                                    </td>
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

            <!-- Modal para mostrar detalles del pedido -->
            <div id="modalDetallesPedido" class="modal" style="display: none;">
                <div class="modal-content" style="max-width: 600px;">
                    <div class="modal-header">
                        <h3>Detalles del Pedido</h3>
                        <span class="close" onclick="cerrarModalDetalles()">&times;</span>
                    </div>
                    <div class="modal-body">
                        <div class="detalle-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <h4>Información del Cliente</h4>
                                <p><strong>Nombre:</strong> <span id="detalle-cliente"></span></p>
                                <p><strong>Teléfono:</strong> <span id="detalle-telefono"></span></p>
                                <p><strong>Dirección:</strong> <span id="detalle-direccion"></span></p>
                                <p><strong>Barrio:</strong> <span id="detalle-barrio"></span></p>
                            </div>
                            <div>
                                <h4>Información del Pedido</h4>
                                <p><strong>Domiciliario:</strong> <span id="detalle-domiciliario"></span></p>
                                <p><strong>Zona:</strong> <span id="detalle-zona"></span></p>
                                <p><strong>Estado:</strong> <span id="detalle-estado"></span></p>
                                <p><strong>Fecha:</strong> <span id="detalle-fecha"></span></p>
                            </div>
                        </div>
                        <div class="detalle-grid" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-top: 15px;">
                            <div>
                                <h4>Tiempos</h4>
                                <p><strong>Hora Salida:</strong> <span id="detalle-salida"></span></p>
                                <p><strong>Hora Llegada:</strong> <span id="detalle-llegada"></span></p>
                                <p><strong>Tiempo Estimado:</strong> <span id="detalle-estimado"></span></p>
                            </div>
                            <div>
                                <h4>Paquetes</h4>
                                <p><strong>Cantidad:</strong> <span id="detalle-paquetes"></span></p>
                                <p><strong>Total:</strong> <span id="detalle-total"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
            function verDetallesPedido(pedido) {
                document.getElementById('detalle-cliente').textContent = pedido.cliente_nombre || 'N/A';
                document.getElementById('detalle-telefono').textContent = pedido.cliente_telefono || 'N/A';
                document.getElementById('detalle-direccion').textContent = pedido.cliente_direccion || 'N/A';
                document.getElementById('detalle-barrio').textContent = pedido.barrio || 'N/A';
                document.getElementById('detalle-domiciliario').textContent = pedido.domiciliario_nombre || 'No asignado';
                document.getElementById('detalle-zona').textContent = pedido.zona_nombre || 'N/A';
                document.getElementById('detalle-estado').textContent = pedido.estado ? pedido.estado.charAt(0).toUpperCase() + pedido.estado.slice(1) : 'N/A';
                document.getElementById('detalle-fecha').textContent = pedido.fecha_pedido ? new Date(pedido.fecha_pedido).toLocaleDateString('es-ES') : 'N/A';
                document.getElementById('detalle-salida').textContent = pedido.hora_salida ? new Date(pedido.hora_salida).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'}) : '-';
                document.getElementById('detalle-llegada').textContent = pedido.hora_llegada ? new Date(pedido.hora_llegada).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'}) : '-';
                document.getElementById('detalle-estimado').textContent = (pedido.tiempo_estimado || '30') + ' min';
                document.getElementById('detalle-paquetes').textContent = pedido.cantidad_paquetes || '1';
                document.getElementById('detalle-total').textContent = '$' + parseFloat(pedido.total || 0).toLocaleString('es-ES', {minimumFractionDigits: 2});
                
                document.getElementById('modalDetallesPedido').style.display = 'block';
            }

            function cerrarModalDetalles() {
                document.getElementById('modalDetallesPedido').style.display = 'none';
            }

            // Cerrar modal al hacer clic fuera de él
            window.onclick = function(event) {
                const modal = document.getElementById('modalDetallesPedido');
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
            </script>
        </div>

        <script src="../componentes/dashboard.js"></script>
        <script>
        function exportarReporte(tipo) {
            // Función para exportar reportes
            console.log('Exportando reporte:', tipo);
        }
        </script>
    </div>
</body>
</html>