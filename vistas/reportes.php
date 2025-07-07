<?php

session_start();
if (!isset($_SESSION['usuario_id'])) {
    // Usuario no autenticado, redirigir al login
    header("Location: ../login.html");
    exit;
}
$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nombreCompleto = $nombre . ' ' . $apellido;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SM - Reportes</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
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
            <a href="zonas.php" class="menu-item">
                <i class="fas fa-map-marked-alt"></i>
                <span class="menu-text">Zonas de Entrega</span>
            </a>
            <a href="reportes.php" class="menu-item active">
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
            <h2>Reportes</h2>
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName"><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-truck-fast"></i>
                    </div>
                    <h3>Entregas por Zona</h3>
                </div>
                <div class="chart-body">
                    <p>Visualización de entregas por zona de reparto</p>
                    <button class="btn-login">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3>Tiempos de Entrega</h3>
                </div>
                <div class="chart-body">
                    <p>Análisis de tiempos promedio de entrega</p>
                    <button class="btn-login">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user-clock"></i>
                    </div>
                    <h3>Rendimiento Domiciliarios</h3>
                </div>
                <div class="chart-body">
                    <p>Evaluación de rendimiento por domiciliario</p>
                    <button class="btn-login">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Clientes Frecuentes</h3>
                </div>
                <div class="chart-body">
                    <p>Análisis de pedidos por cliente</p>
                    <button class="btn-login">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
            </div>
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
    </script>
</body>

</html>