<?php
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    $conexion = conectarDB();
    
    // Verificar si el email existe
    $stmt = $conexion->prepare("SELECT id, nombre FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        
        // Generar token único
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Guardar token en la base de datos
        $stmt = $conexion->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expiracion = ? WHERE id = ?");
        $stmt->bind_param("ssi", $token, $expiracion, $usuario['id']);
        $stmt->execute();
        
        // Enviar email
        $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/SM/vistas/reset-password.php?token=" . $token;
        $to = $email;
        $subject = "Recuperación de Contraseña - Sistema SM";
        $message = "Hola " . $usuario['nombre'] . ",\n\n";
        $message .= "Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:\n\n";
        $message .= $resetLink . "\n\n";
        $message .= "Este enlace expirará en 1 hora.\n\n";
        $message .= "Si no solicitaste este cambio, ignora este mensaje.\n\n";
        $message .= "Saludos,\nEquipo SM";
        
        $headers = "From: noreply@sm.com";
        
        if(mail($to, $subject, $message, $headers)) {
            header('Location: ../vistas/recuperar-contra.html?success=' . urlencode('Se han enviado las instrucciones a tu correo'));
        } else {
            header('Location: ../vistas/recuperar-contra.html?error=' . urlencode('Error al enviar el correo'));
        }
    } else {
        // No revelamos si el email existe o no por seguridad
        header('Location: ../vistas/recuperar-contra.html?success=' . urlencode('Si el correo existe, recibirás las instrucciones'));
    }
    
    exit();
}
?>