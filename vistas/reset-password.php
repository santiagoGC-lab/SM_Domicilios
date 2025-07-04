<?php
require_once '../servicios/conexion.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $conexion = conectarDB();

    // Verificar si el token es válido y no ha expirado
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expiracion > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 0) {
        header('Location: ../login.html?error=' . urlencode('El enlace ha expirado o no es válido'));
        exit();
    }
} else {
    header('Location: ../login.html');
    exit();
}

// Procesar el formulario de nueva contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];
    $token = $_POST['token'];

    if ($nueva_contrasena === $confirmar_contrasena) {
        $conexion = conectarDB();
        $hashed_password = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

        // Actualizar la contraseña y limpiar el token
        $stmt = $conexion->prepare("UPDATE usuarios SET contrasena = ?, reset_token = NULL, reset_token_expiracion = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashed_password, $token);
        $stmt->execute();

        header('Location: ../login.html?success=' . urlencode('Tu contraseña ha sido actualizada'));
        exit();
    } else {
        $error = 'Las contraseñas no coinciden';
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Restablecer Contraseña</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/login-pure.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="cont-auten">
        <div class="img-auten">
            <div>
                <h2>Sistema SM</h2>
                <img src="../componentes/img/logo2.png" alt="Logo SuperMercar" style="max-width: 150px;">
            </div>
        </div>

        <div class="form-auten">
            <div id="alertContainer">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
            </div>

            <div class="titulo-inicio">
                <h3><i class="fas fa-key me-2"></i>Restablecer Contraseña</h3>
            </div>

            <form method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="inputs-login">
                    <label for="nueva_contrasena" class="txt-form">Nueva Contraseña</label>
                    <div class="password-toggle">
                        <input type="password" class="input-form" id="nueva_contrasena" name="nueva_contrasena" required>
                        <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('nueva_contrasena', this)"></i>
                    </div>
                </div>

                <div class="inputs-login">
                    <label for="confirmar_contrasena" class="txt-form">Confirmar Contraseña</label>
                    <div class="password-toggle">
                        <input type="password" class="input-form" id="confirmar_contrasena" name="confirmar_contrasena" required>
                        <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('confirmar_contrasena', this)"></i>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Guardar Nueva Contraseña</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>

</html>