<?php
$host = "localhost";
$user = "root";
$pass = "root"; // En MAMP suele ser 'root'
$db = "nombre_de_tu_base"; // Cambia esto por el nombre real

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}
