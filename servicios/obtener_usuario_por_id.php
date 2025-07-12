<?php
require_once 'verificar_permisos.php';
verificarAcceso('tabla_usuarios');

require_once 'conexion.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID de usuario inválido']);
    exit;
}

$id = intval($_GET['id']);

try {
    $conexion = conectarDB();
    
    $query = "SELECT id_usuario, nombre, apellido, numero_documento, rol, estado, fecha_creacion 
              FROM usuarios WHERE id_usuario = ?";
    
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if ($usuario = mysqli_fetch_assoc($resultado)) {
        echo json_encode(['success' => true, 'usuario' => $usuario]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado']);
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al obtener el usuario: ' . $e->getMessage()]);
}
?> 