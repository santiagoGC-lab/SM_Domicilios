<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SM - Zonas de Entrega</title>
    <link rel="stylesheet" href="../componentes/base.css" />
    <link rel="stylesheet" href="../componentes/zonas.css" />
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
            <a href="domiciliarios.php" class="menu-item">
                <i class="fas fa-motorcycle"></i>
                <span class="menu-text">Domiciliarios</span>
            </a>
            <a href="zonas.php" class="menu-item active">
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
            <h2>Gestión de Zonas de Entrega</h2>
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar zona..." oninput="filterZonas()">
                        <button class="btn btn-primary" onclick="filterZonas()">Buscar</button>
                    </div>
                    <select id="filterStatus" class="filter-select" onchange="filterZonas()">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="abrirModalNueva()">
                    <i class="fas fa-plus"></i> Nueva Zona
                </button>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Zona</th>
                            <th>Ciudad</th>
                            <th>Tarifa Base</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="zonasTableBody">
                        <tr>
                            <td>1</td>
                            <td>Norte</td>
                            <td>Ciudad Principal</td>
                            <td>$5.000</td>
                            <td>
                                <span class="estado-activo">Activo</span>
                            </td>
                            <td>
                                <button class="btn btn-editar" onclick="editarZona(1)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-eliminar" onclick="eliminarZona(1)">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Editar Zona</h2>
                <span class="close" onclick="closeModal('modalEditar')">×</span>
            </div>
            <form id="formEditar">
                <input type="hidden" id="zonaId" name="id">
                <div class="form-group">
                    <label for="nombre">Nombre de la Zona:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="ciudad">Ciudad:</label>
                    <input type="text" id="ciudad" name="ciudad" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="tarifa">Tarifa Base:</label>
                    <input type="number" id="tarifa" name="tarifa" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Nueva Zona -->
    <div id="modalNueva" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Nueva Zona</h2>
                <span class="close" onclick="closeModal('modalNueva')">×</span>
            </div>
            <form id="formNueva">
                <div class="form-group">
                    <label for="nuevoNombre">Nombre de la Zona:</label>
                    <input type="text" id="nuevoNombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="nuevoCiudad">Ciudad:</label>
                    <input type="text" id="nuevoCiudad" name="ciudad" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="nuevoTarifa">Tarifa Base:</label>
                    <input type="number" id="nuevoTarifa" name="tarifa" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="nuevoEstado">Estado:</label>
                    <select id="nuevoEstado" name="estado" class="form-control" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalNueva')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Zona</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Variables globales
        let currentZonaId = null;

        // Inicialización
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            // Aquí podrías cargar las zonas desde una base de datos
            // loadZonas();
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
            document.getElementById('searchInput').addEventListener('input', filterZonas);
            document.getElementById('filterStatus').addEventListener('change', filterZonas);

            // Formularios de edición y nueva zona
            document.getElementById('formEditar').addEventListener('submit', handleZonaSubmit);
            document.getElementById('formNueva').addEventListener('submit', handleNewZonaSubmit);
        }

        function showUserMenu() {
            window.location.href = 'menuUsu.html';
        }

        // Funciones para modales
        function editarZona(id) {
            currentZonaId = id;
            document.getElementById('modalTitle').textContent = 'Editar Zona';
            document.getElementById('zonaId').value = id;

            // Simular carga de datos (reemplazar con datos reales de la base de datos)
            if (id === 1) {
                document.getElementById('nombre').value = 'Norte';
                document.getElementById('ciudad').value = 'Ciudad Principal';
                document.getElementById('tarifa').value = 5000;
                document.getElementById('estado').value = 'activo';
            }

            document.getElementById('modalEditar').style.display = 'block';
        }

        function abrirModalNueva() {
            document.getElementById('modalNueva').style.display = 'block';
            document.getElementById('formNueva').reset();
        }

        function eliminarZona(id) {
            if (confirm('¿Estás seguro de que deseas eliminar esta zona?')) {
                // Lógica para eliminar la zona (puedes usar AJAX o recargar la página)
                alert(`Zona con ID ${id} eliminada`);
                document.getElementById('zonasTableBody').innerHTML = ''; // Simulación
            }
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Filtrado de zonas
        function filterZonas() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
            const rows = document.querySelectorAll('#zonasTableBody tr');

            rows.forEach(row => {
                const zonaName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const status = row.querySelector('.estado-activo, .estado-inactivo').textContent.toLowerCase();

                const matchesSearch = zonaName.includes(searchTerm);
                const matchesStatus = !statusFilter || status === statusFilter;

                if (matchesSearch && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Manejar el envío del formulario de edición
        function handleZonaSubmit(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const zonaData = Object.fromEntries(formData.entries());

            // Simulación de guardado (reemplazar con lógica de backend)
            console.log('Zona actualizada:', zonaData);
            alert('Zona actualizada exitosamente');
            closeModal('modalEditar');
            // Aquí podrías recargar la tabla o actualizarla dinámicamente
        }

        // Manejar el envío del formulario de nueva zona
        function handleNewZonaSubmit(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const nuevaZonaData = Object.fromEntries(formData.entries());

            // Simulación de creación (reemplazar con lógica de backend)
            console.log('Nueva zona creada:', nuevaZonaData);
            alert('Nueva zona creada exitosamente');
            closeModal('modalNueva');
            // Aquí podrías recargar la tabla o actualizarla dinámicamente
        }

        // Cerrar modales al hacer clic fuera
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Exponer funciones al ámbito global
        window.editarZona = editarZona;
        window.abrirModalNueva = abrirModalNueva;
        window.eliminarZona = eliminarZona;
        window.showUserMenu = showUserMenu;
    </script>
</body>
</html>