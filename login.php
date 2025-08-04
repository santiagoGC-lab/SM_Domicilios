<?php
require_once 'config.php';
require_once 'servicios/conexion.php'; // CAMBIAR ESTA LÍNEA

// Mostrar errores siempre para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroDocumento = trim($_POST['numeroDocumento'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    // Validar campos
    if ($numeroDocumento === '' || $contrasena === '') {
        header('Location: vistas/login.html?error=' . urlencode('Número de documento y contraseña requeridos.'));
        exit();
    }

    // Buscar usuario en la base de datos por número de documento
    try {
        $db = ConectarDB(); // CAMBIAR ESTA LÍNEA
        $stmt = $db->prepare('SELECT id_usuario, numero_documento, contrasena, rol, estado, nombre, apellido FROM usuarios WHERE numero_documento = ? AND estado = "activo" LIMIT 1');
        $stmt->bind_param("s", $numeroDocumento);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();

        if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
            // Login exitoso
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['numero_documento'] = $usuario['numero_documento'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];
            $_SESSION['login_time'] = time();
            header('Location: vistas/dashboard.php');
            exit();
        }
        $stmt->close();
        $db->close();
    } catch (Exception $e) {
        error_log("Error de login: " . $e->getMessage());
    }
    // Si falla
    header('Location: vistas/login.html?error=' . urlencode('Número de documento o contraseña incorrectos.'));
    exit();
}

// Si no es POST, redirigir al login
header('Location: vistas/login.html');
exit();
