<?php

if (!isset($_GET['page'])) {
    header("Location: index.php?page=login");
    exit;
}

require_once 'config.php';

// Iniciar sesión
session_start();

// Obtener la página solicitada
$page = $_GET['page'] ?? 'login';

// Verificar autenticación (excepto para login y registro)
$public_pages = ['login', 'registro'];
if (!in_array($page, $public_pages) && !isAuthenticated()) {
    redirect('index.php?page=login');
}

// Definir las páginas disponibles CON RUTAS CORRECTAS
$available_pages = [
    'login' => 'vistas/login.html',
    'dashboard' => 'vistas/dashboard.php',              // Está en vistas/
    'clientes' => 'vistas/clientes.php',                // Está en vistas/
    'domiciliarios' => 'vistas/domiciliarios.php',      // Está en vistas/
    'pedidos' => 'vistas/pedidos.php',                  // Está en vistas/
    'historial_pedidos' => 'vistas/historial_pedidos.php', // Está en vistas/
    'zonas' => 'vistas/zonas.php',                      // Está en vistas/
    'reportes' => 'vistas/reportes.php',                // Está en vistas/
    'tabla_usuarios' => 'vistas/tabla_usuarios.php',    // Está en vistas/
    'registro' => 'vistas/registro.php',                // Está en vistas/
];

// Verificar si la página existe
if (!isset($available_pages[$page])) {
    $page = 'login';
}

// Obtener la ruta del archivo CORRECTA
$file_path = ROOT_PATH . '/' . $available_pages[$page];

// Verificar si el archivo existe
if (!file_exists($file_path)) {
    die('Error: Página no encontrada - ' . $file_path);
}

// Incluir la página
include $file_path;
?>