<?php
session_start();
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = $_POST['cedula'];
    $password = $_POST['password'];
    
    // Prepare SQL statement to prevent SQL injection
    $sql = "SELECT * FROM usuarios WHERE cedula = ? AND password = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $cedula, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        // User found - create session
        $usuario = $result->fetch_assoc();
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['cedula'] = $usuario['cedula'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];
        
        // Return success response
        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "redirect" => "../dashboard.php"
        ]);
    } else {
        // Invalid credentials
        echo json_encode([
            "status" => "error",
            "message" => "Invalid cedula or password"
        ]);
    }
    
    $stmt->close();
} else {
    // Invalid request method
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}

$conexion->close();
?>
