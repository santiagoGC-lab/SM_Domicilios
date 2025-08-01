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

// Función para procesar un nuevo pedido
function procesarPedido($datos)
{
    try {
        $db = ConectarDB();

        // Validar datos requeridos
        $required_fields = ['id_cliente', 'id_zona', 'estado', 'bolsas', 'total'];
        foreach ($required_fields as $field) {
            if (!isset($datos[$field]) || empty($datos[$field])) {
                return ['error' => "Campo requerido: $field"];
            }
        }

        $id_cliente = intval($datos['id_cliente']);
        $id_zona = intval($datos['id_zona']);
        $id_domiciliario = isset($datos['id_domiciliario']) && $datos['id_domiciliario'] !== '' ? intval($datos['id_domiciliario']) : null;
        $estado = $datos['estado'];
        $cantidad_paquetes = intval($datos['bolsas']);
        $total = floatval($datos['total']);
        $tiempo_estimado = intval($datos['tiempo_estimado'] ?? 30);

        // Validar que el cliente existe
        $stmt = $db->prepare("SELECT id_cliente FROM clientes WHERE id_cliente = ? AND estado = 'activo'");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            $stmt->close();
            $db->close();
            return ['error' => 'Cliente no válido'];
        }
        $stmt->close();

        // Validar que la zona existe
        $stmt = $db->prepare("SELECT id_zona FROM zonas WHERE id_zona = ? AND estado = 'activo'");
        $stmt->bind_param("i", $id_zona);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            $stmt->close();
            $db->close();
            return ['error' => 'Zona no válida'];
        }
        $stmt->close();

        // Validar que el domiciliario existe y está disponible
        if ($id_domiciliario !== null) {
            $stmt = $db->prepare("SELECT id_domiciliario FROM domiciliarios WHERE id_domiciliario = ? AND estado = 'disponible'");
            $stmt->bind_param("i", $id_domiciliario);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) {
                $stmt->close();
                $db->close();
                return ['error' => 'Domiciliario no disponible'];
            }
            $stmt->close();
        }

        // Procesar checkboxes
        $envio_inmediato = isset($datos['envio_inmediato']) && $datos['envio_inmediato'] == '1' ? 1 : 0;
        $alistamiento = isset($datos['alistamiento']) && $datos['alistamiento'] == '1' ? 1 : 0;

        // Insertar el pedido
        $stmt = $db->prepare("
            INSERT INTO pedidos (id_cliente, id_zona, id_domiciliario, estado, cantidad_paquetes, total, tiempo_estimado, envio_inmediato, alistamiento, fecha_pedido)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiissdiis", $id_cliente, $id_zona, $id_domiciliario, $estado, $cantidad_paquetes, $total, $tiempo_estimado, $envio_inmediato, $alistamiento);
        $stmt->execute();

        // Actualizar estado del domiciliario según el estado del pedido
        if ($estado === 'entregado' || $estado === 'cancelado') {
            $stmt2 = $db->prepare("UPDATE domiciliarios SET estado = 'disponible' WHERE id_domiciliario = ?");
            $stmt2->bind_param("i", $id_domiciliario);
            $stmt2->execute();
            $stmt2->close();
        }

        $stmt->close();
        $db->close();

        return ['success' => true, 'message' => 'Pedido creado exitosamente'];
    } catch (Exception $e) {
        return ['error' => 'Error al procesar pedido: ' . $e->getMessage()];
    }
}

// Función para actualizar un pedido
function actualizarPedido($datos)
{
    try {
        $db = ConectarDB();

        $id_pedido = intval($datos['id_pedido']);
        $estado = $datos['estado'];
        $id_domiciliario = intval($datos['id_domiciliario'] ?? 0);

        // Procesar checkboxes
        $envio_inmediato = isset($datos['envio_inmediato']) && $datos['envio_inmediato'] == '1' ? 1 : 0;
        $alistamiento = isset($datos['alistamiento']) && $datos['alistamiento'] == '1' ? 1 : 0;

        // Actualizar el pedido
        $stmt = $db->prepare("UPDATE pedidos SET estado = ?, id_domiciliario = ?, envio_inmediato = ?, alistamiento = ? WHERE id_pedido = ?");
        $stmt->bind_param("siiis", $estado, $id_domiciliario, $envio_inmediato, $alistamiento, $id_pedido);
        $stmt->execute();

        // Actualizar estado del domiciliario
        if ($estado === 'entregado' || $estado === 'cancelado') {
            $stmt2 = $db->prepare("UPDATE domiciliarios SET estado = 'disponible' WHERE id_domiciliario = ?");
            $stmt2->bind_param("i", $id_domiciliario);
            $stmt2->execute();
            $stmt2->close();
        }

        $stmt->close();
        $db->close();

        return ['success' => true, 'message' => 'Pedido actualizado exitosamente'];
    } catch (Exception $e) {
        return ['error' => 'Error al actualizar pedido: ' . $e->getMessage()];
    }
}

