<?php
session_start();
require_once 'conexion.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeroDocumento = trim($_POST['numeroDocumento'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');
    $recordarme = isset($_POST['recuerdame']);

    if (empty($numeroDocumento) || empty($contrasena)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        // Buscar usuario por número de documento
        $stmt = $pdo->prepare("SELECT id_usuario, nombre_completo, contraseña, rol, estado FROM usuarios WHERE documento = ?");
        $stmt->execute([$numeroDocumento]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && $usuario['estado'] === 'activo' && password_verify($contrasena, $usuario['contrasena'])) {
            // Autenticación exitosa
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['nombre'] = $usuario['nombre_completo'];
            $_SESSION['rol'] = $usuario['rol'];

            // Recordarme
            if ($recordarme) {
                $token = bin2hex(random_bytes(32));
                $stmt = $pdo->prepare("UPDATE usuarios SET remember_token = ? WHERE id_usuario = ?");
                $stmt->execute([$token, $usuario['id_usuario']]);
                setcookie('remember_token', $token, time() + (30 * 24 * 3600), '/'); // 30 días
            }

            // Redirigir según rol
            header('Location: ../vistas/dashboard.php');
            exit;
        } else {
            $error = 'Número de documento o contraseña incorrectos, o usuario inactivo.';
            header('Location: ../index.html?error=' . urlencode($error));
            exit;
        }
    }
}
?>
