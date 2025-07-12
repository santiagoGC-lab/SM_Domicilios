<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol'])) {
    header("Location: ../login.html");
    exit;
}

// Definir permisos por rol
$permisos = [
    'admin' => [
        'dashboard' => true,
        'pedidos' => true,
        'clientes' => true,
        'domiciliarios' => true,
        'zonas' => true,
        'reportes' => true,
        'crearUsu' => true,
        'tabla_usuarios' => true
    ],
    'org_domicilios' => [
        'dashboard' => false, // Los gestores no tienen acceso al dashboard
        'pedidos' => true,
        'clientes' => true,
        'domiciliarios' => true,
        'zonas' => true,
        'reportes' => true,
        'crearUsu' => false,
        'tabla_usuarios' => false
    ],
    'cajera' => [
        'dashboard' => false,
        'pedidos' => true,
        'clientes' => true,
        'domiciliarios' => false,
        'zonas' => false,
        'reportes' => true,
        'crearUsu' => false,
        'tabla_usuarios' => false
    ]
];

/**
 * Verifica si el usuario tiene permiso para acceder a una página específica
 */
function tienePermiso($pagina) {
    global $permisos;
    
    $rol = $_SESSION['rol'];
    
    if (!isset($permisos[$rol])) {
        return false;
    }
    
    return isset($permisos[$rol][$pagina]) && $permisos[$rol][$pagina];
}

/**
 * Redirige al usuario si no tiene permisos
 */
function verificarAcceso($pagina) {
    if (!tienePermiso($pagina)) {
        header("Location: ../vistas/pedidos.php?error=" . urlencode("No tienes permisos para acceder a esta página."));
        exit;
    }
}

/**
 * Obtiene el rol del usuario actual
 */
function obtenerRol() {
    return $_SESSION['rol'];
}

/**
 * Obtiene el nombre del usuario actual
 */
function obtenerNombreUsuario() {
    return $_SESSION['nombre'];
}

/**
 * Verifica si el usuario es administrador
 */
function esAdmin() {
    return $_SESSION['rol'] === 'admin';
}

/**
 * Verifica si el usuario es gestor de domicilios
 */
function esGestorDomicilios() {
    return $_SESSION['rol'] === 'org_domicilios';
}

/**
 * Verifica si el usuario es cajera
 */
function esCajera() {
    return $_SESSION['rol'] === 'cajera';
}
?> 