<?php
// Archivo principal de entrada para SM_Domicilios
// Este archivo maneja el enrutamiento básico de la aplicación

// Incluir configuración
require_once 'config.php';

// Iniciar sesión
session_start();

// Obtener la página solicitada
$page = $_GET['page'] ?? 'login';

// Verificar autenticación (excepto para login y registro)
$public_pages = ['login', 'registro', 'recuperar-contra', 'reset-password'];
if (!in_array($page, $public_pages) && !isAuthenticated()) {
    redirect('index.php?page=login');
}

// Definir las páginas disponibles
$available_pages = [
    'login' => 'vistas/login.html',
    'dashboard' => 'dashboard.php',
    'clientes' => 'clientes.php',
    'domiciliarios' => 'domiciliarios.php',
    'pedidos' => 'pedidos.php',
    'historial_pedidos' => 'historial_pedidos.php',
    'zonas' => 'zonas.php',
    'reportes' => 'reportes.php',
    'tabla_usuarios' => 'tabla_usuarios.php',
    'registro' => 'registro.php',
    'recuperar-contra' => 'recuperar-contra.html',
    'reset-password' => 'reset-password.php'
];

// Verificar si la página existe
if (!isset($available_pages[$page])) {
    $page = 'vistas/login.html';
}

// Obtener la ruta del archivo
$file_path = getViewPath($available_pages[$page]);

// Verificar si el archivo existe
if (!file_exists($file_path)) {
    die('Error: Página no encontrada');
}

// Incluir la página
include $file_path;
?> 