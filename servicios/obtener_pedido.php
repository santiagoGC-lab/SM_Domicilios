<?php
// Desactivar la visualización de errores para evitar que corrompan el JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Establecer el encabezado JSON
header('Content-Type: application/json');

try {
    // Conexión a la base de datos
    $pdo = new PDO("mysql:host=localhost;dbname=sm_domicilios", "root", "root");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener el ID del pedido
    $id = $_GET['id'] ?? null;
    if (!$id || !is_numeric($id)) {
        throw new Exception("ID de pedido no proporcionado o inválido");
    }

    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare("
        SELECT p.id_pedido, p.id_cliente, c.documento, p.id_domiciliario, p.id_zona, p.estado, p.cantidad_paquetes, p.total
        FROM pedidos p
        LEFT JOIN clientes c ON p.id_cliente = c.id_cliente
        WHERE p.id_pedido = ?
    ");
    $stmt->execute([$id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$pedido) {
        throw new Exception("Pedido no encontrado");
    }

    // Devolver la respuesta JSON
    echo json_encode($pedido);
} catch (Exception $e) {
    // Registrar el error en el log
    error_log("Error en obtener_pedido.php: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>