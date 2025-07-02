<?php
require_once 'conexion.php';
require_once 'verificar_sesion.php';

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id = intval($_GET['id']);
$conexion = obtenerConexion();

$query = "SELECT id, numero_documento, nombre, email FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);
    echo json_encode($usuario);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($conexion)]);
}

mysqli_close($conexion);
?>