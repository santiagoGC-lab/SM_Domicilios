<?php
require_once '../config.php';
require_once 'conexion.php';
header('Content-Type: application/json');

// Función para guardar una zona (crear o actualizar)
function guardarZona($datos)
{
    try {
        $db = ConectarDB();

        $id = $datos['id'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $barrio = $datos['barrio'] ?? '';
        $tarifa = $datos['tarifa'] ?? 0;
        $tiempo_estimado = $datos['tiempo_estimado'] ?? 15;
        $estado = $datos['estado'] ?? 'activo';

        // Validar direcciones cardinales permitidas
        $direcciones_validas = ['Norte', 'Sur', 'Oriente', 'Occidente', 'Nororiente', 'Noroccidente', 'Suroriente', 'Suroccidente'];
        
        if (empty($nombre) || !in_array($nombre, $direcciones_validas) || empty($barrio) || !is_numeric($tarifa) || !is_numeric($tiempo_estimado) || !in_array($estado, ['activo', 'inactivo'])) {
            return ['error' => 'Datos inválidos. El nombre debe ser una dirección cardinal válida.'];
        }

        if ($id) {
            // Actualizar zona existente - agregar tiempo_estimado
            $stmt = $db->prepare("UPDATE zonas SET nombre = ?, barrio = ?, tarifa_base = ?, tiempo_estimado = ?, estado = ? WHERE id_zona = ?");
            $stmt->bind_param("ssdisi", $nombre, $barrio, $tarifa, $tiempo_estimado, $estado, $id);
        } else {
            // Crear nueva zona - agregar tiempo_estimado
            $stmt = $db->prepare("INSERT INTO zonas (nombre, barrio, tarifa_base, tiempo_estimado, estado, fecha_creacion) VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssdis", $nombre, $barrio, $tarifa, $tiempo_estimado, $estado);
        }

        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return ['success' => true];
        } else {
            $stmt->close();
            $db->close();
            return ['success' => false, 'error' => $stmt->error];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
    }
}

// Función para eliminar una zona
function eliminarZona($id)
{
    try {
        $db = ConectarDB();

        if (empty($id)) {
            return ['success' => false, 'error' => 'ID no proporcionado'];
        }

        $stmt = $db->prepare("DELETE FROM zonas WHERE id_zona=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $stmt->close();
            $db->close();
            return ['success' => true];
        } else {
            $stmt->close();
            $db->close();
            return ['success' => false, 'error' => $stmt->error];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => 'Error: ' . $e->getMessage()];
    }
}

// Función para obtener todas las zonas
function obtenerZonas()
{
    try {
        $db = ConectarDB();

        $query = "SELECT * FROM zonas ORDER BY id_zona DESC";
        $resultado = $db->query($query);

        $zonas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $zonas[] = $fila;
        }

        $db->close();
        return $zonas;
    } catch (Exception $e) {
        return ['error' => 'Error al obtener zonas: ' . $e->getMessage()];
    }
}

// Función para obtener una zona por ID
function obtenerZonaPorId($id)
{
    try {
        $db = ConectarDB();

        if (empty($id) || !is_numeric($id)) {
            return ['error' => 'ID inválido'];
        }

        $stmt = $db->prepare("SELECT * FROM zonas WHERE id_zona = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $db->close();
            return ['error' => 'Zona no encontrada'];
        }

        $zona = $result->fetch_assoc();
        $stmt->close();
        $db->close();
        return $zona;
    } catch (Exception $e) {
        return ['error' => 'Error al obtener zona: ' . $e->getMessage()];
    }
}

function obtenerZonasPaginadas($pagina, $por_pagina)
{
    try {
        $db = ConectarDB();
        $offset = ($pagina - 1) * $por_pagina;
        $sql = "SELECT * FROM zonas ORDER BY id_zona DESC LIMIT ? OFFSET ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("ii", $por_pagina, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $zonas = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $total = $db->query("SELECT COUNT(*) as total FROM zonas")->fetch_assoc()['total'];
        $db->close();
        return ['zonas' => $zonas, 'total' => $total];
    } catch (Exception $e) {
        return ['error' => 'Error al obtener zonas: ' . $e->getMessage()];
    }
}

// Buscar zona por barrio (insensible a mayúsculas y tildes, compatible con cualquier collation)
function buscarZonaPorBarrio($barrio)
{
    try {
        $db = ConectarDB();
        $stmt = $db->prepare("SELECT id_zona, nombre, tarifa_base FROM zonas WHERE LOWER(CONVERT(barrio USING utf8)) = LOWER(CONVERT(? USING utf8)) AND estado = 'activo' LIMIT 1");
        $stmt->bind_param("s", $barrio);
        $stmt->execute();
        $result = $stmt->get_result();
        $zona = $result->fetch_assoc();
        $stmt->close();
        $db->close();
        if ($zona) {
            return $zona;
        } else {
            return ['error' => 'Zona no encontrada para ese barrio'];
        }
    } catch (Exception $e) {
        return ['error' => 'Error al buscar zona: ' . $e->getMessage()];
    }
}

// Endpoint para manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'guardar':
            $resultado = guardarZona($_POST);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'eliminar':
            $resultado = eliminarZona($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'obtener':
            $resultado = obtenerZonas();
            if (isset($resultado['error'])) {
                http_response_code(500);
            }
            echo json_encode($resultado);
            break;

        case 'obtener_por_id':
            $resultado = obtenerZonaPorId($_POST['id']);
            if (isset($resultado['error'])) {
                http_response_code(400);
            }
            echo json_encode($resultado);
            break;

        case 'paginar':
            $pagina = isset($_POST['pagina']) ? intval($_POST['pagina']) : 1;
            $por_pagina = isset($_POST['por_pagina']) ? intval($_POST['por_pagina']) : 10;
            $resultado = obtenerZonasPaginadas($pagina, $por_pagina);
            echo json_encode($resultado);
            break;

        case 'buscar_por_barrio':
            $resultado = buscarZonaPorBarrio($_POST['barrio'] ?? '');
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
        $resultado = obtenerZonaPorId($_GET['id']);
        if (isset($resultado['error'])) {
            http_response_code(400);
        }
        echo json_encode($resultado);
    } else {
        $resultado = obtenerZonas();
        if (isset($resultado['error'])) {
            http_response_code(500);
        }
        echo json_encode($resultado);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
