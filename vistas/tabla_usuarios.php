<?php
require_once '../servicios/conexion.php';

// Obtener la conexión
$conexion = conectarDB();

// Consultar usuarios
$query = "SELECT id_usuario, CONCAT(nombre, ' ', apellido) AS nombre, rol, estado FROM usuarios";
$resultado = mysqli_query($conexion, $query);

?>

<table class="users-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($usuario = mysqli_fetch_assoc($resultado)) { ?>
            <tr>
                <td><?php echo $usuario['id_usuario']; ?></td>
                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                <td>
                    <span class="estado-<?php echo strtolower($usuario['estado']); ?>">
                        <?php echo htmlspecialchars($usuario['estado']); ?>
                    </span>
                </td>
                <td>
                    <button class="btn btn-editar" onclick="editarUsuario(<?php echo $usuario['id_usuario']; ?>)">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-eliminar" onclick="eliminarUsuario(<?php echo $usuario['id_usuario']; ?>)">
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