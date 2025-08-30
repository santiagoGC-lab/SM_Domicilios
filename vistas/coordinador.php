<?php
require_once '../servicios/verificar_permisos.php';
verificarAcceso('coordinador');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SM - Gestión de Despachos</title>
    <link rel="shortcut icon" href="../componentes/img/logo2.png" />
    <link rel="stylesheet" href="../componentes/dashboard.css">
    <link rel="stylesheet" href="../componentes/pedidos.css">
    <link rel="stylesheet" href="../componentes/coordinador.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Sidebar de navegación -->
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
                <a href="coordinador.php" class="menu-item active">
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
    <!-- Contenido principal -->
    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2>Gestión de Despachos</h2>
            </div>
            <!-- Se elimina el botón de ver domiciliarios en ruta -->
        </div>
        <div class="recent-activity">
            <!-- Controles de paginación superiores -->
            <div class="pagination-controls" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <!-- Información de paginación removida -->
            </div>
            <div class="orders-table">
                <!-- Tabla de pedidos pendientes de despacho -->
                <table id="tablaDespachos">
                    <thead>
                        <tr>
                            <th>Clientes</th>
                            <th>Telefono</th>
                            <th>Dirección</th>
                            <th>Barrio</th>
                            <th>Zona</th>
                            <th>Num-Paq</th>
                            <th>Alistamiento</th>
                            <th>Inmediato?</th>
                            <th>Estimado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aquí se cargan los pedidos pendientes vía JS -->
                    </tbody>
                </table>
            </div>

            <!-- Controles de paginación inferiores -->
            <div id="paginationPedidos" class="pagination"></div>
        </div>
    </div>
    <!-- Modal para despachar pedido -->
    <div id="modalDespacho" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModalDespacho()">×</span>
            <h2>Despachar pedido</h2>
            <form id="formDespacho">
                <input type="hidden" id="modal_id_pedido">
                <div class="form-group">
                    <label for="modal_repartidor">Repartidor:</label>
                    <select id="modal_repartidor" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label for="modal_vehiculo">Vehículo:</label>
                    <select id="modal_vehiculo" class="form-control" required></select>
                </div>
                <button type="submit" class="btn-login">Confirmar despacho</button>
                <button type="button" class="btn-login" onclick="cerrarModalDespacho()">Cancelar</button>
            </form>
        </div>
    </div>
    <!-- Panel fijo para pedidos en ruta, siempre visible y abajo a la derecha -->
    <div id="panelEnRuta" style="position:fixed; bottom:30px; right:30px; width:400px; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.15); z-index:1000; border-radius:8px; padding:16px;">
        <h3>Domiciliarios en ruta</h3>
        <table id="tablaEnRuta">
            <thead>
                <tr>
                    <th>Domiciliario</th>
                    <th>Salida</th>
                    <th>Llegada</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aquí se cargan los pedidos en ruta vía JS -->
            </tbody>
        </table>
    </div>
    <script>
        // Variables de paginación
        let paginaActual = 1;
        let totalPaginas = 1;
        let totalPedidos = 0;
        let porPagina = 5;

        // Al cargar la página, inicializa la interfaz
        document.addEventListener('DOMContentLoaded', function() {
            cargarPedidosPendientes();
            cargarDomiciliariosEnRuta();
        });

        // Carga los pedidos pendientes de despacho con paginación
        function cargarPedidosPendientes(pagina = 1) {
            const formData = new FormData();
            formData.append('accion', 'pendientes_despacho');
            formData.append('pagina', pagina);
            formData.append('por_pagina', porPagina);

            fetch('../servicios/pedidos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    // Actualizar variables de paginación
                    paginaActual = data.pagina_actual;
                    totalPaginas = data.total_paginas;
                    totalPedidos = data.total;

                    // Actualizar tabla
                    const tbody = document.querySelector('#tablaDespachos tbody');
                    tbody.innerHTML = '';

                    // Por cada pedido pendiente, crea una fila
                    data.pedidos.forEach(pedido => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                        <td>${pedido.cliente || 'N/A'}</td>
                        <td>${pedido.telefono || 'N/A'}</td>
                        <td>${pedido.direccion || 'N/A'}</td>
                        <td>${pedido.barrio || 'N/A'}</td>
                        <td>${pedido.zona || 'N/A'}</td>
                        <td>${pedido.cantidad_paquetes || 'N/A'}</td>
                        <td>${pedido.alistamiento || 'N/A'}</td>
                        <td>${pedido.envio_inmediato || 'N/A'}</td>
                        <td>${pedido.hora_estimada_entrega ? new Date('1970-01-01T' + pedido.hora_estimada_entrega).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit', hour12: true}) : 'N/A'}</td>
                        <td>
                            <button class="btn-despachar-mini" onclick="abrirModalDespacho(${pedido.id_pedido})" title="Despachar pedido">
                                <i class="fas fa-truck"></i>
                            </button>
                        </td>
                    `;
                        tbody.appendChild(tr);
                    });

                    // Actualizar controles de paginación
                    actualizarControlesPaginacion();
                })
                .catch(error => {
                    alert('Error al cargar pedidos pendientes: ' + error.message);
                });
        }

        // Actualizar controles de paginación
        function actualizarControlesPaginacion() {
            // Información de paginación
            const inicio = (paginaActual - 1) * porPagina + 1;
            const fin = Math.min(paginaActual * porPagina, totalPedidos);
            // Comentar o remover estas líneas:
            // document.getElementById('paginationInfo').textContent =
            //     `Mostrando ${inicio}-${fin} de ${totalPedidos} pedidos`;

            // Renderizar solo la paginación estándar
            renderPaginacion(paginaActual);
        }

        // Renderiza los controles de paginación estilo estándar
        function renderPaginacion(page) {
            const pagination = document.getElementById('paginationPedidos');
            const totalPages = Math.ceil(totalPedidos / porPagina) || 1;
            let html = '';
            if (totalPages > 1) {
                html += `<button onclick="irAPagina(1)" ${page===1?'disabled':''}><i class="fas fa-angle-double-left"></i> Primera</button>`;
                html += `<button onclick="irAPagina(${page-1})" ${page===1?'disabled':''}><i class="fas fa-angle-left"></i> Anterior</button>`;
                for (let i = 1; i <= totalPages; i++) {
                    html += `<button onclick="irAPagina(${i})" ${page===i?'class="active"':''}>${i}</button>`;
                }
                html += `<button onclick="irAPagina(${page+1})" ${page===totalPages?'disabled':''}> Siguiente <i class="fas fa-angle-right"></i></button>`;
                html += `<button onclick="irAPagina(${totalPages})" ${page===totalPages?'disabled':''}>Última <i class="fas fa-angle-double-right"></i></button>`;
            }
            pagination.innerHTML = html;
        }

        // Ir a una página específica
        function irAPagina(pagina) {
            if (pagina >= 1 && pagina <= totalPaginas) {
                cargarPedidosPendientes(pagina);
            }
        }

        // Cambiar tamaño de página
        function cambiarTamañoPagina() {
            porPagina = parseInt(document.getElementById('pageSize').value);
            paginaActual = 1;
            cargarPedidosPendientes(1);
        }

        // Abre el modal para despachar un pedido y carga domiciliarios y vehículos disponibles
        function abrirModalDespacho(id_pedido) {
            document.getElementById('modal_id_pedido').value = id_pedido;
            cargarDomiciliariosDisponibles();
            cargarVehiculosDisponibles();
            document.getElementById('modalDespacho').classList.add('active');
        }

        // Cierra el modal de despacho
        function cerrarModalDespacho() {
            document.getElementById('modalDespacho').classList.remove('active');
            document.getElementById('formDespacho').reset();
        }

        // Carga domiciliarios disponibles en el select del modal
        function cargarDomiciliariosDisponibles() {
            fetch('../servicios/domiciliarios.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        accion: 'disponibles'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    const select = document.getElementById('modal_repartidor');
                    select.innerHTML = '<option value="">Seleccione un repartidor</option>';
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    data.forEach(domiciliario => {
                        const option = document.createElement('option');
                        option.value = domiciliario.id_domiciliario;
                        option.textContent = domiciliario.nombre;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    alert('Error al cargar domiciliarios: ' + error.message);
                });
        }

        // Carga vehículos disponibles en el select del modal
        function cargarVehiculosDisponibles() {
            fetch('../servicios/vehiculos.php', {
                    method: 'POST',
                    body: new URLSearchParams({
                        accion: 'disponibles'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    const select = document.getElementById('modal_vehiculo');
                    select.innerHTML = '<option value="">Seleccione un vehículo</option>';
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    data.forEach(vehiculo => {
                        const option = document.createElement('option');
                        option.value = vehiculo.id_vehiculo;
                        option.textContent = `${vehiculo.tipo} - ${vehiculo.placa}`;
                        select.appendChild(option);
                    });
                })
                .catch(error => {
                    alert('Error al cargar vehículos: ' + error.message);
                });
        }

        // Envía el formulario para despachar un pedido
        document.getElementById('formDespacho').addEventListener('submit', function(e) {
            e.preventDefault();
            const id_pedido = document.getElementById('modal_id_pedido').value;
            const id_domiciliario = document.getElementById('modal_repartidor').value;
            const id_vehiculo = document.getElementById('modal_vehiculo').value;

            if (!id_domiciliario || !id_vehiculo) {
                alert('Por favor, seleccione un domiciliario y un vehículo.');
                return;
            }

            const formData = new FormData();
            formData.append('accion', 'despachar');
            formData.append('id_pedido', id_pedido);
            formData.append('id_domiciliario', id_domiciliario);
            formData.append('id_vehiculo', id_vehiculo);

            fetch('../servicios/pedidos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pedido despachado exitosamente');
                        cerrarModalDespacho();
                        cargarPedidosPendientes();
                        cargarDomiciliariosEnRuta();
                    } else {
                        alert('Error: ' + (data.message || data.error));
                    }
                })
                .catch(error => {
                    alert('Error al despachar el pedido: ' + error.message);
                });
        });

        // Carga los pedidos en ruta en el panel flotante
        function cargarDomiciliariosEnRuta() {
            const formData = new FormData();
            formData.append('accion', 'en_ruta');
            fetch('../servicios/pedidos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#tablaEnRuta tbody');
                    tbody.innerHTML = '';
                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }
                    // Por cada pedido en ruta, crea una fila
                    data.forEach(pedido => {
                        const horaSalida = pedido.hora_salida ? new Date(pedido.hora_salida).toLocaleString('es-ES', {
                            timeStyle: 'short'
                        }) : '-';
                        const horaLlegada = pedido.hora_llegada ? new Date(pedido.hora_llegada).toLocaleString('es-ES', {
                            timeStyle: 'short'
                        }) : '-';
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                        <td>${pedido.domiciliario || 'No asignado'}</td>
                        <td>${horaSalida}</td>
                        <td>${horaLlegada}</td>
                        <td>
                            <button class="btn-llego" onclick="marcarLlegada(${pedido.id_pedido})" title="Marcar llegada">
                                <i class="fas fa-check"></i> Llegó
                            </button>
                        </td>
                    `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => {
                    alert('Error al cargar domiciliarios en ruta: ' + error.message);
                });
        }

        // Marca la llegada de un pedido (cambia estado y hora de llegada)
        function marcarLlegada(id_pedido) {
            if (confirm('¿Confirmar que el pedido ha llegado?')) {
                // Deshabilitar el botón para evitar múltiples clics
                const button = event.target.closest('button');
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

                const formData = new FormData();
                formData.append('accion', 'marcar_llegada');
                formData.append('id_pedido', id_pedido);
                fetch('../servicios/pedidos.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Pedido marcado como entregado');
                            cargarDomiciliariosEnRuta(); // Recargar la tabla
                        } else {
                            alert('Error: ' + (data.error || 'Error desconocido'));
                            // Rehabilitar el botón si hay error
                            button.disabled = false;
                            button.innerHTML = '<i class="fas fa-check"></i> Llegó';
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                        // Rehabilitar el botón si hay error
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-check"></i> Llegó';
                    });
            }
        }

        // Ya no se necesita la función togglePanelEnRuta porque el panel siempre está visible
    </script>
</body>

</html>