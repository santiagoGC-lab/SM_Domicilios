<?php
require_once '../servicios/verificar_permisos.php';
verificarAcceso('zonas');
$nombreCompleto = obtenerNombreUsuario();
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
            <a href="vehiculos.php" class="menu-item">
                <i class="fas fa-car"></i>
                <span class="menu-text">Vehiculos</span>
            </a>
            <?php endif; ?>
            <?php if (tienePermiso('zonas')): ?>
            <a href="zonas.php" class="menu-item active">
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
            <a href="tabla_usuarios.php" class="menu-item">
                <i class="fas fa-users-cog"></i>
                <span class="menu-text">Gestionar Usuarios</span>
            </a>
            <?php endif; ?>
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar zona o Barrio..." oninput="cargarZonas()">
                    </div>
                    <select id="filterStatus" class="filter-select" onchange="cargarZonas()">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
                <button class="btn-login" onclick="abrirModalNueva()">
                    <i class="fas fa-plus"></i> Nueva Zona
                </button>
            </div>
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Nombre de Zona</th>
                            <th>Barrio</th>
                            <th>Tarifa Base</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="zonasTableBody"></tbody>
                </table>
                <div id="paginationZonas" class="pagination"></div>
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
                    <label for="barrio">Barrio:</label>
                    <input type="text" id="barrio" name="barrio" class="form-control" required placeholder="Ej. La ciudadela">
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
        const rowsPerPage = 5;
        let currentPage = 1;
        let totalZonas = 0;

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('formZona').addEventListener('submit', guardarZona);
            cargarZonas(currentPage);
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
            fetch(`../servicios/zonas.php`, {
                method: 'POST',
                body: (() => { const fd = new FormData(); fd.append('accion', 'obtener_por_id'); fd.append('id', id); return fd; })()
            })
                .then(res => {
                    if (res.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    return res.json();
                })
                .then(zona => {
                    if (zona.error) {
                        alert('Error: ' + zona.error);
                        return;
                    }
                    document.getElementById('modalTitle').textContent = 'Editar Zona';
                    document.getElementById('zonaId').value = zona.id_zona;
                    document.getElementById('nuevoNombre').value = zona.nombre;
                    document.getElementById('barrio').value = zona.barrio;
                    document.getElementById('tarifa').value = zona.tarifa_base;
                    document.getElementById('estado').value = zona.estado;
                    document.getElementById('modalZona').style.display = 'block';
                })
                .catch(error => {
                    if (error !== 'Sesión expirada') {
                        alert('Error al cargar la zona: ' + error.message);
                    }
                });
        }

        function eliminarZona(id) {
            if (confirm('¿Estás seguro de que deseas eliminar esta zona?')) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('accion', 'eliminar');

                fetch('../servicios/zonas.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => {
                        if (res.status === 401) {
                            alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                            window.location.href = '../vistas/login.html';
                            return Promise.reject('Sesión expirada');
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Zona eliminada exitosamente');
                            cargarZonas(); // Recargar la página actual
                        } else {
                            alert('Error al eliminar la zona: ' + (data.error || 'Desconocido'));
                        }
                    })
                    .catch(error => {
                        if (error !== 'Sesión expirada') {
                            alert('Error al eliminar la zona: ' + error.message);
                        }
                    });
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
            formData.append('accion', 'guardar');

            fetch('../servicios/zonas.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => {
                    if (res.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Zona guardada exitosamente');
                        closeModal('modalZona');
                        cargarZonas();
                    } else {
                        alert('Error al guardar la zona: ' + (data.error || 'Desconocido'));
                    }
                })
                .catch(error => {
                    if (error !== 'Sesión expirada') {
                        alert('Error al guardar la zona: ' + error.message);
                    }
                });
        }

        function removeAccents(str) {
            return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        }

        function cargarZonas(page = 1) {
            const search = document.getElementById('searchInput').value.trim().toLowerCase();
            const estado = document.getElementById('filterStatus').value;
            fetch('../servicios/zonas.php', {
                method: 'POST',
                body: (() => {
                    const fd = new FormData();
                    fd.append('accion', 'paginar');
                    fd.append('pagina', page);
                    fd.append('por_pagina', rowsPerPage);
                    return fd;
                })()
            })
            .then(res => res.json())
            .then(data => {
                console.log('Respuesta paginación zonas:', data); // DEPURACIÓN
                totalZonas = data.total;
                renderZonas(data.zonas, search, estado);
                renderPaginationZonas(page);
            })
            .catch(error => {
                alert('Error al cargar zonas');
                console.error(error);
            });
        }

        function renderZonas(zonas, search, estado) {
            const tbody = document.getElementById('zonasTableBody');
            let html = '';
            zonas.filter(z => {
                const matchSearch =
                    z.nombre.toLowerCase().includes(search) ||
                    z.barrio.toLowerCase().includes(search);
                const matchEstado = !estado || z.estado === estado;
                return matchSearch && matchEstado;
            }).forEach(z => {
                html += `
                <tr>
                    <td>${z.nombre}</td>
                    <td>${z.barrio}</td>
                    <td>$${parseFloat(z.tarifa_base).toFixed(2)}</td>
                    <td><span class="estado-${z.estado}">${z.estado.charAt(0).toUpperCase() + z.estado.slice(1)}</span></td>
                    <td>
                        <button class="btn btn-editar" onclick="editarZona(${z.id_zona})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-eliminar" onclick="eliminarZona(${z.id_zona})"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function renderPaginationZonas(page) {
            const pagination = document.getElementById('paginationZonas');
            const totalPages = Math.ceil(totalZonas / rowsPerPage);
            let html = '';
            if (totalPages > 1) {
                html += `<button onclick="cargarZonas(1)" ${page === 1 ? 'disabled' : ''}>Primera</button>`;
                html += `<button onclick="cargarZonas(${page - 1})" ${page === 1 ? 'disabled' : ''}>Anterior</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    if (i === page) {
                        html += `<button class="active">${i}</button>`;
                    } else {
                        html += `<button onclick="cargarZonas(${i})">${i}</button>`;
                    }
                }
                html += `<button onclick="cargarZonas(${page + 1})" ${page === totalPages ? 'disabled' : ''}>Siguiente</button>`;
                html += `<button onclick="cargarZonas(${totalPages})" ${page === totalPages ? 'disabled' : ''}>Última</button>`;
            }
            pagination.innerHTML = html;
        }

        // Manejo global de errores de sesión para todos los fetch
        (function() {
            const originalFetch = window.fetch;
            window.fetch = function() {
                return originalFetch.apply(this, arguments).then(response => {
                    if (response.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    return response;
                });
            };
        })();
    </script>
</body>

</html>