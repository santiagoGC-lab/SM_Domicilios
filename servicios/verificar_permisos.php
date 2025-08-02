<?php
session_start();

// Mapeo de roles a secciones permitidas
function tienePermiso($seccion)
{
    if (!isset($_SESSION['rol'])) {
        return false;
    }
    $rol = $_SESSION['rol'];
    $permisosPorRol = [
        'admin' => [
            'tabla_usuarios',
            'reportes',
            'clientes',
            'domiciliarios',
            'vehiculos',
            'zonas',
            'despacho',
            'pedidos',
            'dashboard',
            'ventana_flotante',
            'coordinador'
        ],
        'org_domicilios' => [
            'reportes',
            'clientes',
            'domiciliarios',
            'vehiculos',
            'despacho',
            'pedidos',
            'dashboard',
            'ventana_flotante',
            'coordinador'
        ],
        'cajera' => [
            'pedidos',
            'dashboard'
        ]
    ];
    return in_array($seccion, $permisosPorRol[$rol] ?? []);
}

function verificarAcceso($seccion)
{
    if (!isset($_SESSION['rol'])) {
        header('Location: ../vistas/login.html');
        exit();
    }
    if (!tienePermiso($seccion)) {
        header('Location: ../vistas/dashboard.php?error=sin_permiso');
        exit();
    }
}

function esAdmin()
{
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}

function obtenerNombreUsuario()
{
    if (isset($_SESSION['nombre']) && isset($_SESSION['apellido'])) {
        return $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
    }
    return '';
}
