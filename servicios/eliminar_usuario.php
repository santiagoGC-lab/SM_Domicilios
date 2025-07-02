<?php
require_once 'conexion.php';
require_once 'verificar_sesion.php';

// Verificar que se recibió el ID
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = intval($_GET['id']);
$conexion = obtenerConexion();

// Eliminar el usuario
$query = "DELETE FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $query);
 mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conexion)]);
}

mysqli_close($conexion);
?>