// Función para cambiar el estado de un pedido
function cambiarEstadoPedido($id, $estado)
{
    try {
        $db = ConectarDB();

        // Si el estado es 'entregado', mover al histórico automáticamente
        if ($estado === 'entregado') {
            $resultado = moverPedidoHistorial($id);
            if (isset($resultado['error'])) {
                return $resultado;
            }
            return ['success' => true, 'message' => 'Pedido entregado y movido al histórico'];
        }

        // Para otros estados, solo actualizar
        $stmt = $db->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->bind_param("si", $estado, $id);
        $stmt->execute();

        $stmt->close();
        $db->close();

        return ['success' => true, 'message' => 'Estado del pedido cambiado a: ' . $estado];
    } catch (Exception $e) {
        return ['error' => 'Error al cambiar estado: ' . $e->getMessage()];
    }
}

// Función para eliminar un pedido
function eliminarPedido($id)
{
    try {
        $db = ConectarDB();

        $stmt = $db->prepare("DELETE FROM pedidos WHERE id_pedido = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt->close();
        $db->close();

        return ['success' => true, 'message' => 'Pedido eliminado exitosamente'];
    } catch (Exception $e) {
        return ['error' => 'Error al eliminar pedido: ' . $e->getMessage()];
    }
}

// Función para obtener un pedido
function obtenerPedido($id)
{
    try {
        $db = ConectarDB();

        if (!isset($id) || empty($id)) {
            return ['error' => 'ID de pedido requerido'];
        }

        $id_pedido = intval($id);

        $stmt = $db->prepare("
            SELECT p.*, c.documento, c.nombre AS nombre_cliente
            FROM pedidos p
            LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
            WHERE p.id_pedido = ?
        ");
        $stmt->bind_param("i", $id_pedido);
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
        return ['error' => 'Error al obtener pedido: ' . $e->getMessage()];
    }
}

// Función para buscar pedidos
function buscarPedido($criterios)
{
    try {
        $db = ConectarDB();

        $query = "SELECT p.*, c.nombre as cliente, d.nombre as domiciliario, z.nombre as zona 
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                  LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario 
                  LEFT JOIN zonas z ON p.id_zona = z.id_zona 
                  WHERE 1=1";

        $params = [];
        $types = "";

        if (!empty($criterios['estado'])) {
            $query .= " AND p.estado = ?";
            $params[] = $criterios['estado'];
            $types .= "s";
        }

        if (!empty($criterios['fecha'])) {
            $query .= " AND DATE(p.fecha_pedido) = ?";
            $params[] = $criterios['fecha'];
            $types .= "s";
        }

        $query .= " ORDER BY p.fecha_pedido DESC";

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
        return ['error' => 'Error al buscar pedidos: ' . $e->getMessage()];
    }
}

// Función para buscar historial de pedidos
function buscarHistorialPedidos($filtros)
{
    try {
        $db = ConectarDB();

        $query = "SELECT p.*, c.nombre as cliente, d.nombre as domiciliario, z.nombre as zona 
                  FROM pedidos p 
                  LEFT JOIN clientes c ON p.id_cliente = c.id_cliente 
                  LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario 
                  LEFT JOIN zonas z ON p.id_zona = z.id_zona 
                  WHERE p.estado IN ('entregado', 'cancelado')";

        $params = [];
        $types = "";

        if (!empty($filtros['fecha_inicio'])) {
            $query .= " AND DATE(p.fecha_pedido) >= ?";
            $params[] = $filtros['fecha_inicio'];
            $types .= "s";
        }

        if (!empty($filtros['fecha_fin'])) {
            $query .= " AND DATE(p.fecha_pedido) <= ?";
            $params[] = $filtros['fecha_fin'];
            $types .= "s";
        }

        $query .= " ORDER BY p.fecha_pedido DESC";

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
        return ['error' => 'Error al buscar historial: ' . $e->getMessage()];
    }
}

// Función para mover pedido a historial
function moverPedidoHistorial($id)
{
    try {
        $db = ConectarDB();

        // 1. Obtener todos los datos del pedido y sus relaciones
        $stmt = $db->prepare("
            SELECT p.*, c.nombre AS cliente_nombre, c.documento AS cliente_documento, c.telefono AS cliente_telefono, c.direccion AS cliente_direccion,
                   z.nombre AS zona_nombre, z.tarifa_base AS zona_tarifa,
                   d.nombre AS domiciliario_nombre, d.telefono AS domiciliario_telefono,
                   v.tipo AS vehiculo_tipo, v.placa AS vehiculo_placa
            FROM pedidos p
            LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
            LEFT JOIN zonas z ON p.id_zona = z.id_zona
            LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
            LEFT JOIN vehiculos v ON p.id_vehiculo = v.id_vehiculo
            WHERE p.id_pedido = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pedido = $result->fetch_assoc();
        $stmt->close();

        if (!$pedido) {
            $db->close();
            return ['error' => 'Pedido no encontrado'];
        }

        // 2. Escapar valores para evitar inyección SQL
        $id_pedido_original = intval($pedido['id_pedido']);
        $id_cliente = intval($pedido['id_cliente']);
        $id_zona = intval($pedido['id_zona']);
        $id_domiciliario = $pedido['id_domiciliario'] ? intval($pedido['id_domiciliario']) : 'NULL';
        $id_vehiculo = $pedido['id_vehiculo'] ? intval($pedido['id_vehiculo']) : 'NULL';
        $estado = $db->real_escape_string('entregado'); // Forzar a entregado
        $cantidad_paquetes = intval($pedido['cantidad_paquetes']);
        $total = floatval($pedido['total']);
        $tiempo_estimado = intval($pedido['tiempo_estimado']);
        $fecha_pedido = "'" . $db->real_escape_string($pedido['fecha_pedido']) . "'";
        $hora_salida = $pedido['hora_salida'] ? "'" . $db->real_escape_string($pedido['hora_salida']) . "'" : 'NULL';
        $hora_llegada = $pedido['hora_llegada'] ? "'" . $db->real_escape_string($pedido['hora_llegada']) . "'" : 'NOW()';
        $cliente_nombre = "'" . $db->real_escape_string($pedido['cliente_nombre']) . "'";
        $cliente_documento = "'" . $db->real_escape_string($pedido['cliente_documento']) . "'";
        $cliente_telefono = $pedido['cliente_telefono'] ? "'" . $db->real_escape_string($pedido['cliente_telefono']) . "'" : 'NULL';
        $cliente_direccion = $pedido['cliente_direccion'] ? "'" . $db->real_escape_string($pedido['cliente_direccion']) . "'" : 'NULL';
        $zona_nombre = "'" . $db->real_escape_string($pedido['zona_nombre']) . "'";
        $zona_tarifa = floatval($pedido['zona_tarifa']);
        $domiciliario_nombre = $pedido['domiciliario_nombre'] ? "'" . $db->real_escape_string($pedido['domiciliario_nombre']) . "'" : 'NULL';
        $domiciliario_telefono = $pedido['domiciliario_telefono'] ? "'" . $db->real_escape_string($pedido['domiciliario_telefono']) . "'" : 'NULL';
        $vehiculo_tipo = $pedido['vehiculo_tipo'] ? "'" . $db->real_escape_string($pedido['vehiculo_tipo']) . "'" : 'NULL';
        $vehiculo_placa = $pedido['vehiculo_placa'] ? "'" . $db->real_escape_string($pedido['vehiculo_placa']) . "'" : 'NULL';
        $usuario_proceso = isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 'NULL';

        // 3. Insertar con consulta SQL directa
        $sql = "INSERT INTO historico_pedidos (
            id_pedido_original, id_cliente, id_zona, id_domiciliario, id_vehiculo, estado, 
            cantidad_paquetes, total, tiempo_estimado, fecha_pedido, hora_salida, hora_llegada, 
            fecha_completado, cliente_nombre, cliente_documento, cliente_telefono, cliente_direccion, 
            zona_nombre, zona_tarifa, domiciliario_nombre, domiciliario_telefono, vehiculo_tipo, 
            vehiculo_placa, usuario_proceso
        ) VALUES (
            $id_pedido_original, $id_cliente, $id_zona, $id_domiciliario, $id_vehiculo, '$estado',
            $cantidad_paquetes, $total, $tiempo_estimado, $fecha_pedido, $hora_salida, $hora_llegada,
            NOW(), $cliente_nombre, $cliente_documento, $cliente_telefono, $cliente_direccion,
            $zona_nombre, $zona_tarifa, $domiciliario_nombre, $domiciliario_telefono, $vehiculo_tipo,
            $vehiculo_placa, $usuario_proceso
        )";

        if (!$db->query($sql)) {
            $db->close();
            return ['error' => 'Error al insertar en histórico: ' . $db->error];
        }

        // 4. Eliminar el pedido de la tabla principal
        $sql_delete = "DELETE FROM pedidos WHERE id_pedido = $id_pedido_original";
        if (!$db->query($sql_delete)) {
            $db->close();
            return ['error' => 'Error al eliminar pedido: ' . $db->error];
        }

        $db->close();
        return ['success' => true, 'message' => 'Pedido archivado correctamente'];
    } catch (Exception $e) {
        return ['error' => 'Error al mover pedido: ' . $e->getMessage()];
    }
}

// Función para archivar pedidos automáticamente
function archivarPedidosAutomatico()
{
    try {
        $db = ConectarDB();

        // Mover pedidos antiguos (más de 30 días) a historial
        $stmt = $db->prepare("
            UPDATE pedidos 
            SET estado = 'entregado' 
            WHERE estado = 'pendiente' 
            AND fecha_pedido < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();

        $stmt->close();
        $db->close();

        return ['success' => true, 'message' => 'Pedidos archivados automáticamente'];
    } catch (Exception $e) {
        return ['error' => 'Error al archivar pedidos: ' . $e->getMessage()];
    }
}

// Obtener pedidos pendientes de despacho
function obtenerPedidosPendientesDespacho()
{
    try {
        $db = ConectarDB();
        $query = "SELECT p.id_pedido, c.nombre as cliente, c.direccion, c.telefono, c.barrio, 
                         z.nombre as zona, p.cantidad_paquetes, p.tiempo_estimado
                  FROM pedidos p
                  LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                  LEFT JOIN zonas z ON p.id_zona = z.id_zona
                  WHERE p.estado = 'pendiente' AND (p.id_domiciliario IS NULL OR p.id_domiciliario = 0)
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

function despacharPedido($id_pedido, $id_domiciliario, $id_vehiculo)
{
    try {
        $db = ConectarDB();
        // Actualizar pedido
        $stmt = $db->prepare("UPDATE pedidos SET id_domiciliario = ?, id_vehiculo = ?, hora_salida = NOW(), estado = 'en_camino' WHERE id_pedido = ?");
        $stmt->bind_param("iii", $id_domiciliario, $id_vehiculo, $id_pedido);
        $stmt->execute();
        $stmt->close();
        // Marcar domiciliario como ocupado
        $stmt2 = $db->prepare("UPDATE domiciliarios SET estado = 'ocupado' WHERE id_domiciliario = ?");
        $stmt2->bind_param("i", $id_domiciliario);
        $stmt2->execute();
        $stmt2->close();
        // Marcar vehículo como en_ruta
        $stmt3 = $db->prepare("UPDATE vehiculos SET estado = 'en_ruta' WHERE id_vehiculo = ?");
        $stmt3->bind_param("i", $id_vehiculo);
        $stmt3->execute();
        $stmt3->close();
        $db->close();
        return ['success' => true];
    } catch (Exception $e) {
        return ['error' => 'Error al despachar pedido: ' . $e->getMessage()];
    }
}

function marcarLlegadaPedido($id_pedido)
{
    try {
        $db = ConectarDB();

        // Verificar si el pedido existe antes de proceder
        $stmt_check = $db->prepare("SELECT id_pedido FROM pedidos WHERE id_pedido = ?");
        $stmt_check->bind_param("i", $id_pedido);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if (!$result_check->fetch_assoc()) {
            $stmt_check->close();
            $db->close();
            return ['error' => 'El pedido ya fue procesado o no existe'];
        }
        $stmt_check->close();

        // Actualizar el estado del pedido a 'entregado' y marcar hora de llegada
        $stmt = $db->prepare("UPDATE pedidos SET estado = 'entregado', hora_llegada = NOW() WHERE id_pedido = ?");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $stmt->close();

        // Obtener domiciliario y vehículo del pedido
        $stmt = $db->prepare("SELECT id_domiciliario, id_vehiculo FROM pedidos WHERE id_pedido = ?");
        $stmt->bind_param("i", $id_pedido);
        $stmt->execute();
        $stmt->bind_result($id_domiciliario, $id_vehiculo);
        $stmt->fetch();
        $stmt->close();

        // Marcar domiciliario como disponible
        if ($id_domiciliario) {
            $stmt3 = $db->prepare("UPDATE domiciliarios SET estado = 'disponible' WHERE id_domiciliario = ?");
            $stmt3->bind_param("i", $id_domiciliario);
            $stmt3->execute();
            $stmt3->close();
        }
        // Marcar vehículo como disponible
        if ($id_vehiculo) {
            $stmt4 = $db->prepare("UPDATE vehiculos SET estado = 'disponible' WHERE id_vehiculo = ?");
            $stmt4->bind_param("i", $id_vehiculo);
            $stmt4->execute();
            $stmt4->close();
        }

        $db->close();

        // Mover al histórico automáticamente (ahora con estado 'entregado')
        $resultado = moverPedidoHistorial($id_pedido);
        if (isset($resultado['error'])) {
            return $resultado;
        }

        return ['success' => true, 'message' => 'Pedido entregado y movido al histórico'];
    } catch (Exception $e) {
        return ['error' => 'Error al marcar llegada: ' . $e->getMessage()];
    }
}

// Endpoint para manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'procesar':
            $resultado = procesarPedido($_POST);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'actualizar':
            $resultado = actualizarPedido($_POST);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'cambiar_estado':
            $resultado = cambiarEstadoPedido($_POST['id'], $_POST['estado']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'eliminar':
            $resultado = eliminarPedido($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'obtener':
            $resultado = obtenerPedido($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'buscar':
            $resultado = buscarPedido($_POST);
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;

        case 'buscar_historial':
            $resultado = buscarHistorialPedidos($_POST);
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;

        case 'mover_historial':
            $resultado = moverPedidoHistorial($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'archivar_automatico':
            $resultado = archivarPedidosAutomatico();
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;

        case 'pendientes_despacho':
            $resultado = obtenerPedidosPendientesDespacho();
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;

        case 'despachar':
            $id_pedido = intval($_POST['id_pedido']);
            $id_domiciliario = intval($_POST['id_domiciliario']);
            $id_vehiculo = intval($_POST['id_vehiculo']);
            $resultado = despacharPedido($id_pedido, $id_domiciliario, $id_vehiculo);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'marcar_llegada':
            $id_pedido = intval($_POST['id_pedido']);
            $resultado = marcarLlegadaPedido($id_pedido);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;
        // En el case 'en_ruta':
        case 'en_ruta':
            $db = ConectarDB();
            $result = $db->query("SELECT p.id_pedido, d.nombre AS domiciliario, p.hora_salida, p.hora_llegada, 
                                     c.nombre AS cliente, c.direccion, c.telefono
                                  FROM pedidos p 
                                  LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario 
                                  LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                                  WHERE p.estado = 'en_camino'");
            $pedidos = $result->fetch_all(MYSQLI_ASSOC);
            $db->close();
            echo json_encode($pedidos);
            break;

        case 'paginar':
            $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
            $por_pagina = isset($_POST['por_pagina']) ? intval($_POST['por_pagina']) : 5;
            $offset = ($pagina - 1) * $por_pagina;
            $db = ConectarDB();
            // Contar total de pedidos activos
            $resTotal = $db->query("SELECT COUNT(*) as total FROM pedidos WHERE movido_historico = 0");
            $total = $resTotal->fetch_assoc()['total'];
            // Obtener pedidos paginados
            $stmt = $db->prepare("
                SELECT p.id_pedido, c.nombre AS cliente, c.documento, d.nombre AS domiciliario, p.estado, p.fecha_pedido, p.id_cliente, p.id_domiciliario, p.id_zona, p.cantidad_paquetes, p.total, p.tiempo_estimado
                FROM pedidos p
                LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
                LEFT JOIN domiciliarios d ON p.id_domiciliario = d.id_domiciliario
                WHERE p.movido_historico = 0
                ORDER BY p.fecha_pedido DESC
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ii", $por_pagina, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            $pedidos = [];
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = $row;
            }
            $stmt->close();
            $db->close();
            echo json_encode([
                'pedidos' => $pedidos,
                'total' => $total
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Para compatibilidad con el código existente
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'obtener') {
        $resultado = obtenerPedido($_GET['id']);
        if (isset($resultado['error'])) {
            http_response_code(400);
        }
        echo json_encode($resultado);
    } elseif ($accion === 'buscar') {
        $resultado = buscarPedido($_GET);
        if (isset($resultado['error'])) {
            http_response_code(500);
        }
        echo json_encode($resultado);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
