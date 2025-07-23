<?php
require_once '../config.php';
require_once 'conexion.php';
header('Content-Type: application/json');

// Función para guardar un domiciliario (crear o actualizar)
function guardarDomiciliario($datos) {
    try {
        $db = ConectarDB();
        
        $id = $datos['id'] ?? null;
        $nombre = trim($datos['nombre'] ?? '');
        $telefono = trim($datos['telefono'] ?? '');
        $vehiculo = trim($datos['tipoVehiculo'] ?? '');
        $placa = trim($datos['placa'] ?? '');
        $zona = trim($datos['zona'] ?? '');
        $estado = trim($datos['estado'] ?? 'disponible');

        // Validación básica
        if (!$nombre || !$telefono || !$vehiculo || !$placa || !$estado) {
            return ['success' => false, 'error' => 'Todos los campos son obligatorios'];
        }

        // Obtener id_zona
        $stmtZona = $db->prepare("SELECT id_zona FROM zonas WHERE nombre = ?");
        $stmtZona->bind_param("s", $zona);
        $stmtZona->execute();
        $resultZona = $stmtZona->get_result();
        $id_zona = $resultZona->fetch_assoc()['id_zona'] ?? null;
        $stmtZona->close();

        if ($id) {
            // Actualizar
            $stmt = $db->prepare("UPDATE domiciliarios SET nombre=?, telefono=?, vehiculo=?, placa=?, id_zona=?, estado=? WHERE id_domiciliario=?");
            $stmt->bind_param("ssssisi", $nombre, $telefono, $vehiculo, $placa, $id_zona, $estado, $id);
        } else {
            // Crear
            $stmt = $db->prepare("INSERT INTO domiciliarios (nombre, telefono, vehiculo, placa, id_zona, estado) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssis", $nombre, $telefono, $vehiculo, $placa, $id_zona, $estado);
        }

        $success = $stmt->execute();
        $stmt->close();
        $db->close();

        return ['success' => $success];

    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
    }
}

// Función para eliminar un domiciliario
function eliminarDomiciliario($id) {
    try {
        $db = ConectarDB();
        
        if (!$id || !is_numeric($id)) {
            return ['success' => false, 'error' => 'ID inválido'];
        }

        $stmt = $db->prepare("DELETE FROM domiciliarios WHERE id_domiciliario = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        $db->close();

        return ['success' => $success];

    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
    }
}

// Función para obtener todos los domiciliarios
function obtenerDomiciliarios() {
    try {
        $db = ConectarDB();
        $result = $db->query("SELECT d.id_domiciliario, d.nombre, d.telefono, d.vehiculo, d.placa, z.nombre AS zona, d.estado 
                              FROM domiciliarios d 
                              LEFT JOIN zonas z ON d.id_zona = z.id_zona");
        $domiciliarios = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        return $domiciliarios;

    } catch (Exception $e) {
        return ['error' => 'Error al obtener domiciliarios: ' . $e->getMessage()];
    }
}

// Función para obtener un domiciliario por ID
function obtenerDomiciliarioPorId($id) {
    try {
        $db = ConectarDB();
        
        if (!$id || !is_numeric($id)) {
            return [];
        }

        $stmt = $db->prepare("SELECT d.id_domiciliario, d.nombre, d.telefono, d.vehiculo, d.placa, z.nombre AS zona, d.estado 
                              FROM domiciliarios d 
                              LEFT JOIN zonas z ON d.id_zona = z.id_zona 
                              WHERE d.id_domiciliario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $domiciliario = $result->fetch_assoc();
        $stmt->close();
        $db->close();
        
        return $domiciliario;

    } catch (Exception $e) {
        return ['error' => 'Error al obtener domiciliario: ' . $e->getMessage()];
    }
}

// Nueva función para obtener domiciliarios paginados
function obtenerDomiciliariosPaginados($pagina, $por_pagina) {
    try {
        $db = ConectarDB();
        $offset = ($pagina - 1) * $por_pagina;
        $sql = "SELECT d.id_domiciliario, d.nombre, d.telefono, d.vehiculo, d.placa, z.nombre AS zona, d.estado
                FROM domiciliarios d
                LEFT JOIN zonas z ON d.id_zona = z.id_zona
                LIMIT ? OFFSET ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $por_pagina, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $domiciliarios = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        // Obtener el total
        $total = $db->query("SELECT COUNT(*) as total FROM domiciliarios")->fetch_assoc()['total'];
        $db->close();
        return ['domiciliarios' => $domiciliarios, 'total' => $total];
    } catch (Exception $e) {
        return ['error' => 'Error al obtener domiciliarios: ' . $e->getMessage()];
    }
}

// Función para obtener domiciliarios disponibles
function obtenerDomiciliariosDisponibles() {
    try {
        $db = ConectarDB();
        $result = $db->query("SELECT id_domiciliario, nombre FROM domiciliarios WHERE estado = 'disponible'");
        $domiciliarios = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        return $domiciliarios;
    } catch (Exception $e) {
        return ['error' => 'Error al obtener domiciliarios disponibles: ' . $e->getMessage()];
    }
}

// Endpoint para manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'guardar':
            $resultado = guardarDomiciliario($_POST);
            echo json_encode($resultado);
            break;
            
        case 'eliminar':
            $resultado = eliminarDomiciliario($_POST['id']);
            echo json_encode($resultado);
            break;
            
        case 'obtener':
            $resultado = obtenerDomiciliarios();
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;
            
        case 'obtener_por_id':
            $resultado = obtenerDomiciliarioPorId($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;
            
        case 'paginar':
            $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
            $por_pagina = isset($_POST['por_pagina']) ? intval($_POST['por_pagina']) : 5;
            $resultado = obtenerDomiciliariosPaginados($pagina, $por_pagina);
            echo json_encode($resultado);
            break;
            
        case 'disponibles':
            $resultado = obtenerDomiciliariosDisponibles();
            if (isset($resultado['error'])) {
                http_response_code(500);
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
        $resultado = obtenerDomiciliarioPorId($_GET['id']);
        if (isset($resultado['error'])) {
            http_response_code(500);
        }
        echo json_encode($resultado);
    } else {
        $resultado = obtenerDomiciliarios();
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