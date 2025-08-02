<?php
// --- Verificación de permisos y conexión a la base de datos ---
require_once '../servicios/verificar_permisos.php';
verificarAcceso('vehiculos');

require_once '../servicios/conexion.php';

// Obtener estadísticas de vehículos
$totalVehiculos = getPDO()->query("SELECT COUNT(*) FROM vehiculos")->fetchColumn();
$vehiculosDisponibles = getPDO()->query("SELECT COUNT(*) FROM vehiculos WHERE estado = 'disponible'")->fetchColumn();
$vehiculosEnRuta = getPDO()->query("SELECT COUNT(*) FROM vehiculos WHERE estado = 'en_ruta'")->fetchColumn();
$vehiculosMantenimiento = getPDO()->query("SELECT COUNT(*) FROM vehiculos WHERE estado = 'mantenimiento'")->fetchColumn();

// Obtener lista de vehículos
$vehiculos = getPDO()->query("
    SELECT id_vehiculo, tipo, placa, estado, descripcion 
    FROM vehiculos 
    ORDER BY tipo, placa
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vehículos - SM Domicilios</title>
    <link rel="stylesheet" href="../componentes/vehiculos.css">
    <link rel="stylesheet" href="../componentes/menuUsu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Menú lateral -->
    <?php include '../componentes/menu.php'; ?>

    <!-- Contenido principal -->
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-truck"></i> Gestión de Vehículos</h1>
            <div class="user-info">
                <span>Bienvenido, <?php echo obtenerNombreUsuario(); ?></span>
                <a href="../servicios/cerrar_sesion.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalVehiculos; ?></h3>
                    <p>Total Vehículos</p>
                </div>
            </div>
            <div class="stat-card available">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $vehiculosDisponibles; ?></h3>
                    <p>Disponibles</p>
                </div>
            </div>
            <div class="stat-card busy">
                <div class="stat-icon">
                    <i class="fas fa-route"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $vehiculosEnRuta; ?></h3>
                    <p>En Ruta</p>
                </div>
            </div>
            <div class="stat-card maintenance">
                <div class="stat-icon">
                    <i class="fas fa-wrench"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $vehiculosMantenimiento; ?></h3>
                    <p>Mantenimiento</p>
                </div>
            </div>
        </div>

        <!-- Controles -->
        <div class="controls">
            <button class="btn-primary" onclick="openNewVehicleModal()">
                <i class="fas fa-plus"></i> Agregar Vehículo
            </button>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Buscar por placa o tipo..." onkeyup="filterVehicles()">
                <i class="fas fa-search"></i>
            </div>
        </div>

        <!-- Tabla de vehículos -->
        <div class="table-container">
            <table class="vehicles-table" id="vehiclesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Placa</th>
                        <th>Estado</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehiculos as $vehiculo): ?>
                    <tr>
                        <td><?php echo $vehiculo['id_vehiculo']; ?></td>
                        <td>
                            <span class="vehicle-type <?php echo strtolower($vehiculo['tipo']); ?>">
                                <?php 
                                $icon = '';
                                switch(strtolower($vehiculo['tipo'])) {
                                    case 'moto': $icon = 'fas fa-motorcycle'; break;
                                    case 'camioneta': $icon = 'fas fa-truck-pickup'; break;
                                    case 'carguero': $icon = 'fas fa-truck'; break;
                                    default: $icon = 'fas fa-car';
                                }
                                echo "<i class='$icon'></i> " . $vehiculo['tipo'];
                                ?>
                            </span>
                        </td>
                        <td class="placa"><?php echo $vehiculo['placa']; ?></td>
                        <td>
                            <span class="status <?php echo $vehiculo['estado']; ?>">
                                <?php echo ucfirst($vehiculo['estado']); ?>
                            </span>
                        </td>
                        <td><?php echo $vehiculo['descripcion'] ?: '-'; ?></td>
                        <td class="actions">
                            <button class="btn-edit" onclick="editVehicle(<?php echo $vehiculo['id_vehiculo']; ?>)" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-status" onclick="changeStatus(<?php echo $vehiculo['id_vehiculo']; ?>, '<?php echo $vehiculo['estado']; ?>')" title="Cambiar Estado">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                            <button class="btn-delete" onclick="deleteVehicle(<?php echo $vehiculo['id_vehiculo']; ?>)" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para crear/editar vehículo -->
    <div id="modalVehicle" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Vehículo</h2>
                <span class="close" onclick="closeModal('modalVehicle')">&times;</span>
            </div>
            <form id="vehicleForm">
                <input type="hidden" id="vehicleId" name="id">
                
                <div class="form-group">
                    <label for="tipo">Tipo de Vehículo:</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Seleccionar tipo...</option>
                        <option value="Moto">🏍️ Moto</option>
                        <option value="Camioneta">🚐 Camioneta</option>
                        <option value="Carguero">🚛 Carguero</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="placa">Placa:</label>
                    <input type="text" id="placa" name="placa" required maxlength="20" 
                           placeholder="Ej: ABC123" style="text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" required>
                        <option value="disponible">✅ Disponible</option>
                        <option value="en_ruta">🚗 En Ruta</option>
                        <option value="mantenimiento">🔧 Mantenimiento</option>
                        <option value="inactivo">❌ Inactivo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción (Opcional):</label>
                    <textarea id="descripcion" name="descripcion" rows="3" 
                              placeholder="Información adicional del vehículo..."></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalVehicle')">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para cambiar estado -->
    <div id="modalStatus" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Cambiar Estado del Vehículo</h2>
                <span class="close" onclick="closeModal('modalStatus')">&times;</span>
            </div>
            <form id="statusForm">
                <input type="hidden" id="statusVehicleId">
                
                <div class="form-group">
                    <label for="newStatus">Nuevo Estado:</label>
                    <select id="newStatus" required>
                        <option value="disponible">✅ Disponible</option>
                        <option value="en_ruta">🚗 En Ruta</option>
                        <option value="mantenimiento">🔧 Mantenimiento</option>
                        <option value="inactivo">❌ Inactivo</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalStatus')">Cancelar</button>
                    <button type="submit" class="btn-primary">Cambiar Estado</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Función para abrir modal de nuevo vehículo
        function openNewVehicleModal() {
            document.getElementById('modalTitle').textContent = 'Nuevo Vehículo';
            document.getElementById('vehicleForm').reset();
            document.getElementById('vehicleId').value = '';
            document.getElementById('modalVehicle').style.display = 'block';
        }

        // Función para editar vehículo
        function editVehicle(id) {
            fetch('../servicios/vehiculos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }
                
                document.getElementById('modalTitle').textContent = 'Editar Vehículo';
                document.getElementById('vehicleId').value = data.id_vehiculo;
                document.getElementById('tipo').value = data.tipo;
                document.getElementById('placa').value = data.placa;
                document.getElementById('estado').value = data.estado;
                document.getElementById('descripcion').value = data.descripcion || '';
                document.getElementById('modalVehicle').style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los datos del vehículo');
            });
        }

        // Función para cambiar estado
        function changeStatus(id, currentStatus) {
            document.getElementById('statusVehicleId').value = id;
            document.getElementById('newStatus').value = currentStatus;
            document.getElementById('modalStatus').style.display = 'block';
        }

        // Función para eliminar vehículo
        function deleteVehicle(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este vehículo?')) {
                fetch('../servicios/vehiculos.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `accion=eliminar&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Vehículo eliminado correctamente');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.error || 'No se pudo eliminar el vehículo'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el vehículo');
                });
            }
        }

        // Función para cerrar modal
        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        // Función para filtrar vehículos
        function filterVehicles() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('vehiclesTable');
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                const tipo = cells[1].textContent || cells[1].innerText;
                const placa = cells[2].textContent || cells[2].innerText;
                
                if (tipo.toUpperCase().indexOf(filter) > -1 || placa.toUpperCase().indexOf(filter) > -1) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

        // Manejo del formulario de vehículo
        document.getElementById('vehicleForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const id = document.getElementById('vehicleId').value;
            formData.append('accion', id ? 'actualizar' : 'crear');
            
            fetch('../servicios/vehiculos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(id ? 'Vehículo actualizado correctamente' : 'Vehículo creado correctamente');
                    closeModal('modalVehicle');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo guardar el vehículo'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al guardar el vehículo');
            });
        });

        // Manejo del formulario de estado
        document.getElementById('statusForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const id = document.getElementById('statusVehicleId').value;
            const estado = document.getElementById('newStatus').value;
            
            fetch('../servicios/vehiculos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `accion=cambiar_estado&id=${id}&estado=${estado}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Estado cambiado correctamente');
                    closeModal('modalStatus');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo cambiar el estado'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cambiar el estado');
            });
        });

        // Convertir placa a mayúsculas automáticamente
        document.getElementById('placa').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>