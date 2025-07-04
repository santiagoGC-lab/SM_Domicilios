<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Gestión de Pedidos</title>
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
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <a href="pedidos.php" class="menu-item active">
                <i class="fas fa-shopping-bag"></i>
                <span class="menu-text">Pedidos</span>
            </a>
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Clientes</span>
            </a>
            <a href="domiciliarios.php" class="menu-item">
                <i class="fas fa-motorcycle"></i>
                <span class="menu-text">Domiciliarios</span>
            </a>
            <a href="zonas.php" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                <span class="menu-text">Zonas de Entrega</span>
            </a>
            <a href="reportes.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <a href="configuracion.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Configuración</span>
            </a>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
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
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="card-title">Pedidos Pendientes</h3>
                </div>
                <div class="card-value">3</div>
                <div class="card-footer">Actualizado hace 5 minutos</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="card-title">Completados Hoy</h3>
                </div>
                <div class="card-value">2</div>
                <div class="card-footer">Actualizado hace 5 minutos</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="card-title">Ingresos del Día</h3>
                </div>
                <div class="card-value">$150.00</div>
                <div class="card-footer">Actualizado hace 5 minutos</div>
            </div>
        </div>

        <div class="recent-activity">
            <div class="activity-header">
                <h3>Pedidos Recientes</h3>
            </div>
            <div class="activity-list">
                <div class="activity-item">
                    <span>Pedido #001 - Cliente: Juan Pérez - Producto: Pizza (2) - Estado: Pendiente</span>
                    <span>02/07/2025 14:30</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Nuevo Pedido -->
    <div id="modalNuevoPedido" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Nuevo Pedido</h2>
            <form id="formNuevoPedido">
                <div class="form-group">
                    <label for="cliente">Cliente:</label>
                    <input type="text" id="cliente" name="cliente" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="zona">Zona:</label>
                    <select id="zona" name="zona" class="form-control" required>
                        <option value="">Seleccione una zona</option>
                        <option value="norte">Norte</option>
                        <option value="sur">Sur</option>
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
                    <label for="productos">Productos:</label>
                    <div id="productosContainer" class="producto-item">
                        <select class="form-control producto-select" required>
                            <option value="">Seleccione un producto</option>
                            <option value="pizza">Pizza</option>
                            <option value="hamburguesa">Hamburguesa</option>
                        </select>
                        <input type="number" class="form-control cantidad" placeholder="Cantidad" min="1" required>
                        <button type="button" class="btn-remove-producto btn" onclick="this.parentElement.remove()">-</button>
                    </div>
                    <button type="button" class="btn-login" onclick="agregarProducto()">+</button>
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
            document.getElementById('productosContainer').innerHTML = `
                <div class="producto-item">
                    <select class="form-control producto-select" required>
                        <option value="">Seleccione un producto</option>
                        <option value="pizza">Pizza</option>
                        <option value="hamburguesa">Hamburguesa</option>
                    </select>
                    <input type="number" class="form-control cantidad" placeholder="Cantidad" min="1" required>
                    <button type="button" class="btn-remove-producto btn" onclick="this.parentElement.remove()">-</button>
                </div>`;
        }

        function agregarProducto() {
            const container = document.getElementById('productosContainer');
            const newProducto = document.createElement('div');
            newProducto.className = 'producto-item';
            newProducto.innerHTML = `
                <select class="form-control producto-select" required>
                    <option value="">Seleccione un producto</option>
                    <option value="pizza">Pizza</option>
                    <option value="hamburguesa">Hamburguesa</option>
                </select>
                <input type="number" class="form-control cantidad" placeholder="Cantidad" min="1" required>
                <button type="button" class="btn-remove-producto btn" onclick="this.parentElement.remove()">-</button>`;
            container.appendChild(newProducto);
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
        }

        document.getElementById('formNuevoPedido').onsubmit = function(e) {
            e.preventDefault();
            const pedido = {
                cliente: document.getElementById('cliente').value,
                zona: document.getElementById('zona').value,
                estado: document.getElementById('estado').value,
                productos: Array.from(document.querySelectorAll('.producto-item')).map(item => ({
                    producto: item.querySelector('.producto-select').value,
                    cantidad: item.querySelector('.cantidad').value
                }))
            };
            alert('Nuevo pedido creado: ' + JSON.stringify(pedido));
            cerrarModal('modalNuevoPedido');
        }
    </script>
</body>

</html>