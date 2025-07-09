<?php
session_start();
require_once "conexion.php";
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header("Location: ../login.html?error=" . urlencode("Método no permitido"));
    exit;
}

if (empty($_POST['numeroDocumento']) || empty($_POST['contrasena'])) {
    header("Location: ../login.html?error=" . urlencode("Por favor, completa todos los campos."));
    exit;
}

$numeroDocumento = filter_var($_POST['numeroDocumento'], FILTER_SANITIZE_STRING);
$contrasena = trim($_POST['contrasena']);

try {
    $conexion = ConectarDB();

    $stmt = $conexion->prepare("SELECT id_usuario, nombre, apellido, rol, contrasena FROM usuarios WHERE numero_documento = ? AND estado = 'activo'");
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("s", $numeroDocumento);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: ../login.html?error=" . urlencode("Usuario no encontrado o inactivo."));
        exit;
    }

    $usuario = $result->fetch_assoc();

    if (!password_verify($contrasena, $usuario['contrasena'])) {
        header("Location: ../login.html?error=" . urlencode("Contraseña incorrecta."));
        exit;
    }

    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
    $_SESSION['rol'] = $usuario['rol'];

    session_regenerate_id(true);

    header('Location: ../vistas/dashboard.php');
    exit;
} catch (Exception $e) {
    header("Location: ../login.html?error=" . urlencode("Error: " . $e->getMessage()));
    exit;
}
?>