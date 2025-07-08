<?php
session_start();
include "conexion.php";

// Verifica el método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Método no permitido";
    exit;
}

// Verifica campos requeridos
if (empty($_POST['numeroDocumento']) || empty($_POST['contrasena'])) {
    header("Location: ../login.php?error=" . urlencode("Por favor, completa todos los campos."));
    exit;
}

// Limpia entrada
$num_documento = filter_var($_POST['numeroDocumento'], FILTER_VALIDATE_INT);
$contrasena = trim($_POST['contrasena']);

if ($num_documento === false) {
    header("Location: ../login.php?error=" . urlencode("Documento inválido."));
    exit;
}

try {
    echo "Intentando conectar a la base de datos...<br>";
    $conexiondb = ConectarDB();

    if (!$conexiondb) {
        echo "No se pudo conectar a la base de datos";
        exit;
    }

    echo "Conexión exitosa<br>";

    $stmt = $conexiondb->prepare("SELECT id_usuario, nombre_completo, rol, contrasena FROM usuarios WHERE documento = ?");
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexiondb->error);
    }

    echo "Consulta preparada<br>";

    $stmt->bind_param("i", $num_documento);
    $stmt->execute();

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Error al ejecutar consulta: " . $stmt->error);
    }

    if ($result->num_rows === 0) {
        header("Location: ../login.php?error=" . urlencode("Usuario no encontrado."));
        exit;
    }

    $usuario = $result->fetch_assoc();

    if (!password_verify($contrasena, $usuario['contrasena'])) {
        header("Location: ../login.php?error=" . urlencode("Contraseña incorrecta."));
        exit;
    }

    // Guarda sesión
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['nombre'] = $usuario['nombre_completo'];
    $_SESSION['rol'] = $usuario['rol'];

    session_regenerate_id(true);

    header('Location: ../vistas/dashboard.php');
    exit;
} catch (Exception $e) {
    echo "Error atrapado: " . $e->getMessage();
    exit;
}
