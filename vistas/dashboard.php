<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM Domicilios - Dashboard</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <a href="dashboard.php" class="menu-item active">
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
            <h2>Panel de Control - Domicilios</h2>
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName">Usuario</strong></span>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card" onclick="navigateTo('pedidos.php')">
                <div class="card-header">
                    <h3 class="card-title">Pedidos Hoy</h3>
                    <div class="card-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>
                <div class="card-value" id="totalPedidos">25</div>
                <div class="card-footer">10 en proceso, 15 entregados</div>
            </div>

            <div class="card" onclick="navigateTo('domiciliarios.php')">
                <div class="card-header">
                    <h3 class="card-title">Domiciliarios Activos</h3>
                    <div class="card-icon">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                </div>
                <div class="card-value" id="domiciliariosActivos">8</div>
                <div class="card-footer">5 en ruta, 3 disponibles</div>
            </div>

            <div class="card" onclick="navigateTo('pedidos.php?estado=pendiente')">
                <div class="card-header">
                    <h3 class="card-title">Tiempo Promedio</h3>
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="card-value" id="tiempoPromedio">35 min</div>
                <div class="card-footer">Meta: 30 minutos</div>
            </div>

            <div class="card" onclick="showAlerts()">
                <div class="card-header">
                    <h3 class="card-title">Pedidos Pendientes</h3>
                    <div class="card-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="card-value" id="pedidosPendientes">5</div>
                <div class="card-footer">Requieren atención inmediata</div>
            </div>
        </div>

        <div class="recent-activity">
            <div class="activity-header">
                <h3>Actividad Reciente</h3>
                <a href="#" class="btn-login" onclick="refreshActivity()">Actualizar</a>
            </div>

            <ul class="activity-list" id="activityList">
                <li class="activity-item" onclick="showActivityDetails(1)">
                    <div class="activity-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">Pedido #123 entregado exitosamente</div>
                        <div class="activity-time">Hace 10 minutos - por Juan Pérez</div>
                    </div>
                </li>

                <li class="activity-item" onclick="showActivityDetails(2)">
                    <div class="activity-icon">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">Nuevo pedido asignado a domiciliario</div>
                        <div class="activity-time">Hace 15 minutos - Pedido #124</div>
                    </div>
                </li>

                <li class="activity-item" onclick="showActivityDetails(3)">
                    <div class="activity-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">Nuevo cliente registrado</div>
                        <div class="activity-time">Hace 30 minutos - María López</div>
                    </div>
                </li>

                <li class="activity-item" onclick="showActivityDetails(4)">
                    <div class="activity-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">Nueva zona de entrega agregada</div>
                        <div class="activity-time">Hace 1 hora - Zona Norte</div>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeDashboard();
            setupEventListeners();
            loadDashboardData();
        });

        function initializeDashboard() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        }

        function setupEventListeners() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');

            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
                document.getElementById('mainContent').classList.toggle('expanded');
            });
        }

        function loadDashboardData() {
            // Aquí irían las llamadas a la API para cargar datos en tiempo real
        }

        function showUserMenu() {
            // Implementar menú de usuario
        }

        function navigateTo(url) {
            window.location.href = url;
        }

        function showAlerts() {
            // Implementar vista de alertas
        }

        function refreshActivity() {
            // Actualizar lista de actividades
        }

        function showActivityDetails(id) {
            // Mostrar detalles de la actividad
        }
    </script>
</body>

</html>