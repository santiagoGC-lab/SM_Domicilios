<?php
require_once '../servicios/verificar_sesion.php';
verificarSesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="../componentes/menuUsu.css">
    <link rel="stylesheet" href="../componentes/styles.css">
</head>
<body>
    <div class="main-container">
        <nav class="sidebar">
            <div class="logo-container">
                <img src="../img/logo.png" alt="Logo SuperMercar" class="logo">
            </div>
            <ul class="nav-links">
                <li><a href="inicio.html">Inicio</a></li>
                <li><a href="menuUsu.html" class="active">Mi Perfil</a></li>
                <li><a href="misReservas.html">Mis Reservas</a></li>
                <a href="../servicios/cerrar_sesion.php" class="menu-cerrar">
            </ul>
        </nav>

        <div class="content-area">
            <div class="profile-container">
                <div class="profile-header">
                    <img src="../img/default-avatar.png" alt="Foto de Perfil" class="profile-pic"/>
                    <div class="profile-info">
                        <h1>Nombre del Usuario</h1>
                        <p class="user-id">ID: #12345</p>
                        <button class="primary-btn">Editar Perfil</button>
                    </div>
                </div>

                <div class="profile-section">
                    <h2>Información Personal</h2>
                    <div class="info-grid">
                        <p><strong>Correo:</strong> usuario@ejemplo.com</p>
                        <p><strong>Teléfono:</strong> 300 123 4567</p>
                        <p><strong>Documento:</strong> 1234567890</p>
                        <p><strong>Ciudad:</strong> Bogotá</p>
                    </div>
                </div>

                <div class="profile-section">
                    <h2>Historial de Reservas</h2>
                    <div class="reservations-summary">
                        <div class="stat-box">
                            <span class="number">5</span>
                            <span class="label">Reservas Totales</span>
                        </div>
                        <div class="stat-box">
                            <span class="number">2</span>
                            <span class="label">Este Mes</span>
                        </div>
                        <a href="misReservas.html" class="secondary-btn">Ver Todas</a>
                    </div>
                </div>

                <div class="profile-section">
                    <h2>Preferencias</h2>
                    <div class="preferences-grid">
                        <div class="preference-item">
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                            <span>Notificaciones por correo</span>
                        </div>
                        <div class="preference-item">
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                            <span>Notificaciones push</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
