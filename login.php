<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'servicios/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroDocumento = trim($_POST['numeroDocumento'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    // Validar campos
    if ($numeroDocumento === '' || $contrasena === '') {
        header('Location: vistas/login.html?error=' . urlencode('Número de documento y contraseña requeridos.'));
        exit();
    }

    // Buscar usuario en la base de datos por número de documento
    $conexion = ConectarDB();
    $stmt = $conexion->prepare('SELECT id_usuario, numero_documento, contrasena, rol, estado, nombre, apellido FROM usuarios WHERE numero_documento = ? AND estado = "activo" LIMIT 1');
    $stmt->bind_param('s', $numeroDocumento);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado && $resultado->num_rows === 1) {
        $row = $resultado->fetch_assoc();
        if (password_verify($contrasena, $row['contrasena'])) {
            // Login correcto: setear variables de sesión
            // $_SESSION['usuario'] = $row['usuario']; // Eliminar porque no existe
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['numero_documento'] = $row['numero_documento'];
            $_SESSION['rol'] = $row['rol'];
            $_SESSION['nombre'] = $row['nombre'];
            $_SESSION['apellido'] = $row['apellido'];
            // $_SESSION['permisos'] = ...; // Eliminar porque no existe
            // Redirigir al dashboard
            header('Location: vistas/dashboard.php');
            exit();
        }
    }
    // Si falla
    header('Location: vistas/login.html?error=' . urlencode('Número de documento o contraseña incorrectos.'));
    exit();
}
// Si se accede por GET, redirigir al login
header('Location: vistas/login.html');
exit(); 