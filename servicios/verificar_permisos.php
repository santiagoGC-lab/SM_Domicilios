<?php
session_start();

// Mapeo de roles a secciones permitidas
function tienePermiso($seccion) {
    if (!isset($_SESSION['rol'])) {
        return false;
    }
    $rol = $_SESSION['rol'];
    $permisosPorRol = [
        'admin' => ['dashboard', 'clientes', 'pedidos', 'domiciliarios', 'zonas', 'reportes', 'tabla_usuarios'],
        'cajera' => ['dashboard', 'clientes', 'pedidos', 'reportes'],
        'org_domicilios' => ['dashboard', 'domiciliarios', 'zonas', 'reportes']
    ];
    return in_array($seccion, $permisosPorRol[$rol] ?? []);
}

function verificarAcceso($seccion) {
    if (!isset($_SESSION['rol'])) {
        header('Location: ../vistas/login.html');
        exit();
    }
    if (!tienePermiso($seccion)) {
        header('Location: ../vistas/dashboard.php?error=sin_permiso');
        exit();
    }
}

function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function obtenerNombreUsuario() {
    if (isset($_SESSION['nombre']) && isset($_SESSION['apellido'])) {
        return $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
    }
    return '';
} 