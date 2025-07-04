<?php
session_start();
require_once '../conexion.php'; // Asegúrate de tener este archivo con la conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $documento = $_POST['numeroDocumento'];
    $contrasena = $_POST['contrasena'];

    // Consulta del usuario
    $sql = "SELECT * FROM usuarios WHERE numero_documento = ? AND estado = 'activo' LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $documento);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($contrasena, $usuario['contrasena'])) {
            // Login correcto
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol'] = $usuario['rol'];

            header("Location: ../vistas/home.php");
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: ../login.html?error=" . urlencode("Contraseña incorrecta."));
            exit();
        }
    } else {
        // Usuario no encontrado
        header("Location: ../login.html?error=" . urlencode("Usuario no encontrado o inactivo."));
        exit();
    }
}
?>
