<?php
require_once '../config.php';
require_once 'conexion.php';
header('Content-Type: application/json');
session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Función para obtener vehículos disponibles
function obtenerVehiculosDisponibles() {
    try {
        $db = ConectarDB();
        $result = $db->query("SELECT id_vehiculo, tipo, placa FROM vehiculos WHERE estado = 'disponible'");
        $vehiculos = $result->fetch_all(MYSQLI_ASSOC);
        $db->close();
        return $vehiculos;
    } catch (Exception $e) {
        return ['error' => 'Error al obtener vehículos disponibles: ' . $e->getMessage()];
    }
}

// Función para obtener un vehículo por ID
function obtenerVehiculo($id) {
    try {
        $db = ConectarDB();
        $stmt = $db->prepare("SELECT * FROM vehiculos WHERE id_vehiculo = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $vehiculo = $result->fetch_assoc();
        $stmt->close();
        $db->close();
        
        if (!$vehiculo) {
            return ['error' => 'Vehículo no encontrado'];
        }
        
        return $vehiculo;
    } catch (Exception $e) {
        return ['error' => 'Error al obtener vehículo: ' . $e->getMessage()];
    }
}

// Función para crear vehículo
function crearVehiculo($datos) {
    try {
        $db = ConectarDB();
        
        // Validar datos requeridos
        if (empty($datos['tipo']) || empty($datos['placa'])) {
            return ['error' => 'Tipo y placa son requeridos'];
        }
        
        // Verificar que la placa no exista
        $stmt = $db->prepare("SELECT id_vehiculo FROM vehiculos WHERE placa = ?");
        $stmt->bind_param("s", $datos['placa']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            $db->close();
            return ['error' => 'Ya existe un vehículo con esa placa'];
        }
        $stmt->close();
        
        // Insertar vehículo
        $stmt = $db->prepare("INSERT INTO vehiculos (tipo, placa, estado, descripcion) VALUES (?, ?, ?, ?)");
        $estado = $datos['estado'] ?? 'disponible';
        $descripcion = $datos['descripcion'] ?? null;
        $stmt->bind_param("ssss", $datos['tipo'], $datos['placa'], $estado, $descripcion);
        
        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return ['success' => true];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $db->close();
            return ['error' => 'Error al crear vehículo: ' . $error];
        }
    } catch (Exception $e) {
        return ['error' => 'Error al crear vehículo: ' . $e->getMessage()];
    }
}

// Función para actualizar vehículo
function actualizarVehiculo($datos) {
    try {
        $db = ConectarDB();
        
        // Validar datos requeridos
        if (empty($datos['id']) || empty($datos['tipo']) || empty($datos['placa'])) {
            return ['error' => 'ID, tipo y placa son requeridos'];
        }
        
        // Verificar que la placa no exista en otro vehículo
        $stmt = $db->prepare("SELECT id_vehiculo FROM vehiculos WHERE placa = ? AND id_vehiculo != ?");
        $stmt->bind_param("si", $datos['placa'], $datos['id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $stmt->close();
            $db->close();
            return ['error' => 'Ya existe otro vehículo con esa placa'];
        }
        $stmt->close();
        
        // Actualizar vehículo
        $stmt = $db->prepare("UPDATE vehiculos SET tipo = ?, placa = ?, estado = ?, descripcion = ? WHERE id_vehiculo = ?");
        $estado = $datos['estado'] ?? 'disponible';
        $descripcion = $datos['descripcion'] ?? null;
        $stmt->bind_param("ssssi", $datos['tipo'], $datos['placa'], $estado, $descripcion, $datos['id']);
        
        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return ['success' => true];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $db->close();
            return ['error' => 'Error al actualizar vehículo: ' . $error];
        }
    } catch (Exception $e) {
        return ['error' => 'Error al actualizar vehículo: ' . $e->getMessage()];
    }
}

// Función para cambiar estado del vehículo
function cambiarEstadoVehiculo($id, $estado) {
    try {
        $db = ConectarDB();
        
        $estados_validos = ['disponible', 'en_ruta', 'mantenimiento', 'inactivo'];
        if (!in_array($estado, $estados_validos)) {
            return ['error' => 'Estado no válido'];
        }
        
        $stmt = $db->prepare("UPDATE vehiculos SET estado = ? WHERE id_vehiculo = ?");
        $stmt->bind_param("si", $estado, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return ['success' => true];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $db->close();
            return ['error' => 'Error al cambiar estado: ' . $error];
        }
    } catch (Exception $e) {
        return ['error' => 'Error al cambiar estado: ' . $e->getMessage()];
    }
}

// Función para eliminar vehículo
function eliminarVehiculo($id) {
    try {
        $db = ConectarDB();
        
        // Verificar que no esté en uso
        $stmt = $db->prepare("SELECT COUNT(*) FROM pedidos WHERE id_vehiculo = ? AND estado IN ('pendiente', 'en_camino')");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_row()[0];
        $stmt->close();
        
        if ($count > 0) {
            $db->close();
            return ['error' => 'No se puede eliminar: el vehículo tiene pedidos activos'];
        }
        
        // Eliminar vehículo
        $stmt = $db->prepare("DELETE FROM vehiculos WHERE id_vehiculo = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return ['success' => true];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $db->close();
            return ['error' => 'Error al eliminar vehículo: ' . $error];
        }
    } catch (Exception $e) {
        return ['error' => 'Error al eliminar vehículo: ' . $e->getMessage()];
    }
}

// Endpoint para manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'disponibles':
            $resultado = obtenerVehiculosDisponibles();
            break;
            
        case 'obtener':
            $id = intval($_POST['id'] ?? 0);
            $resultado = obtenerVehiculo($id);
            break;
            
        case 'crear':
            $resultado = crearVehiculo($_POST);
            break;
            
        case 'actualizar':
            $resultado = actualizarVehiculo($_POST);
            break;
            
        case 'cambiar_estado':
            $id = intval($_POST['id'] ?? 0);
            $estado = $_POST['estado'] ?? '';
            $resultado = cambiarEstadoVehiculo($id, $estado);
            break;
            
        case 'eliminar':
            $id = intval($_POST['id'] ?? 0);
            $resultado = eliminarVehiculo($id);
            break;
            
        default:
            http_response_code(400);
            $resultado = ['error' => 'Acción no válida'];
    }
    
    if (isset($resultado['error'])) {
        http_response_code(500);
    }
    echo json_encode($resultado);
    
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>