<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit;
}

$nombre = $_SESSION['nombre'] ?? '';
$apellido = $_SESSION['apellido'] ?? '';
$nombreCompleto = $nombre . ' ' . $apellido;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SM - Domiciliarios</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/domiciliarios.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item"><i class="fas fa-tachometer-alt"></i><span class="menu-text">Inicio</span></a>
            <a href="pedidos.php" class="menu-item"><i class="fas fa-shopping-bag"></i><span class="menu-text">Pedidos</span></a>
            <a href="clientes.php" class="menu-item"><i class="fas fa-users"></i><span class="menu-text">Clientes</span></a>
            <a href="domiciliarios.php" class="menu-item active"><i class="fas fa-motorcycle"></i><span class="menu-text">Domiciliarios</span></a>
            <a href="zonas.php" class="menu-item"><i class="fas fa-map-marked-alt"></i><span class="menu-text">Zonas de Entrega</span></a>
            <a href="reportes.php" class="menu-item"><i class="fas fa-chart-bar"></i><span class="menu-text">Reportes</span></a>
            <a href="configuracion.php" class="menu-item"><i class="fas fa-cog"></i><span class="menu-text">Configuración</span></a>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar"><i class="fas fa-sign-out-alt"></i><span class="menu-text">Cerrar Sesión</span></a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Gestión de Domiciliarios</h2>
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar domiciliario..." oninput="filterDomiciliarios()">
                    </div>
                    <select id="filterStatus" class="filter-select" onchange="filterDomiciliarios()">
                        <option value="">Todos</option> <!-- Agregado -->
                        <option value="disponible">Disponible</option>
                        <option value="en servicio">En Servicio</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <button class="btn-login" onclick="openNewDomiciliarioModal()">
                    <i class="fas fa-user-plus"></i> Nuevo Domiciliario
                </button>
            </div>

            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Vehículo</th>
                            <th>Placa</th>
                            <th>Zona Asignada</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="domiciliariosTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Nuevo Domiciliario</h2>
                <span class="close" onclick="closeModal('modalEditar')">×</span>
            </div>
            <form id="formEditar">
                <input type="hidden" id="domiciliarioId" name="id">
                <div class="form-group">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="tipoVehiculo">Tipo de vehículo:</label>
                    <select name="tipoVehiculo" id="tipoVehiculo">
                        <option value="Moto">Moto</option>
                        <option value="Carguero">Carguero</option>
                        <option value="Camioneta">Camioneta</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="placa">Placa:</label>
                    <input type="text" id="placa" name="placa" class="form-control" required>
                </div>
                <div class="form-group" id="zonaGroup" style="display: none;">
                    <label for="zona">Zona Asignada:</label>
                    <select id="zona" name="zona" class="form-control">
                        <option value="norte">Norte</option>
                        <option value="sur">Sur</option>
                        <option value="este">Este</option>
                        <option value="oeste">Oeste</option>
                    </select>
                </div>
                <div class="form-group" id="estadoGroup" style="display: none;">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" class="form-control">
                        <option value="disponible">Disponible</option>
                        <option value="en servicio">En Servicio</option>
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
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
            loadDomiciliarios();
        });

        function setupEventListeners() {
            document.getElementById('sidebarToggle').addEventListener('click', () => {
                document.getElementById('sidebar').classList.toggle('collapsed');
            });
            document.getElementById('formEditar').addEventListener('submit', handleDomiciliarioSubmit);
        }

        function openNewDomiciliarioModal() {
            document.getElementById('modalTitle').textContent = 'Nuevo Domiciliario';
            document.getElementById('formEditar').reset();
            document.getElementById('domiciliarioId').value = '';
            document.getElementById('zonaGroup').style.display = 'none';
            document.getElementById('estadoGroup').style.display = 'none';
            document.getElementById('modalEditar').style.display = 'block';
        }

        function editarDomiciliario(id) {
            fetch(`../servicios/obtener_domiciliario.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalTitle').textContent = 'Editar Domiciliario';
                    document.getElementById('domiciliarioId').value = id;
                    document.getElementById('nombre').value = data.nombre || '';
                    document.getElementById('telefono').value = data.telefono || '';
                    document.getElementById('tipoVehiculo').value = data.vehiculo || 'Moto';
                    document.getElementById('placa').value = data.placa || '';
                    document.getElementById('zona').value = data.zona || 'norte';
                    document.getElementById('estado').value = data.estado || 'disponible';
                    document.getElementById('zonaGroup').style.display = 'block';
                    document.getElementById('estadoGroup').style.display = 'block';
                    document.getElementById('modalEditar').style.display = 'block';
                });
        }

        function eliminarDomiciliario(id) {
            if (confirm('¿Deseas eliminar este domiciliario?')) {
                fetch('../servicios/eliminar_domiciliario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `id=${id}`
                }).then(res => res.json()).then(() => loadDomiciliarios());
            }
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function handleDomiciliarioSubmit(e) {
            e.preventDefault();

            const telefono = document.getElementById('telefono').value;
            const placa = document.getElementById('placa').value;

            if (telefono.length !== 10 || isNaN(telefono)) {
                alert('Teléfono debe tener 10 dígitos.');
                return;
            }

            if (!/^[A-Z]{3}\d{3}$/.test(placa)) {
                alert('Placa inválida. Formato: ABC123');
                return;
            }

            const formData = new FormData(e.target);
            fetch('../servicios/guardar_domiciliario.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Domiciliario guardado correctamente.');
                        closeModal('modalEditar');
                        loadDomiciliarios();
                    } else {
                        alert('Error: ' + (data.error || 'No se pudo guardar'));
                    }
                });
        }

        function showUserMenu() {
            window.location.href = 'menuUsu.html';
        }

        function quitarTildes(texto) {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        function filterDomiciliarios() {
            const searchInput = document.getElementById('searchInput').value.trim();
            const search = quitarTildes(searchInput.toLowerCase());
            const status = document.getElementById('filterStatus').value.toLowerCase();
            const rows = document.querySelectorAll('#domiciliariosTableBody tr');

            rows.forEach(row => {
                const nombre = quitarTildes(row.children[1].textContent);
                const telefono = row.children[2].textContent.toLowerCase();
                const vehiculo = quitarTildes(row.children[3].textContent);
                const zona = quitarTildes((row.children[5].textContent || ''));
                const estado = quitarTildes(row.children[6].textContent);

                const coincideTexto =
                    nombre.includes(search) ||
                    telefono.includes(search) ||
                    vehiculo.includes(search) ||
                    zona.includes(search);

                const coincideEstado = status === '' || estado === status;

                row.style.display = (coincideTexto && coincideEstado) ? '' : 'none';
            });
        }

        function loadDomiciliarios() {
            fetch('../servicios/obtener_domiciliarios.php')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById('domiciliariosTableBody');
                    let html = '';

                    data.forEach(d => {
                        html += `
                        <tr>
                            <td>${d.id_domiciliario}</td>
                            <td>${d.nombre}</td>
                            <td>${d.telefono}</td>
                            <td>${d.vehiculo}</td>
                            <td>${d.placa}</td>
                            <td>${d.zona || 'Sin asignar'}</td>
                            <td><span class="estado-${d.estado.replace(/\s/g, '')}">${d.estado}</span></td>
                            <td>
                                <button class="btn btn-editar" onclick="editarDomiciliario(${d.id_domiciliario})"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-eliminar" onclick="eliminarDomiciliario(${d.id_domiciliario})"><i class="fas fa-trash-alt"></i></button>
                            </td>
                        </tr>`;
                    });

                    tbody.innerHTML = html;
                    filterDomiciliarios();
                });
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>

</html>
