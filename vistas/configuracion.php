
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SM - Configuración</title>
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/configuracion.css" />
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
            <a href="dashboard.php" class="menu-item ">
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
            <a href="usuarios.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Usuarios</span>
            </a>
            <a href="reportes.html" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <a href="configuracion.php" class="menu-item active">
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
            <h2>Configuración del Sistema</h2>
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName">Usuario</strong></span>
            </div>
        </div>

        <div class="config-section admin-only">
            <h3>Configuración General <span class="admin-badge">Admin</span></h3>
            <div class="form-group">
                <label>Nombre de la Empresa</label>
                <input type="text" class="form-control" placeholder="Nombre de la empresa..." />
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Email de Contacto</label>
                    <input type="email" class="form-control" placeholder="Email..." />
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="tel" class="form-control" placeholder="Teléfono..." />
                </div>
            </div>
        </div>

        <div class="config-section advanced-config">
            <h3>Configuración Avanzada <span class="warning-badge">Avanzado</span></h3>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                Estas configuraciones son para usuarios avanzados.
            </div>
            <div class="form-group">
                <label>Copias de Seguridad Automáticas</label>
                <label class="switch">
                    <input type="checkbox">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="form-group">
                <label>Intervalo de Respaldo (días)</label>
                <input type="number" class="form-control" min="1" value="7" />
            </div>
        </div>

        <div class="section-actions">
            <button class="btn btn-secondary">Cancelar</button>
            <button class="btn btn-primary"><i class="fas fa-save"></i> Guardar Cambios</button>
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

        // Theme selector
        document.querySelectorAll('.theme-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.theme-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
    </script>
</body>
</html>