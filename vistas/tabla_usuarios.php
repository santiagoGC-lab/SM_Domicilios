<?php
require_once '../servicios/verificar_permisos.php';
verificarAcceso('tabla_usuarios');

require_once '../servicios/conexion.php';

// Obtener la conexión
$conexion = conectarDB();

// Consultar usuarios
$query = "SELECT id_usuario, CONCAT(nombre, ' ', apellido) AS nombre, rol, estado, numero_documento, fecha_creacion FROM usuarios ORDER BY fecha_creacion DESC";
$resultado = mysqli_query($conexion, $query);

$usuarios = [];
while ($usuario = mysqli_fetch_assoc($resultado)) {
    $usuarios[] = $usuario;
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Gestión de Usuarios</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/menuUsu.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <?php if (tienePermiso('dashboard')): ?>
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <?php endif; ?>
            <a href="pedidos.php" class="menu-item">
                <i class="fas fa-shopping-bag"></i>
                <span class="menu-text">Pedidos</span>
            </a>
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Clientes</span>
            </a>
            <?php if (tienePermiso('domiciliarios')): ?>
            <a href="domiciliarios.php" class="menu-item">
                <i class="fas fa-motorcycle"></i>
                <span class="menu-text">Domiciliarios</span>
            </a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
            <a href="zonas.php" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                <span class="menu-text">Zonas de Entrega</span>
            </a>
            <?php endif; ?>
            <a href="reportes.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <?php if (esAdmin()): ?>
            <a href="tabla_usuarios.php" class="menu-item active">
                <i class="fas fa-users-cog"></i>
                <span class="menu-text">Gestionar Usuarios</span>
            </a>

            <?php endif; ?>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Gestión de Usuarios</h2>
            <div class="header-actions">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar usuarios..." id="searchInput">
                </div>
                <button class="btn-login" onclick="abrirModalNuevoUsuario()">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </button>
            </div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Total Usuarios</h3>
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="card-value"><?php echo count($usuarios); ?></div>
                <div class="card-footer">Usuarios registrados</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Administradores</h3>
                    <div class="card-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
                <div class="card-value"><?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'admin'; })); ?></div>
                <div class="card-footer">Con acceso completo</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestores</h3>
                    <div class="card-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
                <div class="card-value"><?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'org_domicilios'; })); ?></div>
                <div class="card-footer">Gestión de domicilios</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Cajeras</h3>
                    <div class="card-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
                <div class="card-value"><?php echo count(array_filter($usuarios, function($u) { return $u['rol'] === 'cajera'; })); ?></div>
                <div class="card-footer">Atención al cliente</div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo $usuario['id_usuario']; ?></td>
                            <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['numero_documento']); ?></td>
                            <td>
                                <span class="rol-badge rol-<?php echo $usuario['rol']; ?>">
                                    <?php 
                                    switch($usuario['rol']) {
                                        case 'admin': echo 'Administrador'; break;
                                        case 'org_domicilios': echo 'Gestor'; break;
                                        case 'cajera': echo 'Cajera'; break;
                                        default: echo ucfirst($usuario['rol']);
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="estado-<?php echo strtolower($usuario['estado']); ?> estado">
                                    <?php echo ucfirst($usuario['estado']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-editar" onclick="editarUsuario(<?php echo $usuario['id_usuario']; ?>)" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($usuario['id_usuario'] != $_SESSION['usuario_id']): ?>
                                    <button class="btn btn-eliminar" onclick="eliminarUsuario(<?php echo $usuario['id_usuario']; ?>)" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                    <button class="btn btn-toggle" onclick="toggleEstado(<?php echo $usuario['id_usuario']; ?>, '<?php echo $usuario['estado']; ?>')" title="<?php echo $usuario['estado'] === 'activo' ? 'Desactivar' : 'Activar'; ?>">
                                        <i class="fas fa-<?php echo $usuario['estado'] === 'activo' ? 'pause' : 'play'; ?>"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para crear nuevo usuario -->
    <div id="modalNuevoUsuario" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Crear Nuevo Usuario</h2>
                <span class="close" onclick="closeModal('modalNuevoUsuario')">×</span>
            </div>
            <form id="formNuevoUsuario">
                <div class="form-group">
                    <label for="nombreCompleto">Nombre Completo</label>
                    <input type="text" id="nombreCompleto" name="nombreCompleto" class="form-control" 
                           placeholder="Ej: Juan Pérez" required>
                </div>
                <div class="form-group">
                    <label for="numeroDocumento">Número de Documento</label>
                    <input type="text" id="numeroDocumento" name="numeroDocumento" class="form-control" 
                           placeholder="Ej: 123456789" minlength="6" maxlength="12" pattern="[0-9]+" required>
                </div>
                <div class="form-group">
                    <label for="rol">Rol</label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="" disabled selected>Selecciona un rol</option>
                        <option value="admin">Administrador</option>
                        <option value="org_domicilios">Gestor de Domicilios</option>
                        <option value="cajera">Cajera</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="contrasena">Contraseña</label>
                    <div class="password-input">
                        <input type="password" id="contrasena" name="contrasena" class="form-control" 
                               placeholder="Mínimo 8 caracteres" maxlength="20" minlength="8" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('contrasena', this)"></i>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalNuevoUsuario')">Cancelar</button>
                    <button type="submit" class="btn-login">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para editar usuario -->
    <div id="modalEditarUsuario" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Editar Usuario</h2>
                <span class="close" onclick="closeModal('modalEditarUsuario')">×</span>
            </div>
            <form id="formEditarUsuario">
                <input type="hidden" id="usuarioId" name="id">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="numeroDocumento">Documento:</label>
                    <input type="text" id="numeroDocumento" name="numeroDocumento" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="rol">Rol:</label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="admin">Administrador</option>
                        <option value="org_domicilios">Gestor de Domicilios</option>
                        <option value="cajera">Cajera</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalEditarUsuario')">Cancelar</button>
                    <button type="submit" class="btn-login">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function abrirModalNuevoUsuario() {
            document.getElementById('formNuevoUsuario').reset();
            document.getElementById('modalNuevoUsuario').style.display = 'block';
        }

        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Manejar el formulario de nuevo usuario
        document.getElementById('formNuevoUsuario').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../servicios/registro.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Usuario creado exitosamente');
                    closeModal('modalNuevoUsuario');
                    location.reload(); // Recargar para mostrar el nuevo usuario
                } else {
                    alert('Error: ' + (data.error || 'Error desconocido'));
                }
            })
            .catch(error => {
                alert('Error al crear el usuario');
                console.error('Error:', error);
            });
        });

        function editarUsuario(id) {
            // Aquí implementarías la lógica para cargar los datos del usuario
            // y mostrar el modal de edición
            alert('Función de edición en desarrollo');
        }

        function eliminarUsuario(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
                // Aquí implementarías la lógica para eliminar el usuario
                alert('Función de eliminación en desarrollo');
            }
        }

        function toggleEstado(id, estadoActual) {
            const nuevoEstado = estadoActual === 'activo' ? 'inactivo' : 'activo';
            const accion = estadoActual === 'activo' ? 'desactivar' : 'activar';
            
            if (confirm(`¿Estás seguro de que deseas ${accion} este usuario?`)) {
                // Aquí implementarías la lógica para cambiar el estado
                alert(`Función de ${accion} en desarrollo`);
            }
        }
    </script>
</body>

</html>