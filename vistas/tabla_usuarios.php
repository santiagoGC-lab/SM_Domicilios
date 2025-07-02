<?php
require_once '../servicios/conexion.php';

// Obtener la conexión
$conexion = conectarDB();

// Consultar usuarios
$query = "SELECT id, nombre, email, rol, estado FROM usuarios";
$resultado = mysqli_query($conexion, $query);
?>

<table class="users-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($usuario = mysqli_fetch_assoc($resultado)) { ?>
            <tr>
                <td><?php echo $usuario['id']; ?></td>
                <td><?php echo $usuario['nombre']; ?></td>
                <td><?php echo $usuario['email']; ?></td>
                <td><?php echo $usuario['rol']; ?></td>
                <td>
                    <span class="estado-<?php echo strtolower($usuario['estado']); ?>">
                        <?php echo $usuario['estado']; ?>
                    </span>
                </td>
                <td>
                    <button class="btn btn-editar" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-eliminar" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<?php
// Cerrar la conexión
mysqli_close($conexion);
?>