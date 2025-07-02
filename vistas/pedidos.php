<?php
require_once '../servicios/verificar_sesion.php';
verificarSesion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Gestión de Pedidos</title>
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
           <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <a href="pedidos.php" class="menu-item active">
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

    <div class="main-content">
        <div class="header">
            <h2>Gestión de Pedidos</h2>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Buscar pedidos...">
            </div>
            <div class="action-buttons">
                <button class="btn-login" id="btnAddPedido">
                    <i class="fas fa-plus"></i> Nuevo Pedido
                </button>
            </div>
        </div>

        <div class="dashboard-cards">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="card-title">Pedidos Pendientes</h3>
                </div>
                <div class="card-value">0</div>
                <div class="card-footer">Actualizado hace 5 minutos</div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="card-title">Completados Hoy</h3>
                </div>
                <div class="card-value">0</div>
                <div class="card-footer">Actualizado hace 5 minutos</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="card-title">Ingresos del Día</h3>
                </div>
                <div class="card-value">$0</div>
                <div class="card-footer">Actualizado hace 5 minutos</div>
            </div>
        </div>

        <div class="recent-activity">
            <div class="activity-header">
                <h3>Pedidos Recientes</h3>
            </div>
            <div class="activity-list">
                <!-- Los pedidos se cargarán dinámicamente aquí -->
            </div>
        </div>
    </div>
</body>
</html>