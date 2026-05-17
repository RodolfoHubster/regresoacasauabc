<?php include 'php/auth_check.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participantes del Evento - UABC</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/participantes.css">
</head>
<body class="admin-body">

    <header class="p-header">
        <div class="p-container">
            <a href="admin.php" class="back-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Volver al Panel
            </a>
            <h1 id="event-title">Participantes</h1>
            <p id="event-meta">Cargando información del evento...</p>
        </div>
    </header>

    <main class="p-container">
        <div class="admin-table-wrapper card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Campus</th>
                        <th>Facultad</th> <th>Carrera</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody id="tabla-asistentes">
                    <tr><td colspan="5" style="text-align:center;">Cargando asistentes...</td></tr>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Obtener el ID del evento de la URL (ej: participantes.html?id=5)
            const params = new URLSearchParams(window.location.search);
            const id = params.get('id');

            if (!id) {
                alert("ID de evento no encontrado");
                window.location.href = 'admin.html';
                return;
            }

            fetch(`php/get_asistentes_evento.php?evento_id=${id}`)
                .then(res => res.json())
                .then(result => {
                    const tbody = document.getElementById('tabla-asistentes');
                    const title = document.getElementById('event-title');
                    
                    if (result.status === 'success') {
                        tbody.innerHTML = '';
                        
                        if (result.data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay personas registradas aún.</td></tr>';
                            return;
                        }

                        // Puedes opcionalmente pedir el nombre del evento en tu consulta PHP
                        // Por ahora llenamos la tabla
                        // Busca este bloque en participantes.html y actualízalo
                    // Dentro del fetch de cargarParticipantes...
                    // Busca esta parte en el script de participantes.html
                    result.data.forEach(asistente => {
                        // Validamos de dónde viene el nombre de la facultad y carrera
                        const nombreFacultad = asistente.facultad_nombre || asistente.facultad_otra || 'No especificada';
                        const nombreCarrera = asistente.carrera_nombre || asistente.carrera_otra || 'No especificada';

                        const row = `
                            <tr>
                                <td><strong>${asistente.apellidos}, ${asistente.nombre}</strong></td>
                                <td>${asistente.correo}</td>
                                <td>${asistente.campus_nombre || 'N/A'}</td>
                                <td>${nombreFacultad}</td> 
                                <td>${nombreCarrera}</td>
                                <td><span class="badge badge--gray">${asistente.tipo_asistente || 'N/A'}</span></td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                    } else {
                        tbody.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center;">${result.message}</td></tr>`;
                    }
                })
                .catch(err => {
                    console.error(err);
                    document.getElementById('tabla-asistentes').innerHTML = '<tr><td colspan="5" style="text-align:center;">Error de conexión.</td></tr>';
                });
        });
    </script>
</body>
</html>