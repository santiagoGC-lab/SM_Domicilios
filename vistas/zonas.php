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
    <title>SM - Zonas de Entrega</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css" />
    <link rel="stylesheet" href="../componentes/zonas.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="../componentes/img/logo2.png" alt="Logo" />
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-tachometer-alt"></i>
                <span class="menu-text">Inicio</span>
            </a>
            <a href="pedidos.php" class="menu-item">
                <i class="fas fa-shopping-bag"></i>
                <span class="menu-text">Pedidos</span>
            </a>
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Clientes</span>
            </a>
            <a href="domiciliarios.php" class="menu-item">
                <i class="fas fa-motorcycle"></i>
                <span class="menu-text">Domiciliarios</span>
            </a>
            <a href="zonas.php" class="menu-item active">
                <i class="fas fa-map-marked-alt"></i>
                <span class="menu-text">Zonas de Entrega</span>
            </a>
            <a href="reportes.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <a href="configuracion.php" class="menu-item">
                <i class="fas fa-cog"></i>
                <span class="menu-text">Configuración</span>
            </a>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="header">
            <h2>Gestión de Zonas de Entrega</h2>
            <div class="user-info">
                <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Usuario" />
                <span>Bienvenido, <strong id="userName"><?php echo htmlspecialchars($nombreCompleto); ?></strong></span>
            </div>
        </div>
        <div class="users-section">
            <div class="users-actions">
                <div class="search-and-filter">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar zona o ciudad..." oninput="cargarZonas()">
                        <button class="btn btn-search" onclick="cargarZonas()">Buscar</button>
                    </div>
                    <select id="filterStatus" class="filter-select" onchange="cargarZonas()">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <button class="btn btn-primary" onclick="abrirModalNueva()">
                    <i class="fas fa-plus"></i> Nueva Zona
                </button>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre de Zona</th>
                            <th>Ciudad</th>
                            <th>Tarifa Base</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="zonasTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal único para editar / crear -->
    <div id="modalZona" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Zona</h2>
                <span class="close" onclick="closeModal('modalZona')">×</span>
            </div>
            <form id="formZona">
                <input type="hidden" id="zonaId" name="id">
                <div class="form-group">
                    <label for="nuevoNombre">Nombre de la Zona:</label>
                    <select id="nuevoNombre" name="nombre" class="form-control" required>
                        <option value="">Selecciona una zona</option>
                        <option value="Norte">Norte</option>
                        <option value="Sur">Sur</option>
                        <option value="Centro">Centro</option>
                        <option value="Occidente">Occidente</option>
                        <option value="Oriente">Oriente</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ciudad">Ciudad:</label>
                    <input type="text" id="ciudad" name="ciudad" class="form-control" required placeholder="Ej. Bogotá">
                </div>
                <div class="form-group">
                    <label for="tarifa">Tarifa Base:</label>
                    <input type="number" id="tarifa" name="tarifa" class="form-control" required placeholder="Ej. 5000" min="0" step="1">
                </div>
                <div class="form-group">
                    <label for="estado">Estado:</label>
                    <select id="estado" name="estado" class="form-control" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('modalZona')">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar Zona</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('formZona').addEventListener('submit', guardarZona);
            cargarZonas();
        });

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function abrirModalNueva() {
            document.getElementById('formZona').reset();
            document.getElementById('zonaId').value = '';
            document.getElementById('modalTitle').textContent = 'Nueva Zona';
            document.getElementById('modalZona').style.display = 'block';
        }

        function editarZona(id) {
            fetch('../servicios/obtener_zona_por_id.php?id=' + id)
                .then(res => res.json())
                .then(zona => {
                    if (zona.error) {
                        alert('Error: ' + zona.error);
                        return;
                    }
                    document.getElementById('modalTitle').textContent = 'Editar Zona';
                    document.getElementById('zonaId').value = zona.id_zona;
                    document.getElementById('nuevoNombre').value = zona.nombre;
                    document.getElementById('ciudad').value = zona.ciudad;
                    document.getElementById('tarifa').value = zona.tarifa_base;
                    document.getElementById('estado').value = zona.estado;
                    document.getElementById('modalZona').style.display = 'block';
                })
                .catch(error => alert('Error al cargar la zona: ' + error.message));
        }

        function eliminarZona(id) {
            if (confirm('¿Estás seguro de que deseas eliminar esta zona?')) {
                const formData = new FormData();
                formData.append('id', id);

                fetch('../servicios/eliminar_zona.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            alert('Zona eliminada exitosamente');
                            cargarZonas();
                        } else {
                            alert('Error al eliminar la zona: ' + (data.error || 'Desconocido'));
                        }
                    })
                    .catch(error => alert('Error al eliminar la zona: ' + error.message));
            }
        }

        function guardarZona(e) {
            e.preventDefault();
            const form = e.target;
            const tarifa = form.tarifa.value;

            // Validación en el frontend
            if (tarifa < 0) {
                alert('La tarifa base no puede ser negativa');
                return;
            }

            const formData = new FormData(form);

            fetch('../servicios/guardar_zona.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Zona guardada exitosamente');
                        closeModal('modalZona');
                        cargarZonas();
                    } else {
                        alert('Error al guardar la zona: ' + (data.error || 'Desconocido'));
                    }
                })
                .catch(error => alert('Error al guardar la zona: ' + error.message));
        }

        function removeAccents(str) {
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        function cargarZonas() {
            const search = removeAccents(document.getElementById('searchInput').value.toLowerCase());
            const estado = document.getElementById('filterStatus').value;

            fetch('../servicios/obtener_zonas.php')
                .then(res => res.json())
                .then(zonas => {
                    const tbody = document.getElementById('zonasTableBody');
                    tbody.innerHTML = '';

                    zonas.forEach(z => {
                        const nombreZona = removeAccents(z.nombre.toLowerCase());
                        const ciudadZona = removeAccents(z.ciudad.toLowerCase());
                        const estadoZona = z.estado;

                        if ((nombreZona.includes(search) || ciudadZona.includes(search) || !search) && (!estado || estado === estadoZona)) {
                            const tr = document.createElement('tr');
                            tr.innerHTML = `
                                <td>${z.id_zona}</td>
                                <td>${z.nombre}</td>
                                <td>${z.ciudad}</td>
                                <td>$${parseInt(z.tarifa_base).toLocaleString()}</td>
                                <td><span class="estado-${estadoZona}">${estadoZona.charAt(0).toUpperCase() + estadoZona.slice(1)}</span></td>
                                <td>
                                    <button class="btn btn-editar" onclick="editarZona(${z.id_zona})"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-eliminar" onclick="eliminarZona(${z.id_zona})"><i class="fas fa-trash-alt"></i></button>
                                </td>`;
                            tbody.appendChild(tr);
                        }
                    });
                })
                .catch(error => alert('Error al cargar las zonas: ' + error.message));
        }
    </script>
</body>

</html>