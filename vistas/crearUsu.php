<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Crear Usuario</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/crearUsu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

    <div class="cont-auten">
        <!-- Sección de imagen/logo -->
        <div class="img-auten">
            <div>
                <h2>Sistema SM</h2>
                <img src="../componentes/img/logo2.png" alt="Logo SuperMercar" style="max-width: 150px;">
            </div>
        </div>


        <div class="form-auten">
            <div id="alertContainer"></div>
            <div class="titulo-inicio">
                <h3><i class="titulo-inicio"></i>Crear Usuario</h3>
            </div>

            <form id="formRegistro" action="../servicios/registro.php" method="post">
                <div class="inputs-login">
                    <label for="nombreCompleto" class="txt-form">Nombre Completo</label>
                    <input type="text" class="input-form" id="nombreCompleto" name="nombreCompleto" required>
                </div>
                <div class="inputs-login">
                    <label for="numeroDocumento" class="txt-form">Número de Documento</label>
                    <input type="text" class="input-form" id="numeroDocumento" name="numeroDocumento" minlength="6"
                        maxlength="12" pattern="[0-9]+" required>
                </div>
                <div class="inputs-login">
                    <label for="email" class="txt-form">Correo Electrónico</label>
                    <input type="email" class="input-form" id="email" name="email" required>
                </div>
                <div class="inputs-login">
                    <label for="rol" class="txt-form">Rol</label>
                    <select id="rol" name="rol" class="input-form" required>
                        <option value="" disabled selected>Selecciona un rol</option>
                        <option value="admin">Administrador</option>
                        <option value="cliente">Cliente</option>
                        <option value="repartidor">Repartidor</option>
                    </select>
                </div>

                <div class="inputs-login">
                    <label for="contrasena" class="txt-form">Contraseña</label>
                    <div class="password-toggle">
                        <input type="password" class="input-form" id="contrasena" name="contrasena" maxlength="20"
                            minlength="8" required>
                        <i class="toggle-icon fas fa-eye" onclick="togglePasswordVisibility('contrasena', this)"></i>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Función para mostrar/ocultar contraseña
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

        // Validación de contraseñas coincidentes
        document.getElementById('formRegistro').addEventListener('submit', function (e) {
            const password = document.getElementById('contrasena').value;
            const confirmPassword = document.getElementById('confirmarContrasena').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('Las contraseñas no coinciden', 'error');
            }
        });

        // Función para mostrar mensajes de alerta
        function showAlert(message, type = 'error') {
            const alertContainer = document.getElementById('alertContainer');
            alertContainer.innerHTML = `<div class="alerta alerta-${type}">${message}</div>`;
            setTimeout(() => alertContainer.innerHTML = '', 5000);
        }
    </script>
</body>

</html>