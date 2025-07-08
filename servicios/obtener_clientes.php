<?php
require_once 'conexion.php';
header('Content-Type: application/json');

try {
    $db = ConectarDB();

    $query = "SELECT id_cliente, nombre, documento, telefono, direccion, barrio, tipo_cliente FROM clientes ORDER BY id_cliente DESC";
    $result = $db->query($query);

    $clientes = [];
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }

    echo json_encode($clientes);
    
    $db->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener clientes', 'detalle' => $e->getMessage()]);
}
