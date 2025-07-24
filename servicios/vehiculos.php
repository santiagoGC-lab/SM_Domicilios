<?php
require_once '../config.php';
require_once 'conexion.php';
header('Content-Type: application/json');

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

// Endpoint para manejar las peticiones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    switch ($accion) {
        case 'disponibles':
            $resultado = obtenerVehiculosDisponibles();
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