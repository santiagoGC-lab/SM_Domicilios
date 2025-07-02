<?php
require_once '../servicios/verificar_sesion.php';
verificarSesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SM - Usuarios</title>
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/usuarios.css" />
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
            <a href="productos.php" class="menu-item">
                <i class="fas fa-box"></i>
                <span class="menu-text">Productos</span>
            </a>
            <a href="categorias.php" class="menu-item">
                <i class="fas fa-tags"></i>
                <span class="menu-text">Categorías</span>
            </a>
            <a href="movimientos.php" class="menu-item">
                <i class="fas fa-exchange-alt"></i>
                <span class="menu-text">Movimientos</span>
            </a>
            <a href="usuarios.php" class="menu-item active">
                <i class="fas fa-users"></i>
                <span class="menu-text">Usuarios</span>
            </a>
            <a href="reportes.html" class="menu-item">
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
            <h2>Gestión de Usuarios</h2>
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName">Usuario</strong></span>
            </div>
        </div>
        
        <div class="users-section">
            <div class="users-actions">
                <input type="text" class="form-control" placeholder="Agregar usuario..." />
                <button class="btn-login">
                    <i class="fas fa-user-plus"></i> Nuevo Usuario
                </button>
            </div>

            <div class="users-table">
                <?php include 'tabla_usuarios.php'; ?>
            </div>
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
    </script>
<!-- Modal de Edición -->
<div id="modalEditar" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Editar Usuario</h2>
        <form id="formEditar">
            <input type="hidden" id="userId" name="id">
            <div class="form-group">
                <label for="numero_documento">Número de Documento:</label>
                <input type="text" id="numero_documento" name="numero_documento" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn-login">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
    // Función para editar usuario
    function editarUsuario(id) {
        // Obtener datos del usuario
        fetch(`../servicios/obtener_usuario.php?id=${id}`)
            .then(response => response.json())
            .then(usuario => {
                // Llenar el formulario con los datos del usuario
                document.getElementById('userId').value = usuario.id;
                document.getElementById('nombre').value = usuario.nombre;
                document.getElementById('email').value = usuario.email;
                document.getElementById('rol').value = usuario.rol;
                document.getElementById('estado').value = usuario.estado;
                
                // Mostrar el modal
                document.getElementById('modalEditar').style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al obtener los datos del usuario');
            });
    }

    // Función para eliminar usuario (mantener la misma que ya teníamos)
    function eliminarUsuario(id) {
        if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
            fetch(`../servicios/eliminar_usuario.php?id=${id}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Usuario eliminado correctamente');
                    window.location.reload();
                } else {
                    alert('Error al eliminar el usuario: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al eliminar el usuario');  
            });
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
        const formData = new FormData(this);

        fetch('../servicios/actualizar_usuario.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario actualizado correctamente');
                document.getElementById('modalEditar').style.display = 'none';
                window.location.reload();
            } else {
                alert('Error al actualizar el usuario: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el usuario');
        });
    }
</script>
</body>
</html>
