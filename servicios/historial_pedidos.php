<?php
require_once '../config.php';
require_once 'conexion.php';
session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Función para obtener historial de pedidos (sin paginación para frontend)
function obtenerHistorialPedidos($filtros = []) {
    try {
        $db = ConectarDB();
        
        // Construir consulta base
        $query = "SELECT hp.* FROM historico_pedidos hp WHERE 1=1";
        $params = [];
        $types = "";
        
        // Aplicar filtros
        if (!empty($filtros['fecha_inicio'])) {
            $query .= " AND DATE(hp.fecha_pedido) >= ?";
            $params[] = $filtros['fecha_inicio'];
            $types .= "s";
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $query .= " AND DATE(hp.fecha_pedido) <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= "s";
        }
        
        if (!empty($filtros['estado'])) {
            $query .= " AND hp.estado = ?";
            $params[] = $filtros['estado'];
            $types .= "s";
        }
        
        // Consulta completa sin paginación
        $query .= " ORDER BY hp.fecha_completado DESC";
        
        $stmt = $db->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        
        $stmt->close();
        $db->close();
        
        return $pedidos;

    } catch (Exception $e) {
        return ['error' => 'Error al obtener historial: ' . $e->getMessage()];
    }
}

// Función para obtener detalles de un pedido del historial
function obtenerDetalleHistorial($id_historico) {
    try {
        $db = ConectarDB();
        
        $stmt = $db->prepare("
            SELECT hp.*, c.documento as cliente_documento, c.telefono as cliente_telefono, c.direccion as cliente_direccion 
            FROM historico_pedidos hp 
            LEFT JOIN clientes c ON hp.cliente_nombre = c.nombre 
            WHERE hp.id_historico = ?
        ");
        $stmt->bind_param("i", $id_historico);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();
        
        if (!$pedido) {
            $stmt->close();
            $db->close();
            return ['error' => 'Pedido no encontrado'];
        }
        
        $stmt->close();
        $db->close();
        
        return $pedido;

    } catch (Exception $e) {
        return ['error' => 'Error al obtener detalle: ' . $e->getMessage()];
    }
}

// Función para buscar en el historial
function buscarHistorial($query) {
    try {
        $db = ConectarDB();
        
        $searchTerm = '%' . $query . '%';
        
        $stmt = $db->prepare("
            SELECT hp.id_historico, hp.id_pedido_original, hp.cliente_nombre, hp.domiciliario_nombre, 
                   hp.estado, hp.fecha_pedido, hp.fecha_completado, hp.total, hp.zona_nombre
            FROM historico_pedidos hp 
            WHERE hp.cliente_nombre LIKE ? 
               OR hp.domiciliario_nombre LIKE ? 
               OR hp.zona_nombre LIKE ? 
               OR hp.id_pedido_original LIKE ?
               OR hp.estado LIKE ?
            ORDER BY hp.fecha_completado DESC 
            LIMIT 50
        ");
        $stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        
        $stmt->close();
        $db->close();
        
        return $pedidos;

    } catch (Exception $e) {
        return ['error' => 'Error en la búsqueda: ' . $e->getMessage()];
    }
}

// Endpoint para manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'obtener':
            $filtros = [
                'fecha_inicio' => $_POST['fecha_inicio'] ?? '',
                'fecha_fin' => $_POST['fecha_fin'] ?? '',
                'estado' => $_POST['estado'] ?? ''
            ];
            
            $resultado = obtenerHistorialPedidos($filtros);
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;
            
        case 'detalle':
            $resultado = obtenerDetalleHistorial($_POST['id_historico']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'buscar':
            $resultado = buscarHistorial($_POST['query']);
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
    $accion = $_GET['accion'] ?? '';
    
    switch ($accion) {
        case 'obtener':
            $filtros = [
                'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
                'fecha_fin' => $_GET['fecha_fin'] ?? '',
                'estado' => $_GET['estado'] ?? ''
            ];
            
            $resultado = obtenerHistorialPedidos($filtros);
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;
            
        case 'detalle':
            $resultado = obtenerDetalleHistorial($_GET['id_historico']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
            
        case 'buscar':
            $resultado = buscarHistorial($_GET['query']);
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?> 