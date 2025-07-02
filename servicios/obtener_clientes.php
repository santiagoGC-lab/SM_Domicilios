<?php
// Activar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Encabezado JSON
header('Content-Type: application/json');

// Conexión a la base de datos
$host = "localhost";
$user = "root";
$pass = "root"; // En MAMP normalmente es 'root'
$db = "sm_domicilios"; // Cambia esto por el nombre real de tu base de datos

$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Consulta SQL
$sql = "SELECT * FROM clientes ORDER BY id DESC";
$result = $conn->query($sql);

$clientes = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
    echo json_encode($clientes);
} else {
    echo json_encode(['error' => 'Error en la consulta']);
}

$conn->close();
