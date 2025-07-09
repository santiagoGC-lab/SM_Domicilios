<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$nombreCompleto = trim($_POST['nombreCompleto'] ?? '');
$numeroDocumento = trim($_POST['numeroDocumento'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$rol = $_POST['rol'] ?? '';
if (!$nombreCompleto || !$numeroDocumento || !$contrasena || !$rol) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios']);
    exit;
}

$nombreArray = explode(' ', $nombreCompleto, 2);
$nombre = $nombreArray[0];
$apellido = $nombreArray[1] ?? '';

try {
    $conexion = ConectarDB();

    // Validar si el documento ya existe
    $stmt = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE numero_documento = ?");
    $stmt->bind_param("s", $numeroDocumento);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['error' => 'El documento ya está registrado']);
        exit;
    }

    // Hashear la contraseña
    $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar usuario
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, apellido, numero_documento, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $nombre, $apellido, $numeroDocumento, $contrasenaHash, $rol);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Error al registrar el usuario: " . $stmt->error);
    }

    $stmt->close();
    $conexion->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
}
?>