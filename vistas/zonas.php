<?php
require_once '../servicios/verificar_sesion.php';
verificarSesion();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SM - Zonas de Entrega</title>
    <link rel="stylesheet" href="../componentes/dashboard.css" />
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
                <input type="text" class="form-control" placeholder="Buscar zona..." />
                <button class="btn-login" onclick="abrirModalNueva()">
                    <i class="fas fa-plus"></i> Nueva Zona
                </button>
            </div>

            <div class="users-table">
                <table class="users-table">
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
                    <tbody>
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
            <span class="close">&times;</span>
            <h2>Editar Zona</h2>
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
                <button type="submit" class="btn-login">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <!-- Modal de Nueva Zona -->
    <div id="modalNueva" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Nueva Zona</h2>
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
                <button type="submit" class="btn-login">Crear Zona</button>
            </form>
        </div>
    </div>

    <script>
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

        function showUserMenu() {
            window.location.href = 'menuUsu.html';
        };

        // Funciones para el modal de edición
        function editarZona(id) {
            document.getElementById('modalEditar').style.display = 'block';
            // Aquí iría la lógica para cargar los datos de la zona con ID 'id'
            document.getElementById('zonaId').value = id;
            document.getElementById('nombre').value = 'Norte'; // Ejemplo de datos
            document.getElementById('ciudad').value = 'Ciudad Principal';
            document.getElementById('tarifa').value = 5000;
            document.getElementById('estado').value = 'activo';
        }

        // Funciones para el modal de nueva zona
        function abrirModalNueva() {
            document.getElementById('modalNueva').style.display = 'block';
            // Limpiar el formulario al abrir
            document.getElementById('formNueva').reset();
        }

        // Cerrar los modales
        document.querySelectorAll('.close').forEach(closeBtn => {
            closeBtn.onclick = function() {
                this.closest('.modal').style.display = 'none';
            };
        });

        // Cerrar los modales si se hace clic fuera de ellos
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Manejar el envío del formulario de edición
        document.getElementById('formEditar').onsubmit = function(e) {
            e.preventDefault();
            // Aquí iría la lógica para actualizar la zona
            alert('Zona actualizada: ' + document.getElementById('nombre').value);
            document.getElementById('modalEditar').style.display = 'none';
        }

        // Manejar el envío del formulario de nueva zona
        document.getElementById('formNueva').onsubmit = function(e) {
            e.preventDefault();
            // Aquí iría la lógica para crear la nueva zona (por ejemplo, enviar datos a un servidor)
            const nuevaZona = {
                nombre: document.getElementById('nuevoNombre').value,
                ciudad: document.getElementById('nuevoCiudad').value,
                tarifa: document.getElementById('nuevoTarifa').value,
                estado: document.getElementById('nuevoEstado').value
            };
            alert('Nueva zona creada: ' + JSON.stringify(nuevaZona));
            document.getElementById('modalNueva').style.display = 'none';
            // Aquí podrías añadir lógica para recargar la tabla o agregar la zona dinámicamente
        }

        function eliminarZona(id) {
            if (confirm('¿Estás seguro de que deseas eliminar esta zona?')) {
                // Aquí iría la lógica para eliminar la zona
                alert('Zona con ID ' + id + ' eliminada');
            }
        }
    </script>
</body>

</html>