<?php
require_once 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroDocumento = filter_input(INPUT_POST, 'numeroDocumento', FILTER_SANITIZE_STRING);
    $contrasena = $_POST['contrasena'];
    
    $conexion = conectarDB();
    
    // Preparar la consulta
    $stmt = $conexion->prepare("SELECT id, numero_documento, contrasena, nombre FROM usuarios WHERE numero_documento = ?");
    $stmt->bind_param("s", $numeroDocumento);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        
        // Verificar la contraseña
        if (password_verify($contrasena, $usuario['contrasena'])) {
            // Login exitoso
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            
            // Si marcó "recordarme"
            if (isset($_POST['recuerdame'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                
                // Guardar token en la base de datos
                $stmt = $conexion->prepare("UPDATE usuarios SET remember_token = ? WHERE id = ?");
                $stmt->bind_param("si", $token, $usuario['id']);
                $stmt->execute();
            }
            
            header('Location: ../vistas/dashboard.php');
            exit();
        }
    }
    
    // Si llegamos aquí, las credenciales son incorrectas
    header('Location: ../login.html?error=' . urlencode('Credenciales incorrectas'));
    exit();
}
?>