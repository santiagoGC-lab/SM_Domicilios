<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
require_once 'conexion.php';
session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Función para obtener pedidos pendientes de despacho
function obtenerPedidosPendientesDespacho()
{
    try {
        $db = ConectarDB();
        $query = "SELECT p.id_pedido, c.nombre AS cliente, c.barrio, z.nombre AS zona
                  FROM pedidos p
                  LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                  LEFT JOIN zonas z ON p.id_zona = z.id_zona
                  WHERE p.estado = 'pendiente' 
                  AND (p.id_domiciliario IS NULL OR p.id_domiciliario = 0)
                  AND (p.id_vehiculo IS NULL OR p.id_vehiculo = '')
                  AND (p.hora_salida IS NULL OR p.hora_salida = '')
                  ORDER BY p.fecha_pedido ASC";
        $result = $db->query($query);
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        $db->close();
        return $pedidos;
    } catch (Exception $e) {
        return ['error' => 'Error al obtener pedidos pendientes: ' . $e->getMessage()];
    }
}

// Función para despachar un pedido
function despacharPedido($id_pedido, $id_domiciliario, $id_vehiculo)
{
    try {
        $db = ConectarDB();
        // Validar que el domiciliario está disponible
        $stmt = $db->prepare("SELECT id_domiciliario FROM domiciliarios WHERE id_domiciliario = ? AND estado = 'disponible'");
        $stmt->bind_param("i", $id_domiciliario);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            $stmt->close();
            $db->close();
            return ['error' => 'Domiciliario no disponible'];
        }
        $stmt->close();

        // Validar que el vehículo está disponible
        $stmt = $db->prepare("SELECT id_vehiculo FROM vehiculos WHERE id_vehiculo = ? AND estado = 'disponible'");
        $stmt->bind_param("i", $id_vehiculo);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            $stmt->close();
            $db->close();
            return ['error' => 'Vehículo no disponible'];
        }
        $stmt->close();

        // Actualizar el pedido
        $stmt = $db->prepare("UPDATE pedidos SET id_domiciliario = ?, id_vehiculo = ?, estado = 'en_camino', hora_salida = NOW() WHERE id_pedido = ? AND estado = 'pendiente'");
        $stmt->bind_param("iii", $id_domiciliario, $id_vehiculo, $id_pedido);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            $db->close();
            return ['error' => 'No se pudo despachar el pedido o el pedido no está pendiente'];
        }
        $stmt->close();

        // Actualizar estado del domiciliario
        $stmt = $db->prepare("UPDATE domiciliarios SET estado = 'ocupado' WHERE id_domiciliario = ?");
        $stmt->bind_param("i", $id_domiciliario);
        $stmt->execute();
        $stmt->close();

        // Actualizar estado del vehículo
        $stmt = $db->prepare("UPDATE vehiculos SET estado = 'en_ruta' WHERE id_vehiculo = ?");
        $stmt->bind_param("i", $id_vehiculo);
        $stmt->execute();
        $stmt->close();

        $db->close();
        return ['success' => true, 'message' => 'Pedido despachado exitosamente'];
    } catch (Exception $e) {
        return ['error' => 'Error al despachar el pedido: ' . $e->getMessage()];
    }
}

// Función para marcar la llegada de un pedido
function marcarLlegadaPedido($id_pedido)
{
    try {
        $db = ConectarDB();
        // Obtener id_domiciliario e id_vehiculo del pedido
        $stmt = $db->prepare("SELECT id_domiciliario, id_vehiculo FROM pedidos WHERE id_pedido = ? AND estado = 'en_camino'");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $result = $stmt->get_result();
        if (!$row = $result->fetch_assoc()) {
            $stmt->close();
            $db->close();
            return ['error' => 'El pedido no está en camino o no existe'];
        }
        $id_domiciliario = $row['id_domiciliario'];
        $id_vehiculo = $row['id_vehiculo'];
        $stmt->close();

        // Actualizar el pedido
        $stmt = $db->prepare("UPDATE pedidos SET estado = 'entregado', hora_llegada = NOW() WHERE id_pedido = ?");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            $db->close();
            return ['error' => 'No se pudo marcar la llegada del pedido'];
        }
        $stmt->close();

        // Actualizar estado del domiciliario
        $stmt = $db->prepare("UPDATE domiciliarios SET estado = 'disponible' WHERE id_domiciliario = ?");
        $stmt->bind_param("i", $id_domiciliario);
        $stmt->execute();
        $stmt->close();

        // Actualizar estado del vehículo
        $stmt = $db->prepare("UPDATE vehiculos SET estado = 'disponible' WHERE id_vehiculo = ?");
        $stmt->bind_param("i", $id_vehiculo);
        $stmt->execute();
        $stmt->close();

        $db->close();
        return ['success' => true, 'message' => 'Llegada marcada exitosamente'];
    } catch (Exception $e) {
        return ['error' => 'Error al marcar la llegada: ' . $e->getMessage()];
    }
}

// Función para obtener pedidos en ruta
function obtenerPedidosEnRuta()
{
    try {
        $db = ConectarDB();
        $query = "SELECT p.id_pedido, d.nombre AS domiciliario, p.hora_salida, p.hora_llegada
                  FROM pedidos p
                  LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
                  WHERE p.estado = 'en_camino'
                  ORDER BY p.hora_salida ASC";
        $result = $db->query($query);
        $pedidos = [];
        while ($row = $result->fetch_assoc()) {
            $pedidos[] = $row;
        }
        $db->close();
        return $pedidos;
    } catch (Exception $e) {
        return ['error' => 'Error al obtener pedidos en ruta: ' . $e->getMessage()];
    }
}

// Manejo de solicitudes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'pendientes_despacho':
            $resultado = obtenerPedidosPendientesDespacho();
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;

        case 'despachar':
            $id_pedido = $_POST['id_pedido'] ?? 0;
            $id_domiciliario = $_POST['id_domiciliario'] ?? 0;
            $id_vehiculo = $_POST['id_vehiculo'] ?? 0;
            if (!$id_pedido || !$id_domiciliario || !$id_vehiculo) {
                http_response_code(400);
                echo json_encode(['error' => 'Faltan parámetros']);
                exit;
            }
            $resultado = despacharPedido($id_pedido, $id_domiciliario, $id_vehiculo);
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;

        case 'marcar_llegada':
            $id_pedido = $_POST['id_pedido'] ?? 0;
            if (!$id_pedido) {
                http_response_code(400);
                echo json_encode(['error' => 'Falta el ID del pedido']);
                exit;
            }
            $resultado = marcarLlegadaPedido($id_pedido);
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;

        case 'en_ruta':
            $resultado = obtenerPedidosEnRuta();
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
