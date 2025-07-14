<?php
require_once '../servicios/verificar_permisos.php';
verificarAcceso('pedidos');

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Parámetros de filtrado
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : '';
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : '';
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Construir consulta con filtros
$where_conditions = [];
$params = [];

if ($estado_filtro && in_array($estado_filtro, ['entregado', 'cancelado'])) {
    $where_conditions[] = "h.estado = ?";
    $params[] = $estado_filtro;
}

if ($fecha_desde) {
    $where_conditions[] = "DATE(h.fecha_completado) >= ?";
    $params[] = $fecha_desde;
}

if ($fecha_hasta) {
    $where_conditions[] = "DATE(h.fecha_completado) <= ?";
    $params[] = $fecha_hasta;
}

if ($busqueda) {
    $where_conditions[] = "(h.cliente_nombre LIKE ? OR h.cliente_documento LIKE ? OR h.id_pedido_original = ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = is_numeric($busqueda) ? intval($busqueda) : 0;
}

$where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Consulta principal con paginación
$sql = "
    SELECT h.*, u.nombre as usuario_nombre
    FROM historico_pedidos h
    LEFT JOIN usuarios u ON h.usuario_proceso = u.id_usuario
    $where_sql
    ORDER BY h.fecha_completado DESC
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para el total de registros (para paginación)
$count_sql = "SELECT COUNT(*) FROM historico_pedidos h $where_sql";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Obtener estadísticas rápidas
$stats_entregados = $pdo->query("SELECT COUNT(*) FROM historico_pedidos WHERE estado = 'entregado'")->fetchColumn();
$stats_cancelados = $pdo->query("SELECT COUNT(*) FROM historico_pedidos WHERE estado = 'cancelado'")->fetchColumn();
$stats_ingresos = $pdo->query("SELECT SUM(total) FROM historico_pedidos WHERE estado = 'entregado'")->fetchColumn() ?? 0;
$stats_hoy = $pdo->query("SELECT COUNT(*) FROM historico_pedidos WHERE DATE(fecha_completado) = CURDATE()")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Histórico de Pedidos</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="stylesheet" href="../componentes/pedidos.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .historico-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; margin-bottom: 5px; }
        .stat-label { color: #666; font-size: 0.9em; }
        .entregados { color: #28a745; }
        .cancelados { color: #dc3545; }
        .ingresos { color: #007bff; }
        .hoy { color: #ffc107; }
        .filtros-container { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .filtros-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end; }
        .filtro-grupo label { display: block; margin-bottom: 5px; font-weight: 500; }
        .filtro-grupo input, .filtro-grupo select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn-filtrar { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn-limpiar { background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-left: 10px; }
        .tabla-historico { background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .tabla-historico table { width: 100%; border-collapse: collapse; }
        .tabla-historico th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: 500; border-bottom: 1px solid #dee2e6; }
        .tabla-historico td { padding: 12px; border-bottom: 1px solid #eee; }
        .estado-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 500; }
        .estado-entregado { background: #d4edda; color: #155724; }
        .estado-cancelado { background: #f8d7da; color: #721c24; }
        .paginacion { margin-top: 20px; text-align: center; }
        .paginacion a, .paginacion span { display: inline-block; padding: 8px 12px; margin: 0 4px; text-decoration: none; border: 1px solid #ddd; border-radius: 4px; }
        .paginacion .current { background: #007bff; color: white; border-color: #007bff; }
        .sin-resultados { text-align: center; padding: 40px; color: #666; }
        .detalle-btn { background: #17a2b8; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.8em; }
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
            <a href="pedidos.php" class="menu-item"><i class="fas fa-shopping-bag"></i><span class="menu-text">Pedidos Activos</span></a>
            <a href="historico_pedidos.php" class="menu-item active"><i class="fas fa-history"></i><span class="menu-text">Histórico</span></a>
            <a href="clientes.php" class="menu-item"><i class="fas fa-users"></i><span class="menu-text">Clientes</span></a>
            <?php if (tienePermiso('domiciliarios')): ?>
            <a href="domiciliarios.php" class="menu-item"><i class="fas fa-motorcycle"></i><span class="menu-text">Domiciliarios</span></a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
            <a href="zonas.php" class="menu-item"><i class="fas fa-map-marked-alt"></i><span class="menu-text">Zonas de Entrega</span></a>
            <?php endif; ?>
            <a href="reportes.php" class="menu-item"><i class="fas fa-chart-bar"></i><span class="menu-text">Reportes</span></a>
            <?php if (esAdmin()): ?>
            <a href="tabla_usuarios.php" class="menu-item"><i class="fas fa-users-cog"></i><span class="menu-text">Gestionar Usuarios</span></a>
            <?php endif; ?>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2><i class="fas fa-history"></i> Histórico de Pedidos</h2>
                <span style="color: #666; font-size: 0.9em;">Pedidos entregados y cancelados</span>
            </div>
            <div class="action-buttons">
                <a href="pedidos.php" class="btn-login">
                    <i class="fas fa-arrow-left"></i> Volver a Pedidos Activos
                </a>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="historico-stats">
            <div class="stat-card">
                <div class="stat-number entregados"><?php echo number_format($stats_entregados); ?></div>
                <div class="stat-label">Pedidos Entregados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number cancelados"><?php echo number_format($stats_cancelados); ?></div>
                <div class="stat-label">Pedidos Cancelados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number ingresos">$<?php echo number_format($stats_ingresos, 0); ?></div>
                <div class="stat-label">Ingresos Totales</div>
            </div>
            <div class="stat-card">
                <div class="stat-number hoy"><?php echo number_format($stats_hoy); ?></div>
                <div class="stat-label">Completados Hoy</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros-container">
            <form method="GET" action="">
                <div class="filtros-grid">
                    <div class="filtro-grupo">
                        <label for="estado">Estado:</label>
                        <select id="estado" name="estado">
                            <option value="">Todos</option>
                            <option value="entregado" <?php echo $estado_filtro === 'entregado' ? 'selected' : ''; ?>>Entregados</option>
                            <option value="cancelado" <?php echo $estado_filtro === 'cancelado' ? 'selected' : ''; ?>>Cancelados</option>
                        </select>
                    </div>
                    <div class="filtro-grupo">
                        <label for="fecha_desde">Desde:</label>
                        <input type="date" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($fecha_desde); ?>">
                    </div>
                    <div class="filtro-grupo">
                        <label for="fecha_hasta">Hasta:</label>
                        <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($fecha_hasta); ?>">
                    </div>
                    <div class="filtro-grupo">
                        <label for="busqueda">Buscar:</label>
                        <input type="text" id="busqueda" name="busqueda" placeholder="Cliente, documento, ID..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    <div class="filtro-grupo">
                        <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="historico_pedidos.php" class="btn-limpiar"><i class="fas fa-times"></i> Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de histórico -->
        <div class="tabla-historico">
            <?php if (empty($historico)): ?>
                <div class="sin-resultados">
                    <i class="fas fa-search" style="font-size: 3em; color: #ddd; margin-bottom: 15px;"></i>
                    <h3>No se encontraron registros</h3>
                    <p>No hay pedidos en el histórico que coincidan con los filtros aplicados.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Original</th>
                            <th>Cliente</th>
                            <th>Documento</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Domiciliario</th>
                            <th>Fecha Pedido</th>
                            <th>Fecha Completado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historico as $item): ?>
                            <tr>
                                <td><strong>#<?php echo $item['id_pedido_original']; ?></strong></td>
                                <td><?php echo htmlspecialchars($item['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($item['cliente_documento']); ?></td>
                                <td>
                                    <span class="estado-badge estado-<?php echo $item['estado']; ?>">
                                        <?php echo ucfirst($item['estado']); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($item['total'], 0); ?></td>
                                <td><?php echo htmlspecialchars($item['domiciliario_nombre'] ?: 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['fecha_pedido'])); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['fecha_completado'])); ?></td>
                                <td>
                                    <button class="detalle-btn" onclick="verDetalle(<?php echo $item['id_historico']; ?>)">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Paginación -->
        <?php if ($total_pages > 1): ?>
            <div class="paginacion">
                <?php
                $query_params = $_GET;
                
                // Página anterior
                if ($page > 1):
                    $query_params['page'] = $page - 1;
                    $prev_url = '?' . http_build_query($query_params);
                ?>
                    <a href="<?php echo $prev_url; ?>"><i class="fas fa-chevron-left"></i> Anterior</a>
                <?php endif; ?>

                <?php
                // Páginas numeradas
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++):
                    $query_params['page'] = $i;
                    $page_url = '?' . http_build_query($query_params);
                    
                    if ($i == $page):
                ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="<?php echo $page_url; ?>"><?php echo $i; ?></a>
                <?php endif; endfor; ?>

                <?php
                // Página siguiente
                if ($page < $total_pages):
                    $query_params['page'] = $page + 1;
                    $next_url = '?' . http_build_query($query_params);
                ?>
                    <a href="<?php echo $next_url; ?>">Siguiente <i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Información de paginación -->
        <div style="text-align: center; margin-top: 10px; color: #666; font-size: 0.9em;">
            Mostrando <?php echo (($page - 1) * $per_page) + 1; ?> - <?php echo min($page * $per_page, $total_records); ?> 
            de <?php echo number_format($total_records); ?> registros
        </div>
    </div>

    <!-- Modal para detalles -->
    <div id="modalDetalle" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="detalleContent">
                <div class="loading" style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2em;"></i>
                    <p>Cargando detalles...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function verDetalle(id) {
            const modal = document.getElementById('modalDetalle');
            const content = document.getElementById('detalleContent');
            
            modal.style.display = 'block';
            
            fetch(`../servicios/obtener_detalle_historico.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        content.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
                    } else {
                        content.innerHTML = formatearDetalle(data);
                    }
                })
                .catch(error => {
                    content.innerHTML = `<p style="color: red;">Error al cargar detalles: ${error.message}</p>`;
                });
        }

        function formatearDetalle(data) {
            return `
                <h3>Detalle del Pedido #${data.id_pedido_original}</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h4>Información del Cliente</h4>
                        <p><strong>Nombre:</strong> ${data.cliente_nombre}</p>
                        <p><strong>Documento:</strong> ${data.cliente_documento}</p>
                        <p><strong>Teléfono:</strong> ${data.cliente_telefono || 'N/A'}</p>
                        <p><strong>Dirección:</strong> ${data.cliente_direccion || 'N/A'}</p>
                    </div>
                    <div>
                        <h4>Información del Pedido</h4>
                        <p><strong>Estado:</strong> <span class="estado-badge estado-${data.estado}">${data.estado}</span></p>
                        <p><strong>Total:</strong> $${new Intl.NumberFormat().format(data.total)}</p>
                        <p><strong>Cantidad:</strong> ${data.cantidad_paquetes} paquetes</p>
                        <p><strong>Tiempo estimado:</strong> ${data.tiempo_estimado} min</p>
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <h4>Detalles de Entrega</h4>
                    <p><strong>Zona:</strong> ${data.zona_nombre} ($${new Intl.NumberFormat().format(data.zona_tarifa)})</p>
                    <p><strong>Domiciliario:</strong> ${data.domiciliario_nombre || 'N/A'}</p>
                    <p><strong>Fecha pedido:</strong> ${new Date(data.fecha_pedido).toLocaleString()}</p>
                    <p><strong>Fecha completado:</strong> ${new Date(data.fecha_completado).toLocaleString()}</p>
                    ${data.motivo_cancelacion ? `<p><strong>Motivo cancelación:</strong> ${data.motivo_cancelacion}</p>` : ''}
                    ${data.observaciones ? `<p><strong>Observaciones:</strong> ${data.observaciones}</p>` : ''}
                </div>
            `;
        }

        // Cerrar modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('modalDetalle').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modalDetalle');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>