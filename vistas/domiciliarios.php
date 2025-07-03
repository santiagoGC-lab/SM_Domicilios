<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
                <input type="text" class="form-control" placeholder="Buscar domiciliario..." />
                <button class="btn-login">
                    <i class="fas fa-user-plus"></i> Nuevo Domiciliario
                </button>
            </div>

            <div class="users-table">
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
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Juan Pérez</td>
                            <td>3001234567</td>
                            <td>Moto - ABC123</td>
                            <td>Norte</td>
                            <td>
                                <span class="estado-activo">Disponible</span>
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Editar Domiciliario</h2>
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
                <button type="submit" class="btn-login">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        // Sidebar responsive
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('collapsed');
        });

        document.addEventListener('click', function (e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('collapsed');
            }
        });

        function showUserMenu() {
            window.location.href = 'menuUsu.html';
        };

        // Funciones para el modal
        function editarDomiciliario(id) {
            document.getElementById('modalEditar').style.display = 'block';
        }

        function eliminarDomiciliario(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este domiciliario?')) {
                // Aquí iría la lógica para eliminar el domiciliario
            }
        }

        // Cerrar el modal
        document.querySelector('.close').onclick = function() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        // Cerrar el modal si se hace clic fuera de él
        window.onclick = function(event) {
            if (event.target == document.getElementById('modalEditar')) {
                document.getElementById('modalEditar').style.display = 'none';
            }
        }

        // Manejar el envío del formulario
        document.getElementById('formEditar').onsubmit = function(e) {
            e.preventDefault();
            // Aquí iría la lógica para actualizar el domiciliario
            document.getElementById('modalEditar').style.display = 'none';
        }
    </script>
</body>
</html>