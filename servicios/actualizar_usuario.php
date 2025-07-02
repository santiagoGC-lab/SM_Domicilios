<?php
require_once 'conexion.php';
require_once 'verificar_sesion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = intval($_POST['id']);
$numero_documento = $_POST['numero_documento'];
$nombre = $_POST['nombre'];
$email = $_POST['email'];

$conexion = obtenerConexion();

$query = "UPDATE usuarios SET numero_documento = ?, nombre = ?, email = ? WHERE id = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, 'sssi', $numero_documento, $nombre, $email, $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conexion)]);
}

mysqli_close($conexion);
?>