<?php
// --- Verificación de permisos y obtención de nombre de usuario ---
require_once '../servicios/verificar_permisos.php';
verificarAcceso('vehiculos');
$nombreCompleto = obtenerNombreUsuario();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SM - Vehículos</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/vehiculos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <?php if (tienePermiso('dashboard')): ?>
                <a href="dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">Inicio</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('pedidos')): ?>
                <a href="pedidos.php" class="menu-item">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="menu-text">Pedidos</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('coordinador')): ?>
                <a href="coordinador.php" class="menu-item">
                    <i class="fas fa-truck"></i>
                    <span class="menu-text">Coordinador</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('clientes')): ?>
                <a href="clientes.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span class="menu-text">Clientes</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('domiciliarios')): ?>
                <a href="domiciliarios.php" class="menu-item">
                    <i class="fas fa-motorcycle"></i>
                    <span class="menu-text">Domiciliarios</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('vehiculos')): ?>
                <a href="vehiculos.php" class="menu-item active">
                    <i class="fas fa-car"></i>
                    <span class="menu-text">Vehículos</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
                <a href="zonas.php" class="menu-item">
                    <i class="fas fa-map-marked-alt"></i>
                    <span class="menu-text">Zonas de Entrega</span>
                </a>
            <?php endif; ?>
            <?php if (tienePermiso('reportes')): ?>
                <a href="reportes.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span class="menu-text">Reportes</span>
                </a>
            <?php endif; ?>
            <?php if (esAdmin()): ?>
                <a href="tabla_usuarios.php" class="menu-item"><i class="fas fa-users-cog"></i><span class="menu-text">Gestionar Usuarios</span></a>
            <?php endif; ?>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Gestión de Vehículos</h2>
            <div class="user-info" onclick="showUserMenu()">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName"><?= htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>

        <div class="users-section">
            <div class="users-actions">
                <div class="search-and-filter">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar vehículo..." oninput="filterVehiculos()">
                    </div>
                    <select id="filterStatus" class="filter-select" onchange="filterVehiculos()">
                        <option value="">Todos</option>
                        <option value="disponible">Disponible</option>
                        <option value="en_ruta">En Ruta</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <button class="btn-login" onclick="openNewVehiculoModal()">
                    <i class="fas fa-plus"></i> Nuevo Vehículo
                </button>
            </div>

            <div class="users-table-container" style="background: #fff; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.07); overflow-x: auto;">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Placa</th>
                            <th>Estado</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="vehiculosTableBody"></tbody>
                </table>
                <div id="paginationVehiculos" class="pagination"></div>
            </div>
        </div>
    </div>

    <!-- Modal para crear o editar un vehículo -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Vehículo</h2>
                <span class="close" onclick="closeModal('modalEditar')">×</span>
            </div>
            <form id="formEditar">
                <input type="hidden" id="vehiculoId" name="id">
                <div class="form-group">
                    <label for="tipo">Tipo de Vehículo:</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="moto">Motocicleta</option>
                        <option value="camioneta">Camioneta</option>
                        <option value="carguero">Carguero</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="placa">Placa:</label>
                    <input type="text" id="placa" name="placa" class="form-control" required placeholder="ABC-123">
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" class="form-control" rows="3" placeholder="Descripción del vehículo (opcional)"></textarea>
                </div>
                <div class="form-group" id="estadoGroup" style="display: none;">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" class="form-control">
                        <option value="disponible">Disponible</option>
                        <option value="en_ruta">En Ruta</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal('modalEditar')">Cancelar</button>
                    <button type="submit" class="btn-login">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- Variables de paginación y estado global ---
        const rowsPerPage = 5;
        let currentPage = 1;
        let totalVehiculos = 0;

        // --- Inicialización de eventos y carga de vehículos al cargar la página ---
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            loadVehiculos(currentPage);
        });

        // Configura los listeners de eventos principales
        function setupEventListeners() {
            document.getElementById('sidebarToggle').addEventListener('click', () => {
                document.getElementById('sidebar').classList.toggle('collapsed');
            });
            document.getElementById('formEditar').addEventListener('submit', handleVehiculoSubmit);
        }

        // Abre el modal para crear un nuevo vehículo
        function openNewVehiculoModal() {
            document.getElementById('modalTitle').textContent = 'Nuevo Vehículo';
            document.getElementById('formEditar').reset();
            document.getElementById('vehiculoId').value = '';
            document.getElementById('estadoGroup').style.display = 'none';
            document.getElementById('modalEditar').style.display = 'block';
        }

        // Carga los datos de un vehículo y abre el modal para editar
        function editarVehiculo(id) {
            fetch(`../servicios/vehiculos.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=obtener&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const vehiculo = data.vehiculo;
                    document.getElementById('modalTitle').textContent = 'Editar Vehículo';
                    document.getElementById('vehiculoId').value = vehiculo.id_vehiculo;
                    document.getElementById('tipo').value = vehiculo.tipo;
                    document.getElementById('placa').value = vehiculo.placa;
                    document.getElementById('descripcion').value = vehiculo.descripcion || '';
                    document.getElementById('estado').value = vehiculo.estado;
                    document.getElementById('estadoGroup').style.display = 'block';
                    document.getElementById('modalEditar').style.display = 'block';
                } else {
                    alert('Error al cargar los datos del vehículo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al cargar los datos del vehículo');
            });
        }

        // Maneja el envío del formulario de vehículo
        function handleVehiculoSubmit(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const isEdit = formData.get('id') !== '';
            formData.append('action', isEdit ? 'actualizar' : 'crear');

            fetch('../servicios/vehiculos.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeModal('modalEditar');
                    loadVehiculos(currentPage);
                    alert(isEdit ? 'Vehículo actualizado exitosamente' : 'Vehículo creado exitosamente');
                } else {
                    alert(data.message || 'Error al procesar la solicitud');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        }

        // Carga la lista de vehículos con paginación
        function loadVehiculos(page = 1) {
            const searchTerm = document.getElementById('searchInput').value;
            const statusFilter = document.getElementById('filterStatus').value;
            
            fetch('../servicios/vehiculos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=listar&page=${page}&search=${encodeURIComponent(searchTerm)}&status=${encodeURIComponent(statusFilter)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayVehiculos(data.vehiculos);
                    totalVehiculos = data.total;
                    currentPage = page;
                    updatePagination();
                } else {
                    console.error('Error al cargar vehículos:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Muestra los vehículos en la tabla
        function displayVehiculos(vehiculos) {
            const tbody = document.getElementById('vehiculosTableBody');
            tbody.innerHTML = '';

            vehiculos.forEach(vehiculo => {
                const row = document.createElement('tr');
                
                // Iconos para tipos de vehículos
                const tipoIcons = {
                    'moto': '<i class="fas fa-motorcycle"></i>',
                    'camioneta': '<i class="fas fa-truck-pickup"></i>',
                    'carguero': '<i class="fas fa-truck"></i>'
                };
                
                // Clases para estados
                const estadoClasses = {
                    'disponible': 'status-disponible',
                    'en_ruta': 'status-en-ruta',
                    'mantenimiento': 'status-mantenimiento',
                    'inactivo': 'status-inactivo'
                };

                row.innerHTML = `
                    <td>
                        <span class="vehicle-type">
                            ${tipoIcons[vehiculo.tipo] || '<i class="fas fa-car"></i>'}
                            ${vehiculo.tipo.charAt(0).toUpperCase() + vehiculo.tipo.slice(1)}
                        </span>
                    </td>
                    <td><span class="placa">${vehiculo.placa}</span></td>
                    <td><span class="status ${estadoClasses[vehiculo.estado] || ''}">${vehiculo.estado.replace('_', ' ')}</span></td>
                    <td>${vehiculo.descripcion || '-'}</td>
                    <td>
                        <div class="actions">
                            <button class="btn-edit" onclick="editarVehiculo(${vehiculo.id_vehiculo})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-status" onclick="cambiarEstado(${vehiculo.id_vehiculo}, '${vehiculo.estado}')" title="Cambiar Estado">
                                <i class="fas fa-exchange-alt"></i>
                            </button>
                            <button class="btn-delete" onclick="eliminarVehiculo(${vehiculo.id_vehiculo})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Actualiza la paginación
        function updatePagination() {
            const totalPages = Math.ceil(totalVehiculos / rowsPerPage);
            const paginationContainer = document.getElementById('paginationVehiculos');
            paginationContainer.innerHTML = '';

            if (totalPages <= 1) return;

            // Botón anterior
            if (currentPage > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.textContent = 'Anterior';
                prevBtn.onclick = () => loadVehiculos(currentPage - 1);
                paginationContainer.appendChild(prevBtn);
            }

            // Números de página
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = i;
                pageBtn.className = i === currentPage ? 'active' : '';
                pageBtn.onclick = () => loadVehiculos(i);
                paginationContainer.appendChild(pageBtn);
            }

            // Botón siguiente
            if (currentPage < totalPages) {
                const nextBtn = document.createElement('button');
                nextBtn.textContent = 'Siguiente';
                nextBtn.onclick = () => loadVehiculos(currentPage + 1);
                paginationContainer.appendChild(nextBtn);
            }
        }

        // Filtra los vehículos según búsqueda y estado
        function filterVehiculos() {
            currentPage = 1;
            loadVehiculos(1);
        }

        // Cambia el estado de un vehículo
        function cambiarEstado(id, estadoActual) {
            const estados = ['disponible', 'en_ruta', 'mantenimiento', 'inactivo'];
            const estadosTexto = ['Disponible', 'En Ruta', 'Mantenimiento', 'Inactivo'];
            
            let options = '';
            estados.forEach((estado, index) => {
                const selected = estado === estadoActual ? 'selected' : '';
                options += `<option value="${estado}" ${selected}>${estadosTexto[index]}</option>`;
            });
            
            const nuevoEstado = prompt(`Seleccionar nuevo estado:\n\n${estadosTexto.map((texto, i) => `${i+1}. ${texto}`).join('\n')}\n\nIngrese el número (1-4):`);
            
            if (nuevoEstado && nuevoEstado >= 1 && nuevoEstado <= 4) {
                const estadoSeleccionado = estados[nuevoEstado - 1];
                
                fetch('../servicios/vehiculos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=cambiar_estado&id=${id}&estado=${estadoSeleccionado}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadVehiculos(currentPage);
                        alert('Estado actualizado exitosamente');
                    } else {
                        alert(data.message || 'Error al cambiar el estado');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cambiar el estado');
                });
            }
        }

        // Elimina un vehículo
        function eliminarVehiculo(id) {
            if (confirm('¿Está seguro de que desea eliminar este vehículo?')) {
                fetch('../servicios/vehiculos.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=eliminar&id=${id}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadVehiculos(currentPage);
                        alert('Vehículo eliminado exitosamente');
                    } else {
                        alert(data.message || 'Error al eliminar el vehículo');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el vehículo');
                });
            }
        }

        // Cierra un modal
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Función para mostrar menú de usuario (placeholder)
        function showUserMenu() {
            // Implementar si es necesario
        }
    </script>
</body>
</html>