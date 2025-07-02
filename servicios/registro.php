<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombreCompleto = filter_input(INPUT_POST, 'nombreCompleto', FILTER_SANITIZE_STRING);
    $numeroDocumento = filter_input(INPUT_POST, 'numeroDocumento', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $contrasena = $_POST['contrasena'];
    $confirmarContrasena = $_POST['confirmarContrasena'];
    
    // Validar que las contraseñas coincidan
    if ($contrasena !== $confirmarContrasena) {
        header('Location: ../vistas/crearUsu.html?error=' . urlencode('Las contraseñas no coinciden'));
        exit();
    }
    
    $conexion = conectarDB();
    
    // Verificar si el usuario ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE numero_documento = ? OR email = ?");
    $stmt->bind_param("ss", $numeroDocumento, $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows > 0) {
        header('Location: ../vistas/crearUsu.html?error=' . urlencode('El número de documento o email ya está registrado'));
        exit();
    }
    
    // Encriptar la contraseña
    $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
    
    // Insertar el nuevo usuario
    $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, numero_documento, email, contrasena) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombreCompleto, $numeroDocumento, $email, $contrasena_hash);
    
    if ($stmt->execute()) {
        header('Location: ../login.html?success=' . urlencode('Usuario creado exitosamente. Por favor inicia sesión'));
    } else {
        header('Location: ../vistas/crearUsu.html?error=' . urlencode('Error al crear el usuario'));
    }
    
    $stmt->close();
    $conexion->close();
    exit();
}
?>