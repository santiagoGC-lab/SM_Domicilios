<?php
require_once '../servicios/verificar_sesion.php';
verificarSesion();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SM - Gestión de Clientes</title>
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/cliente.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <a href="pedidos.php" class="menu-item">
                <i class="fas fa-shopping-bag"></i>
                <span class="menu-text">Pedidos</span>
            </a>
            <a href="clientes.php" class="menu-item active">
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

    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Gestión de Clientes</h2>
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName">Usuario</strong></span>
            </div>
        </div>

        <!-- Estadísticas de clientes -->
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="card-title">Total Clientes</h3>
                </div>
                <div class="card-value" id="totalClientes">247</div>
                <div class="card-footer">15 nuevos este mes</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h3 class="card-title">Clientes Activos</h3>
                </div>
                <div class="card-value" id="clientesActivos">189</div>
                <div class="card-footer">Pedidos en los últimos 30 días</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3 class="card-title">Clientes VIP</h3>
                </div>
                <div class="card-value" id="clientesVip">32</div>
                <div class="card-footer">Más de 10 pedidos</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="card-title">Promedio Pedidos</h3>
                </div>
                <div class="card-value" id="promedioPedidos">4.2</div>
                <div class="card-footer">Por cliente/mes</div>
            </div>
        </div>

        <div class="clients-section">
            <div class="clients-actions">
                <div class="search-filters">
                    <input type="text" id="searchClient" class="form-control" placeholder="Buscar por nombre, teléfono o email..." />
                    <select id="filterStatus" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                        <option value="vip">VIP</option>
                    </select>
                </div>
                <button class="btn-login" onclick="openNewClientModal()">
                    <i class="fas fa-user-plus"></i> Nuevo Cliente
                </button>
            </div>

            <div class="clients-table-container">
                <table class="clients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Contacto</th>
                            <th>Direcciones</th>
                            <th>Pedidos</th>
                            <th>Estado</th>
                            <th>Último Pedido</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="clientsTableBody">
                        <tr>
                            <td>001</td>
                            <td>
                                <div class="client-info">
                                    <strong>María González</strong>
                                    <small>Cliente desde: 15/03/2024</small>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <div><i class="fas fa-phone"></i> 3001234567</div>
                                    <div><i class="fas fa-envelope"></i> maria@email.com</div>
                                </div>
                            </td>
                            <td>
                                <span class="address-count">3 direcciones</span>
                            </td>
                            <td>
                                <div class="orders-info">
                                    <strong>24 pedidos</strong>
                                    <small>$560,000 total</small>
                                </div>
                            </td>
                            <td>
                                <span class="estado-vip">VIP</span>
                            </td>
                            <td>
                                <small>Hace 2 días</small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-ver" onclick="viewClient(1)" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-editar" onclick="editClient(1)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-historial" onclick="viewHistory(1)" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-eliminar" onclick="deleteClient(1)" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>002</td>
                            <td>
                                <div class="client-info">
                                    <strong>Carlos Ramírez</strong>
                                    <small>Cliente desde: 28/02/2024</small>
                                </div>
                            </td>
                            <td>
                                <div class="contact-info">
                                    <div><i class="fas fa-phone"></i> 3009876543</div>
                                    <div><i class="fas fa-envelope"></i> carlos@email.com</div>
                                </div>
                            </td>
                            <td>
                                <span class="address-count">1 dirección</span>
                            </td>
                            <td>
                                <div class="orders-info">
                                    <strong>8 pedidos</strong>
                                    <small>$180,000 total</small>
                                </div>
                            </td>
                            <td>
                                <span class="estado-activo">Activo</span>
                            </td>
                            <td>
                                <small>Hace 1 semana</small>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-ver" onclick="viewClient(2)" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-editar" onclick="editClient(2)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-historial" onclick="viewHistory(2)" title="Historial">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-eliminar" onclick="deleteClient(2)" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Cliente -->
    <div id="modalClient" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Cliente</h2>
                <span class="close" onclick="closeModal('modalClient')">&times;</span>
            </div>
            <form id="formClient">
                <input type="hidden" id="clientId" name="id">

                <div class="form-tabs">
                    <button type="button" class="tab-button active" onclick="showTab('info-personal')">
                        <i class="fas fa-user"></i> Información Personal
                    </button>
                    <button type="button" class="tab-button" onclick="showTab('direcciones')">
                        <i class="fas fa-map-marker-alt"></i> Direcciones
                    </button>
                    <button type="button" class="tab-button" onclick="showTab('preferencias')">
                        <i class="fas fa-cog"></i> Preferencias
                    </button>
                </div>

                <!-- Tab Información Personal -->
                <div id="info-personal" class="tab-content active">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo *:</label>
                            <input type="text" id="nombre" name="nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono *:</label>
                            <input type="tel" id="telefono" name="telefono" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="documento">Documento:</label>
                            <input type="text" id="documento" name="documento" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fechaNacimiento">Fecha de Nacimiento:</label>
                            <input type="date" id="fechaNacimiento" name="fechaNacimiento" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="genero">Género:</label>
                            <select id="genero" name="genero" class="form-control">
                                <option value="">Seleccionar</option>
                                <option value="masculino">Masculino</option>
                                <option value="femenino">Femenino</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tab Direcciones -->
                <div id="direcciones" class="tab-content">
                    <div class="addresses-section">
                        <div class="addresses-header">
                            <h4>Direcciones del Cliente</h4>
                            <button type="button" class="btn-secondary" onclick="addAddress()">
                                <i class="fas fa-plus"></i> Agregar Dirección
                            </button>
                        </div>
                        <div id="addressesList" class="addresses-list">
                            <!-- Las direcciones se cargarán dinámicamente -->
                        </div>
                    </div>
                </div>

                <!-- Tab Preferencias -->
                <div id="preferencias" class="tab-content">
                    <div class="form-group">
                        <label for="tipoCliente">Tipo de Cliente:</label>
                        <select id="tipoCliente" name="tipoCliente" class="form-control">
                            <option value="regular">Regular</option>
                            <option value="vip">VIP</option>
                            <option value="corporativo">Corporativo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="horariosPreferidos">Horarios Preferidos:</label>
                        <div class="checkbox-group">
                            <label><input type="checkbox" name="horarios[]" value="mañana"> Mañana (6:00 - 12:00)</label>
                            <label><input type="checkbox" name="horarios[]" value="tarde"> Tarde (12:00 - 18:00)</label>
                            <label><input type="checkbox" name="horarios[]" value="noche"> Noche (18:00 - 22:00)</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notas">Notas y Observaciones:</label>
                        <textarea id="notas" name="notas" class="form-control" rows="4" placeholder="Instrucciones especiales, alergias, preferencias, etc."></textarea>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="notificaciones" name="notificaciones" checked>
                            Recibir notificaciones por WhatsApp
                        </label>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="promociones" name="promociones">
                            Recibir promociones y ofertas
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalClient')">Cancelar</button>
                    <button type="submit" class="btn-login">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Historial de Pedidos -->
    <div id="modalHistory" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Historial de Pedidos - <span id="clientNameHistory"></span></h2>
                <span class="close" onclick="closeModal('modalHistory')">&times;</span>
            </div>
            <div class="history-content">
                <div class="history-stats">
                    <div class="stat-item">
                        <span class="stat-value" id="totalOrders">0</span>
                        <span class="stat-label">Total Pedidos</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value" id="totalSpent">$0</span>
                        <span class="stat-label">Total Gastado</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value" id="avgOrder">$0</span>
                        <span class="stat-label">Promedio por Pedido</span>
                    </div>
                </div>
                <div class="history-table-container">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Pedido #</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Dirección</th>
                                <th>Domiciliario</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="historyTableBody">
                            <!-- Los pedidos se cargarán dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let currentClientId = null;
        let addressCounter = 0;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            loadClients();
        });

        function setupEventListeners() {
            // Sidebar responsive
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('collapsed');
            });

            // Búsqueda en tiempo real
            document.getElementById('searchClient').addEventListener('input', filterClients);
            document.getElementById('filterStatus').addEventListener('change', filterClients);

            // Formulario de cliente
            document.getElementById('formClient').addEventListener('submit', handleClientSubmit);
        }

        function showUserMenu() {
            // Implementar menú de usuario
        }

        // Funciones de pestañas
        function showTab(tabName) {
            // Ocultar todas las pestañas
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });

            // Mostrar la pestaña seleccionada
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }

        // Funciones de clientes
        function openNewClientModal() {
            document.getElementById('modalTitle').textContent = 'Nuevo Cliente';
            document.getElementById('formClient').reset();
            document.getElementById('clientId').value = '';
            currentClientId = null;
            clearAddresses();
            document.getElementById('modalClient').style.display = 'block';
        }

        function editClient(id) {
            currentClientId = id;
            document.getElementById('modalTitle').textContent = 'Editar Cliente';
            document.getElementById('clientId').value = id;

            // Aquí cargarías los datos del cliente desde la base de datos
            // Por ahora, datos de ejemplo
            loadClientData(id);

            document.getElementById('modalClient').style.display = 'block';
        }

        function loadClientData(id) {
            // Simular carga de datos del cliente
            if (id === 1) {
                document.getElementById('nombre').value = 'María González';
                document.getElementById('telefono').value = '3001234567';
                document.getElementById('email').value = 'maria@email.com';
                document.getElementById('documento').value = '12345678';
                document.getElementById('tipoCliente').value = 'vip';
                document.getElementById('notas').value = 'Cliente preferencial, le gusta recibir los pedidos en el portón principal.';

                // Cargar direcciones
                loadClientAddresses(id);
            }
        }

        function loadClientAddresses(clientId) {
            clearAddresses();
            // Simular direcciones del cliente
            const addresses = [{
                    id: 1,
                    direccion: 'Calle 123 #45-67',
                    barrio: 'Centro',
                    ciudad: 'Cali',
                    telefono: '555-0123',
                    principal: true
                },
                {
                    id: 2,
                    direccion: 'Carrera 89 #12-34',
                    barrio: 'Norte',
                    ciudad: 'Cali',
                    telefono: '555-0456',
                    principal: false
                }
            ];

            addresses.forEach(address => {
                addAddressToList(address);
            });
        }

        function viewClient(id) {
            // Implementar vista detallada del cliente
            alert(`Ver detalles del cliente ${id}`);
        }

        function viewHistory(id) {
            currentClientId = id;
            document.getElementById('clientNameHistory').textContent = getClientName(id);
            loadClientHistory(id);
            document.getElementById('modalHistory').style.display = 'block';
        }

        function loadClientHistory(clientId) {
            // Simular historial de pedidos
            const historyData = {
                totalOrders: 24,
                totalSpent: 560000,
                avgOrder: 23333,
                orders: [{
                        id: '001',
                        fecha: '2024-01-15',
                        estado: 'entregado',
                        total: 25000,
                        direccion: 'Calle 123 #45-67',
                        domiciliario: 'Juan Pérez'
                    },
                    {
                        id: '002',
                        fecha: '2024-01-10',
                        estado: 'entregado',
                        total: 18000,
                        direccion: 'Carrera 89 #12-34',
                        domiciliario: 'Ana García'
                    }
                ]
            };

            document.getElementById('totalOrders').textContent = historyData.totalOrders;
            document.getElementById('totalSpent').textContent = `$${historyData.totalSpent.toLocaleString()}`;
            document.getElementById('avgOrder').textContent = `$${historyData.avgOrder.toLocaleString()}`;

            const tbody = document.getElementById('historyTableBody');
            tbody.innerHTML = '';

            historyData.orders.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>#${order.id}</td>
                    <td>${order.fecha}</td>
                    <td><span class="estado-${order.estado}">${order.estado}</span></td>
                    <td>$${order.total.toLocaleString()}</td>
                    <td>${order.direccion}</td>
                    <td>${order.domiciliario}</td>
                    <td>
                        <button class="btn btn-ver" onclick="viewOrder('${order.id}')" title="Ver pedido">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function deleteClient(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este cliente? Esta acción no se puede deshacer.')) {
                // Aquí iría la lógica para eliminar el cliente
                alert(`Cliente ${id} eliminado`);
                loadClients();
            }
        }

        // Funciones de direcciones
        function addAddress() {
            addAddressToList();
        }

        function addAddressToList(addressData = null) {
            addressCounter++;
            const addressDiv = document.createElement('div');
            addressDiv.className = 'address-item';
            addressDiv.innerHTML = `
                <div class="address-header">
                    <h5>Dirección ${addressCounter}</h5>
                    <button type="button" class="btn-remove" onclick="removeAddress(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Dirección *:</label>
                        <input type="text" name="addresses[${addressCounter}][direccion]" class="form-control" 
                               value="${addressData?.direccion || ''}" required>
                    </div>
                    <div class="form-group">
                        <label>Barrio:</label>
                        <input type="text" name="addresses[${addressCounter}][barrio]" class="form-control" 
                               value="${addressData?.barrio || ''}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Ciudad:</label>
                        <input type="text" name="addresses[${addressCounter}][ciudad]" class="form-control" 
                               value="${addressData?.ciudad || 'Cali'}">
                    </div>
                    <div class="form-group">
                        <label>Teléfono de contacto:</label>
                        <input type="tel" name="addresses[${addressCounter}][telefono]" class="form-control" 
                               value="${addressData?.telefono || ''}">
                    </div>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="addresses[${addressCounter}][principal]" 
                               ${addressData?.principal ? 'checked' : ''}>
                        Dirección principal
                    </label>
                </div>
                <div class="form-group">
                    <label>Instrucciones especiales:</label>
                    <textarea name="addresses[${addressCounter}][instrucciones]" class="form-control" rows="2"
                              placeholder="Ej: Casa de color azul, portón negro...">${addressData?.instrucciones || ''}</textarea>
                </div>
            `;
            document.getElementById('addressesList').appendChild(addressDiv);
        }

        function removeAddress(button) {
            button.closest('.address-item').remove();
        }

        function clearAddresses() {
            document.getElementById('addressesList').innerHTML = '';
            addressCounter = 0;
        }

        // Funciones de formulario
        function handleClientSubmit(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const clientData = Object.fromEntries(formData.entries());

            // Aquí enviarías los datos al servidor
            console.log('Datos del cliente:', clientData);

            alert(currentClientId ? 'Cliente actualizado exitosamente' : 'Cliente creado exitosamente');
            closeModal('modalClient');
            loadClients();
        }

        // Funciones auxiliares
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function getClientName(id) {
            const names = {
                1: 'María González',
                2: 'Carlos Ramírez'
            };
            return names[id] || 'Cliente';
        }

        function loadClients() {
            // Aquí cargarías los clientes desde la base de datos
            // Por ahora, los datos están hardcodeados en el HTML
        }

        function filterClients() {
            const searchTerm = document.getElementById('searchClient').value.toLowerCase();
            const statusFilter = document.getElementById('filterStatus').value;

            // Implementar filtrado de la tabla
            // Esta función filtraría los clientes mostrados según los criterios
        }

        function viewOrder(orderId) {
            alert(`Ver detalles del pedido ${orderId}`);
        }

        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>

</html>