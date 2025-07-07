<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php'; // Ajusta la ruta según tu estructura

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST['nombreCompleto']);
    $documento = trim($_POST['numeroDocumento']);
    $correo = trim($_POST['email']);
    $contrasena = $_POST['contrasena'];
    $confirmar = $_POST['confirmarContrasena'];

    // Validar que las contraseñas coincidan
    if ($contrasena !== $confirmar) {
        echo "Error: Las contraseñas no coinciden.";
        exit;
    }

    // Hashear la contraseña
    $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

    // Conexión a la BD
    $conexion = ConectarDB();

    // Validar si el documento o el correo ya existen
    $sqlVerificar = "SELECT * FROM usuarios WHERE documento = ? OR correo = ?";
    $stmt = $conexion->prepare($sqlVerificar);
    $stmt->bind_param("ss", $documento, $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo "Error: El documento o correo ya están registrados.";
        exit;
    }

    // Insertar nuevo usuario
    $rol = $_POST['rol'];
    $fecha = date("Y-m-d");

    $sqlInsertar = "INSERT INTO usuarios (nombre_completo, documento, correo, contraseña, rol, fecha_registro)
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sqlInsertar);
    $stmt->bind_param("ssssss", $nombre, $documento, $correo, $contrasenaHash, $rol, $fecha);

    if ($stmt->execute()) {
        echo "Usuario creado correctamente.";
    } else {
        echo "Error al registrar el usuario: " . $stmt->error;
    }

    if (!isset($_POST['rol'])) {
        die("Error: Debes seleccionar un rol.");
    }
    $rol = $_POST['rol'];


    $stmt->close();
    $conexion->close();
} else {
    echo "Método de solicitud inválido.";
}
