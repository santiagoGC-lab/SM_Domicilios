<?php
require_once '../config.php';
require_once 'conexion.php';
header('Content-Type: application/json');

// Función para guardar un cliente (crear o actualizar)
function guardarCliente($datos) {
    try {
        $db = ConectarDB();
        
        $id = isset($datos['id']) && $datos['id'] !== '' ? (int) $datos['id'] : null;
        $nombre = trim($datos['nombre'] ?? '');
        $documento = trim($datos['documento'] ?? '');
        $telefono = trim($datos['telefono'] ?? '');
        $direccion = trim($datos['direccion'] ?? '');
        $barrio = trim($datos['barrio'] ?? '');
        $tipoCliente = $datos['tipoCliente'] ?? 'regular';

        if (!$nombre || !$documento || !$telefono || !$direccion || !$barrio) {
            return ['error' => 'Faltan campos obligatorios'];
        }

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
                return ['error' => 'Ya existe un cliente con ese documento'];
            }
            $checkStmt->close();

            // Insertar nuevo cliente
            $stmt = $db->prepare("INSERT INTO clientes (nombre, documento, telefono, direccion, barrio, tipo_cliente) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $nombre, $documento, $telefono, $direccion, $barrio, $tipoCliente);
        }

        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return ['success' => true, 'id' => $id ?: $db->insert_id];
        } else {
            return ['error' => 'No se realizó ninguna modificación'];
        }

        $stmt->close();
        $db->close();

    } catch (mysqli_sql_exception $e) {
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            return ['error' => 'Ya existe un cliente con ese documento'];
        } else {
            return ['error' => 'Error SQL: ' . $e->getMessage()];
        }
    } catch (Exception $e) {
        return ['error' => 'Error general: ' . $e->getMessage()];
    }
}

// Función para eliminar un cliente
function eliminarCliente($id) {
    try {
        $db = ConectarDB();

        if (!$id || !is_numeric($id)) {
            return ['error' => 'ID de cliente inválido'];
        }

        $stmt = $db->prepare("DELETE FROM clientes WHERE id_cliente = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            return ['success' => true];
        } else {
            return ['error' => 'Cliente no encontrado o ya eliminado'];
        }

        $stmt->close();
        $db->close();

    } catch (Exception $e) {
        return ['error' => 'Error al eliminar cliente: ' . $e->getMessage()];
    }
}

// Función para obtener todos los clientes
function obtenerClientes() {
    try {
        $db = ConectarDB();
        $query = "SELECT id_cliente, nombre, documento, telefono, direccion, barrio, tipo_cliente FROM clientes WHERE estado = 'activo' ORDER BY id_cliente DESC";
        $result = $db->query($query);

        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }

        $db->close();
        return $clientes;

    } catch (Exception $e) {
        return ['error' => 'Error al obtener clientes: ' . $e->getMessage()];
    }
}

// Función para obtener un cliente por ID
function obtenerClientePorId($id) {
    try {
        $db = ConectarDB();
        
        if (!$id) {
            return ['error' => 'ID de cliente no proporcionado'];
        }

        $stmt = $db->prepare("SELECT id_cliente, nombre, documento, telefono, direccion, barrio, tipo_cliente FROM clientes WHERE id_cliente = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($cliente = $result->fetch_assoc()) {
            $stmt->close();
            $db->close();
            return $cliente;
        } else {
            $stmt->close();
            $db->close();
            return ['error' => 'Cliente no encontrado'];
        }

    } catch (Exception $e) {
        return ['error' => 'Error al obtener cliente: ' . $e->getMessage()];
    }
}

// Función para obtener un cliente por documento
function obtenerClientePorDocumento($documento) {
    try {
        $db = ConectarDB();
        if (!$documento) {
            return ['error' => 'Documento no proporcionado'];
        }
        $stmt = $db->prepare("SELECT id_cliente, nombre, documento, telefono, direccion, barrio, tipo_cliente FROM clientes WHERE documento = ? AND estado = 'activo'");
        $stmt->bind_param("s", $documento);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($cliente = $result->fetch_assoc()) {
            $stmt->close();
            $db->close();
            return $cliente;
        } else {
            $stmt->close();
            $db->close();
            return ['error' => 'Cliente no encontrado'];
        }
    } catch (Exception $e) {
        return ['error' => 'Error al obtener cliente: ' . $e->getMessage()];
    }
}

// Endpoint para manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'guardar':
            $resultado = guardarCliente($_POST);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'eliminar':
            $resultado = eliminarCliente($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'obtener':
            $resultado = obtenerClientes();
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;
            
        case 'obtener_por_id':
            $resultado = obtenerClientePorId($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'obtener_por_documento':
            $resultado = obtenerClientePorDocumento($_POST['documento']);
            if (isset($resultado['error'])) {
                http_response_code(404);
            }
            echo json_encode($resultado);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Para compatibilidad con el código existente
    $accion = $_GET['accion'] ?? '';
    
    if ($accion === 'obtener_por_id') {
        $resultado = obtenerClientePorId($_GET['id']);
        if (isset($resultado['error'])) {
            http_response_code(400);
        }
        echo json_encode($resultado);
    } else {
        $resultado = obtenerClientes();
        if (isset($resultado['error'])) {
            http_response_code(500);
        }
        echo json_encode($resultado);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?> 