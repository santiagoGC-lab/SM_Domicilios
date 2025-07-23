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
            <a href="coordinador.php" class="menu-item active">
                <i class="fas fa-truck"></i>
                <span class="menu-text">Coordinador</span>
            </a>
            <a href="clientes.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span class="menu-text">Clientes</span>
            </a>
            <a href="reportes.php" class="menu-item">
                <i class="fas fa-chart-bar"></i>
                <span class="menu-text">Reportes</span>
            </a>
            <a href="../servicios/cerrar_sesion.php" class="menu-cerrar">
                <i class="fas fa-sign-out-alt"></i>
                <span class="menu-text">Cerrar Sesión</span>
            </a>
        </div>
    </div>
    <div class="main-content">
        <div class="header">
            <div class="header-left">
                <h2>Gestión de Despachos</h2>
            </div>
            <div class="action-buttons">
                <button class="btn-login" id="btnVerEnRuta">
                    <i class="fas fa-route"></i> Ver domiciliarios en ruta
                </button>
            </div>
        </div>
        <div class="recent-activity">
            <div class="orders-table">
                <table id="tablaDespachos">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Barrio</th>
                            <th>Zona</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
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
    <div id="panelEnRuta">
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
            </tbody>
        </table>
    </div>
    <script>
        // Cargar pedidos pendientes de despacho al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            cargarPedidosPendientes();
            cargarDomiciliariosEnRuta();
            document.getElementById('btnVerEnRuta').addEventListener('click', togglePanelEnRuta);
        });

        // Función para cargar pedidos pendientes de despacho
        function cargarPedidosPendientes() {
            fetch('../servicios/pedidos.php', {
                    method: 'POST',
                    body: new FormData().append('accion', 'pendientes_despacho')
                })
                .then(response => {
                    if (response.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    if (!response.ok) {
                        throw new Error('Error en la solicitud: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    const tbody = document.querySelector('#tablaDespachos tbody');
                    tbody.innerHTML = '';
                    data.forEach(pedido => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                <td>#${pedido.id_pedido}</td>
                <td>${pedido.cliente}</td>
                <td>${pedido.barrio}</td>
                <td>${pedido.zona}</td>
                <td>
                    <button class="btn-despachar" onclick="abrirModalDespacho(${pedido.id_pedido})" title="Despachar">
                        <i class="fas fa-truck"></i> Despachar
                    </button>
                </td>
            `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => {
                    if (error !== 'Sesión expirada') {
                        console.error('Error:', error);
                        alert('Error al cargar pedidos pendientes: ' + error.message);
                    }
                });
        }

        // Función para abrir el modal de despacho y cargar domiciliarios y vehículos disponibles
        function abrirModalDespacho(id_pedido) {
            const modal = document.getElementById('modalDespacho');
            const selectRepartidor = document.getElementById('modal_repartidor');
            const selectVehiculo = document.getElementById('modal_vehiculo');
            document.getElementById('modal_id_pedido').value = id_pedido;

            // Cargar domiciliarios disponibles
            fetch('/SM_Domicilios/servicios/domiciliarios.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: new URLSearchParams({
                        accion: 'obtener_disponibles'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.error) throw new Error(data.error);
                    selectRepartidor.innerHTML = '<option value="">Seleccione un repartidor</option>';
                    data.forEach(domiciliario => {
                        const option = document.createElement('option');
                        option.value = domiciliario.id_domiciliario;
                        option.textContent = domiciliario.nombre;
                        selectRepartidor.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar domiciliarios: ' + error.message);
                });

            // Cargar vehículos disponibles
            fetch('/SM_Domicilios/servicios/vehiculos.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: new URLSearchParams({
                        accion: 'obtener_disponibles'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.error) throw new Error(data.error);
                    selectVehiculo.innerHTML = '<option value="">Seleccione un vehículo</option>';
                    data.forEach(vehiculo => {
                        const option = document.createElement('option');
                        option.value = vehiculo.id_vehiculo;
                        option.textContent = `${vehiculo.tipo} - ${vehiculo.placa}`;
                        selectVehiculo.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar vehículos: ' + error.message);
                });

            modal.classList.add('active');
        }

        // Función para cerrar el modal de despacho
        function cerrarModalDespacho() {
            document.getElementById('modalDespacho').classList.remove('active');
            document.getElementById('formDespacho').reset();
        }

        // Función para despachar un pedido
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
                .then(response => {
                    if (response.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    if (!response.ok) {
                        throw new Error('Error en la solicitud: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert('Pedido despachado exitosamente');
                        cerrarModalDespacho();
                        cargarPedidosPendientes();
                        cargarDomiciliariosEnRuta();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    if (error !== 'Sesión expirada') {
                        console.error('Error:', error);
                        alert('Error al despachar el pedido: ' + error.message);
                    }
                });
        });

        // Función para cargar domiciliarios en ruta
        function cargarDomiciliariosEnRuta() {
            fetch('../servicios/pedidos.php', {
                    method: 'POST',
                    body: new FormData().append('accion', 'en_ruta')
                })
                .then(response => {
                    if (response.status === 401) {
                        alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                        window.location.href = '../vistas/login.html';
                        return Promise.reject('Sesión expirada');
                    }
                    if (!response.ok) {
                        throw new Error('Error en la solicitud: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    const tbody = document.querySelector('#tablaEnRuta tbody');
                    tbody.innerHTML = '';
                    data.forEach(pedido => {
                        const tr = document.createElement('tr');
                        const horaSalida = new Date(pedido.hora_salida).toLocaleString('es-ES', {
                            dateStyle: 'short',
                            timeStyle: 'short'
                        });
                        const horaLlegada = pedido.hora_llegada ? new Date(pedido.hora_llegada).toLocaleString('es-ES', {
                            dateStyle: 'short',
                            timeStyle: 'short'
                        }) : '-';
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
                    if (error !== 'Sesión expirada') {
                        console.error('Error:', error);
                        alert('Error al cargar domiciliarios en ruta: ' + error.message);
                    }
                });
        }

        // Función para marcar la llegada de un pedido
        function marcarLlegada(id_pedido) {
            if (confirm('¿Confirmar que el pedido ha llegado?')) {
                const formData = new FormData();
                formData.append('accion', 'marcar_llegada');
                formData.append('id_pedido', id_pedido);
                fetch('../servicios/pedidos.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (response.status === 401) {
                            alert('Sesión expirada. Por favor, inicia sesión nuevamente.');
                            window.location.href = '../vistas/login.html';
                            return Promise.reject('Sesión expirada');
                        }
                        if (!response.ok) {
                            throw new Error('Error en la solicitud: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Llegada marcada exitosamente');
                            cargarDomiciliariosEnRuta();
                            cargarPedidosPendientes();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        if (error !== 'Sesión expirada') {
                            console.error('Error:', error);
                            alert('Error al marcar llegada: ' + error.message);
                        }
                    });
            }
        }

        // Función para mostrar/ocultar el panel de domiciliarios en ruta
        function togglePanelEnRuta() {
            const panel = document.getElementById('panelEnRuta');
            panel.style.display = panel.style.display === 'none' || panel.style.display === '' ? 'block' : 'none';
        }
    </script>
</body>

</html>