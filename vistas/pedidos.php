<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit;
}

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios", "root", "root"); // Cambia "tu_contraseña"
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
    SELECT p.id_pedido, c.nombre AS cliente, c.documento, d.nombre AS domiciliario, p.estado, p.fecha_pedido, p.id_cliente, p.id_domiciliario, p.id_zona, p.cantidad_paquetes, p.total
    FROM pedidos p
    LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
    LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
    ORDER BY p.fecha_pedido DESC
    LIMIT 5
");
$recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener clientes, zonas y domiciliarios para el formulario
$clients = $pdo->query("SELECT id_cliente, nombre, documento FROM clientes WHERE estado = 'activo'")->fetchAll(PDO::FETCH_ASSOC);
$zones = $pdo->query("SELECT id_zona, nombre, tarifa_base FROM zonas WHERE estado = 'activo'")->fetchAll(PDO::FETCH_ASSOC);
$domiciliarios = $pdo->query("SELECT id_domiciliario, nombre FROM domiciliarios WHERE estado = 'disponible'")->fetchAll(PDO::FETCH_ASSOC);
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
            <div class="orders-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Domiciliario</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $pedido): ?>
                            <tr>
                                <td>#<?php echo $pedido['id_pedido']; ?></td>
                                <td><?php echo htmlspecialchars($pedido['cliente']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['domiciliario'] ?? 'No asignado'); ?></td>
                                <td><span class="estado-<?php echo strtolower($pedido['estado']); ?> estado"><?php echo ucfirst($pedido['estado']); ?></span></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                                <td>
                                    <button class="btn btn-editar" onclick="editarPedido(<?php echo $pedido['id_pedido']; ?>)"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Nuevo/Editar Pedido -->
    <div id="modalNuevoPedido" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2 id="modalTitle">Nuevo Pedido</h2>
            <form id="formNuevoPedido">
                <input type="hidden" id="id_pedido" name="id_pedido">
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
                    <label for="id_domiciliario">Repartidor:</label>
                    <select id="id_domiciliario" name="id_domiciliario" class="form-control" required>
                        <option value="">Seleccione un repartidor</option>
                        <?php foreach ($domiciliarios as $domiciliario): ?>
                            <option value="<?php echo $domiciliario['id_domiciliario']; ?>"><?php echo htmlspecialchars($domiciliario['nombre']); ?></option>
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
                <button type="submit" class="btn-login">Guardar</button>
                <button type="button" class="btn-login" onclick="cerrarModal('modalNuevoPedido')">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModalNuevoPedido() {
            const modal = document.getElementById('modalNuevoPedido');
            const modalTitle = document.getElementById('modalTitle');
            const form = document.getElementById('formNuevoPedido');
            modalTitle.textContent = 'Nuevo Pedido';
            form.reset();
            document.getElementById('id_pedido').value = '';
            document.getElementById('total').value = '';
            modal.classList.add('active');
            setupFormListeners();
        }

        function editarPedido(id) {
            fetch(`../servicios/obtener_pedido.php?id=${id}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error en la solicitud: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    const modal = document.getElementById('modalNuevoPedido');
                    const modalTitle = document.getElementById('modalTitle');
                    modalTitle.textContent = 'Editar Pedido';
                    document.getElementById('id_pedido').value = data.id_pedido || '';
                    document.getElementById('numeroDocumento').value = data.documento || '';
                    document.getElementById('id_cliente').value = data.id_cliente || '';
                    document.getElementById('id_zona').value = data.id_zona || '';
                    document.getElementById('id_domiciliario').value = data.id_domiciliario || '';
                    document.getElementById('estado').value = data.estado || 'pendiente';
                    document.getElementById('bolsas').value = data.cantidad_paquetes || 1;
                    document.getElementById('total').value = parseFloat(data.total || 0).toFixed(2);
                    modal.classList.add('active');
                    setupFormListeners();
                })
                .catch(error => {
                    alert('Error al cargar el pedido: ' + error.message);
                });
        }

        function setupFormListeners() {
            const numeroDocumento = document.getElementById('numeroDocumento');
            const idCliente = document.getElementById('id_cliente');
            const idZona = document.getElementById('id_zona');
            numeroDocumento.addEventListener('input', () => {
                const selectedOption = Array.from(idCliente.options).find(opt => opt.dataset.documento === numeroDocumento.value);
                idCliente.value = selectedOption ? selectedOption.value : '';
            });
            idCliente.addEventListener('change', () => {
                const selectedOption = idCliente.options[idCliente.selectedIndex];
                numeroDocumento.value = selectedOption.dataset.documento || '';
            });
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
            fetch('../buscar_pedido.php', {
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
                            <td>#${order.id_pedido}</td>
                            <td>${order.cliente}</td>
                            <td>${order.domiciliario || 'No asignado'}</td>
                            <td><span class="estado-${order.estado.toLowerCase()} estado">${order.estado.charAt(0).toUpperCase() + order.estado.slice(1)}</span></td>
                            <td>${new Date(order.fecha_pedido).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' })}</td>
                            <td><button class="btn btn-editar" onclick="editarPedido(${order.id_pedido})"><i class="fas fa-edit"></i></button></td>
                        `;
                        tbody.appendChild(tr);
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
            const url = document.getElementById('id_pedido').value ? '../servicios/actualizar_pedido.php' : '../procesar_pedido.php';
            fetch(url, {
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
                        alert(document.getElementById('id_pedido').value ? 'Pedido actualizado exitosamente' : 'Pedido creado exitosamente');
                        cerrarModal('modalNuevoPedido');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
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