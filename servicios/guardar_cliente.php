<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Recibir y validar datos
$id = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
$nombre = trim($_POST['nombre'] ?? '');
$documento = trim($_POST['documento'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$barrio = trim($_POST['barrio'] ?? '');
$tipoCliente = $_POST['tipoCliente'] ?? 'regular';

if (!$nombre || !$documento || !$telefono || !$direccion || !$barrio) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos obligatorios']);
    exit;
}

try {
    $db = ConectarDB();

    if ($id) {
        // Actualizar cliente existente
        $stmt = $db->prepare("UPDATE clientes SET nombre = ?, documento = ?, telefono = ?, direccion = ?, barrio = ?, tipo_cliente = ? WHERE id_cliente = ?");
        $stmt->bind_param("ssssssi", $nombre, $documento, $telefono, $direccion, $barrio, $tipoCliente, $id);
    } else {
        // Verificar si el documento ya existe antes de insertar
        $checkStmt = $db->prepare("SELECT id_cliente FROM clientes WHERE documento = ?");
        $checkStmt->bind_param("s", $documento);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            $checkStmt->close();
            http_response_code(409);
            echo json_encode(['error' => 'Ya existe un cliente con ese documento']);
            exit;
        }
        $checkStmt->close();

        // Insertar nuevo cliente
        $stmt = $db->prepare("INSERT INTO clientes (nombre, documento, telefono, direccion, barrio, tipo_cliente) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $documento, $telefono, $direccion, $barrio, $tipoCliente);
    }

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'id' => $id ?: $db->insert_id]);
    } else {
        throw new Exception("No se realizó ninguna modificación");
    }

    $stmt->close();
    $db->close();

} catch (mysqli_sql_exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate entry')) {
        http_response_code(409);
        echo json_encode(['error' => 'Ya existe un cliente con ese documento']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error SQL: ' . $e->getMessage()]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
}
?>