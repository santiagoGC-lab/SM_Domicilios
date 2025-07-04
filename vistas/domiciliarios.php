<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SM - Domiciliarios</title>
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/domiciliarios.css" />
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
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Clientes</span>
            </a>
            <a href="domiciliarios.php" class="menu-item active">
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
            <h2>Gestión de Domiciliarios</h2>
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName">Usuario</strong></span>
            </div>
        </div>

        <div class="users-section">
            <div class="users-actions">
                <div class="search-and-filter">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar domiciliario..." oninput="filterDomiciliarios()">
                        <button class="btn-search" onclick="filterDomiciliarios()">Buscar</button>
                    </div>
                    <select id="filterStatus" class="filter-select" onchange="filterDomiciliarios()">
                        <option value="">Todos los estados</option>
                        <option value="disponible">Disponible</option>
                        <option value="ocupado">En Servicio</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <button class="btn-login" onclick="openNewDomiciliarioModal()">
                    <i class="fas fa-user-plus"></i> Nuevo Domiciliario
                </button>
            </div>

            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Vehículo</th>
                            <th>Zona Asignada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="domiciliariosTableBody">
                        <tr>
                            <td>1</td>
                            <td>Juan Pérez</td>
                            <td>3001234567</td>
                            <td>Moto - ABC123</td>
                            <td>Norte</td>
                            <td>
                                <span class="estado-disponible">Disponible</span>
                            </td>
                            <td>
                                <button class="btn btn-editar" onclick="editarDomiciliario(1)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-eliminar" onclick="eliminarDomiciliario(1)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <!-- Más filas se pueden agregar dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Edición/Nuevo Domiciliario -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Editar Domiciliario</h2>
                <span class="close" onclick="closeModal('modalEditar')">×</span>
            </div>
            <form id="formEditar">
                <input type="hidden" id="domiciliarioId" name="id">
                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="vehiculo">Vehículo:</label>
                    <input type="text" id="vehiculo" name="vehiculo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="placa">Placa:</label>
                    <input type="text" id="placa" name="placa" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="zona">Zona Asignada:</label>
                    <select id="zona" name="zona" class="form-control" required>
                        <option value="norte">Norte</option>
                        <option value="sur">Sur</option>
                        <option value="este">Este</option>
                        <option value="oeste">Oeste</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="disponible">Disponible</option>
                        <option value="ocupado">En Servicio</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn-login">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Variables globales
        let currentDomiciliarioId = null;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            // Aquí podrías cargar los domiciliarios desde una base de datos
            // loadDomiciliarios();
        });

        function setupEventListeners() {
            // Sidebar responsive
            document.getElementById('sidebarToggle').addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('collapsed');
            });

            document.addEventListener('click', function(e) {
                const sidebar = document.getElementById('sidebar');
                const toggle = document.getElementById('sidebarToggle');
                if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                    sidebar.classList.remove('collapsed');
                }
            });

            // Búsqueda y filtro en tiempo real
            document.getElementById('searchInput').addEventListener('input', filterDomiciliarios);
            document.getElementById('filterStatus').addEventListener('change', filterDomiciliarios);

            // Formulario de edición
            document.getElementById('formEditar').addEventListener('submit', handleDomiciliarioSubmit);
        }

        function showUserMenu() {
            window.location.href = 'menuUsu.html';
        }

        // Funciones para el modal
        function openNewDomiciliarioModal() {
            document.getElementById('modalTitle').textContent = 'Nuevo Domiciliario';
            document.getElementById('formEditar').reset();
            document.getElementById('domiciliarioId').value = '';
            currentDomiciliarioId = null;
            document.getElementById('modalEditar').style.display = 'block';
        }

        function editarDomiciliario(id) {
            currentDomiciliarioId = id;
            document.getElementById('modalTitle').textContent = 'Editar Domiciliario';
            document.getElementById('domiciliarioId').value = id;

            // Simular carga de datos (reemplazar con datos reales de la base de datos)
            if (id === 1) {
                document.getElementById('nombre').value = 'Juan Pérez';
                document.getElementById('telefono').value = '3001234567';
                document.getElementById('vehiculo').value = 'Moto';
                document.getElementById('placa').value = 'ABC123';
                document.getElementById('zona').value = 'norte';
                document.getElementById('estado').value = 'disponible';
            }

            document.getElementById('modalEditar').style.display = 'block';
        }

        function eliminarDomiciliario(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este domiciliario?')) {
                // Lógica para eliminar el domiciliario (puedes usar AJAX o recargar la página)
                alert(`Domiciliario ${id} eliminado`);
                document.getElementById('domiciliariosTableBody').innerHTML = ''; // Simulación
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Filtrado de domiciliarios
        function filterDomiciliarios() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
            const rows = document.querySelectorAll('#domiciliariosTableBody tr');

            rows.forEach(row => {
                const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const status = row.querySelector('.estado-disponible, .estado-ocupado, .estado-inactivo').textContent.toLowerCase();

                const matchesSearch = name.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Manejar el envío del formulario
        function handleDomiciliarioSubmit(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const domiciliarioData = Object.fromEntries(formData.entries());

            // Simulación de guardado (reemplazar con lógica de backend)
            console.log('Datos del domiciliario:', domiciliarioData);
            alert(currentDomiciliarioId ? 'Domiciliario actualizado' : 'Domiciliario creado');
            closeModal('modalEditar');
            // Aquí podrías recargar la tabla o actualizarla dinámicamente
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