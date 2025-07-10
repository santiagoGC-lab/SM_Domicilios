<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit;
}

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener datos para las tarjetas del tablero
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'pendiente'")->fetchColumn();
$completedToday = $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()")->fetchColumn();
$revenueToday = $pdo->query("SELECT SUM(total) FROM pedidos WHERE estado = 'entregado' AND DATE(fecha_pedido) = CURDATE()")->fetchColumn() ?? 0.00;

// Obtener pedidos recientes
$stmt = $pdo->query("
    SELECT p.id_pedido, c.nombre AS cliente, d.nombre AS domiciliario, p.estado, p.fecha_pedido
    FROM pedidos p
    LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
    LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
    ORDER BY p.fecha_pedido DESC
    LIMIT 5
");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener clientes y zonas para el formulario
$clients = $pdo->query("SELECT id_cliente, nombre, documento FROM clientes WHERE estado = 'activo'")->fetchAll(PDO::FETCH_ASSOC);
$zones = $pdo->query("SELECT id_zona, nombre, tarifa_base FROM zonas WHERE estado = 'activo'")->fetchAll(PDO::FETCH_ASSOC);
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
            <a href="dashboard.php" class="menu-item"><i class="fas fa-tachometer-alt"></i><span class="menu-text">Inicio</span></a>
            <a href="pedidos.php" class="menu-item active"><i class="fas fa-shopping-bag"></i><span class="menu-text">Pedidos</span></a>
            <a href="clientes.php" class="menu-item"><i class="fas fa-users"></i><span class="menu-text">Clientes</span></a>
            <a href="domiciliarios.php" class="menu-item"><i class="fas fa-motorcycle"></i><span class="menu-text">Domiciliarios</span></a>
            <a href="zonas.php" class="menu-item"><i class="fas fa-map-marked-alt"></i><span class="menu-text">Zonas de Entrega</span></a>
            <a href="reportes.php" class="menu-item"><i class="fas fa-chart-bar"></i><span class="menu-text">Reportes</span></a>
            <a href="configuracion.php" class="menu-item"><i class="fas fa-cog"></i><span class="menu-text">Configuración</span></a>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Gestión de Pedidos</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar pedidos..." id="searchInput">
                <button class="btn-search" onclick="buscarPedido()">Buscar</button>
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
                <div class="card-footer">Actualizado hace 5 minutos</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-check-circle"></i></div>
                    <h3 class="card-title">Completados Hoy</h3>
                </div>
                <div class="card-value"><?php echo $completedToday; ?></div>
                <div class="card-footer">Actualizado hace 5 minutos</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-dollar-sign"></i></div>
                    <h3 class="card-title">Ingresos del Día</h3>
                </div>
                <div class="card-value">$<?php echo number_format($revenueToday, 2); ?></div>
                <div class="card-footer">Actualizado hace 5 minutos</div>
            </div>
        </div>

        <div class="recent-activity">
            <div class="activity-header">
                <h3>Pedidos Recientes</h3>
            </div>
            <div class="activity-list">
                <?php foreach ($recentOrders as $order): ?>
                    <div class="activity-item">
                        <span>Pedido #<?php echo $order['id_pedido']; ?> - Cliente: <?php echo htmlspecialchars($order['cliente']); ?> - Estado: <?php echo $order['estado']; ?> - Repartidor: <?php echo htmlspecialchars($order['domiciliario'] ?? 'No asignado'); ?></span>
                        <span><?php echo date('d/m/Y H:i', strtotime($order['fecha_pedido'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal de Nuevo Pedido -->
    <div id="modalNuevoPedido" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2>Nuevo Pedido</h2>
            <form id="formNuevoPedido">
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
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="pendiente">Pendiente</option>
                        <option value="en_camino">En Camino</option>
                        <option value="entregado">Entregado</option>
                        <option value="cancelado">Cancelado</option>
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
                <button type="submit" class="btn-login">Crear Pedido</button>
                <button type="button" class="btn-login" onclick="cerrarModal('modalNuevoPedido')">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModalNuevoPedido() {
            const modal = document.getElementById('modalNuevoPedido');
            modal.classList.add('active');
            document.getElementById('formNuevoPedido').reset();
            document.getElementById('total').value = ''; // Limpiar el campo total
            // Sincronizar cédula y cliente
            const numeroDocumento = document.getElementById('numeroDocumento');
            const idCliente = document.getElementById('id_cliente');
            numeroDocumento.addEventListener('input', () => {
                const selectedOption = Array.from(idCliente.options).find(opt => opt.dataset.documento === numeroDocumento.value);
                idCliente.value = selectedOption ? selectedOption.value : '';
            });
            idCliente.addEventListener('change', () => {
                const selectedOption = idCliente.options[idCliente.selectedIndex];
                numeroDocumento.value = selectedOption.dataset.documento || '';
            });
            // Actualizar total según la zona seleccionada
            const idZona = document.getElementById('id_zona');
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
            fetch('../servicios/buscar_pedido.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
                const activityList = document.querySelector('.activity-list');
                activityList.innerHTML = '';
                data.forEach(order => {
                    const item = document.createElement('div');
                    item.className = 'activity-item';
                    item.innerHTML = `
                        <span>Pedido #${order.id_pedido} - Cliente: ${order.cliente} - Estado: ${order.estado} - Repartidor: ${order.domiciliario || 'No asignado'}</span>
                        <span>${new Date(order.fecha_pedido).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })}</span>`;
                    activityList.appendChild(item);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al buscar pedidos: ' + error.message);
            });
        }

        document.getElementById('formNuevoPedido').onsubmit = function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('../servicios/procesar_pedido.php', {
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
                    alert('Pedido creado exitosamente');
                    cerrarModal('modalNuevoPedido');
                    location.reload();
                } else {
                    alert('Error al crear el pedido: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar el pedido: ' + error.message);
            });
        };
    </script>
</body>
</html>