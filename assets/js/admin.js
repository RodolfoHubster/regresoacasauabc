/* admin.js – Lógica del panel de administración */

// ─── NAVEGACIÓN ENTRE SECCIONES ───
const sectionTitles = {
  dashboard:  'Dashboard',
  eventos:    'Gestión de Eventos',
  registros:  'Asistentes Registrados',
  faq:        'Preguntas Frecuentes',
  qr:         'Validar QR de Acceso'
};

function showSection(name) {
  // ─── 1. CONTROL INTELIGENTE DE CÁMARA ───
  if (name !== 'qr') {
      // Si salimos de la pestaña, buscamos el botón de Stop y apagamos la cámara
      const btnStop = document.getElementById('html5-qrcode-button-camera-stop');
      if (btnStop && window.getComputedStyle(btnStop).display !== 'none') {
          btnStop.click();
      }
  } else {
      // Si entramos a la pestaña QR, creamos el escáner (solo la primera vez)
      if (typeof inicializarLectorQR === 'function') {
          inicializarLectorQR();
      }
  }

  // ─── 2. CAMBIO VISUAL DE SECCIONES ───
  document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));

  const sec = document.getElementById('section-' + name);
  if (sec) sec.classList.add('active');

  const link = document.querySelector('[data-section="' + name + '"]');
  if (link) link.classList.add('active');

  const title = document.getElementById('topbar-title');
  if (title) title.textContent = sectionTitles[name] || name;

  document.getElementById('admin-sidebar').classList.remove('open');
  const main = document.getElementById('admin-main');
  if (main) main.scrollTop = 0;

  if (name === 'registros') {
      if (typeof cargarTablaAsistentes === 'function') cargarTablaAsistentes();
  }
}

function toggleSidebar() {
  document.getElementById('admin-sidebar').classList.toggle('open');
}

// ─── MODALES ADMIN ───
function openAdminModal(id) {
  const overlay = document.getElementById(id);
  if (!overlay) return;
  overlay.hidden = false;
  document.body.style.overflow = 'hidden';
  overlay._esc = (e) => { if (e.key === 'Escape') closeAdminModal(id); };
  overlay._click = (e) => { if (e.target === overlay) closeAdminModal(id); };
  document.addEventListener('keydown', overlay._esc);
  overlay.addEventListener('click', overlay._click);
  const firstFocusable = overlay.querySelector('input, select, textarea, button:not(.modal-close)');
  if (firstFocusable) setTimeout(() => firstFocusable.focus(), 100);
}

function closeAdminModal(id) {
  const overlay = document.getElementById(id);
  if (!overlay) return;
  overlay.hidden = true;
  document.body.style.overflow = '';
  if (overlay._esc) document.removeEventListener('keydown', overlay._esc);
  if (overlay._click) overlay.removeEventListener('click', overlay._click);
}

// Variable global para guardar los eventos
let eventosCargados = [];

function openModalEvento(id) {
  const title = document.getElementById('modal-evento-title');
  const form = document.getElementById('form-evento');
  form.reset(); 
  
  const idInput = document.getElementById('ev-id');
  if (idInput) idInput.value = ""; 

  if (id) {
    title.textContent = 'Editar Evento';
    const evento = eventosCargados.find(e => e.id == id);
    if (evento) {
      if (idInput) idInput.value = evento.id;
      document.getElementById('field-campus-evento').value = evento.campus_id || '';
      document.getElementById('ev-nombre').value = evento.nombre;
      document.getElementById('ev-descripcion').value = evento.descripcion;
      document.getElementById('ev-fecha').value = evento.fecha;
      document.getElementById('ev-hora').value = evento.hora;
      document.getElementById('ev-ubicacion').value = evento.ubicacion;
      // Limpiamos el input de archivo por si había algo seleccionado antes
      document.getElementById('ev-imagen').value = ""; 
      // Guardamos la URL de la imagen actual en el campo oculto
      document.getElementById('ev-imagen-actual').value = evento.imagen || '';
      document.getElementById('ev-estado').value = evento.estado;
    }
  } else {
    title.textContent = 'Nuevo Evento';
  }
  openAdminModal('modal-evento');
}

function openModalRecordatorio(evento) {
  const label = document.getElementById('modal-rec-evento');
  if (label) label.textContent = ' ' + evento;
  openAdminModal('modal-recordatorio');
}

function openModalAsistente(nombre) {
  const nameEl = document.getElementById('asi-nombre');
  if (nameEl) nameEl.textContent = nombre;
  openAdminModal('modal-asistente');
}

function openModalFaq(key) {
  const title = document.getElementById('modal-faq-title');
  if (title) title.textContent = key ? 'Editar Pregunta' : 'Nueva Pregunta Frecuente';
  openAdminModal('modal-faq');
}

function confirmarEliminarFaq(id) {
  if (confirm('¿Eliminar esta pregunta frecuente? Esta acción no se puede deshacer.')) {
    alert('Pregunta #' + id + ' eliminada (simulación). Requiere backend PHP.');
  }
}

// ─── FUNCIONES DE QR ───

// Función para cuando se CREA un evento nuevo
function showSuccessWithQR(eventId) {
  const container = document.getElementById('qrcode-container');
  const linkInput = document.getElementById('event-link-input');
  
  // Extraemos solo el número del ID (ej. de EVT-2026-5 sacamos el 5)
  const soloId = eventId.split('-').pop();
  
  container.innerHTML = "";
  
  // Forzamos la ruta de tu carpeta específica
  const eventUrl = `${window.location.origin}/regresaacasa/index.html?evento=${soloId}`;
  linkInput.value = eventUrl;

  new QRCode(container, {
    text: eventUrl,
    width: 180,
    height: 180,
    colorDark : "#002855",
    colorLight : "#ffffff",
    correctLevel : QRCode.CorrectLevel.H
  });

  closeAdminModal('modal-evento');
  openAdminModal('modal-evento-exito');
}

// Función para el botón "Ver QR"
function verQR(id, nombre) {
  const container = document.getElementById('qrcode-container');
  const linkInput = document.getElementById('event-link-input');

  container.innerHTML = "";
  
  // Usamos directamente el ID numérico y la carpeta del proyecto
  const eventUrl = `${window.location.origin}/regresaacasa/index.html?evento=${id}`;
  linkInput.value = eventUrl;

  new QRCode(container, {
    text: eventUrl,
    width: 180,
    height: 180,
    colorDark : "#002855",
    colorLight : "#ffffff",
    correctLevel : QRCode.CorrectLevel.H
  });

  openAdminModal('modal-evento-exito');
}

function copyEventLink() {
  const linkInput = document.getElementById('event-link-input');
  linkInput.select();
  document.execCommand('copy');
  alert('¡Link copiado!');
}

function downloadQR() {
  const qrImage = document.querySelector('#qrcode-container img');
  if (qrImage) {
    const link = document.createElement('a');
    link.download = 'QR-Evento-UABC.png';
    link.href = qrImage.src;
    link.click();
  }
}

// ─── CARGAR EVENTOS DESDE LA BASE DE DATOS ───
function loadEventos() {
  const container = document.getElementById('events-container');
  if (!container) return;

  fetch('php/get_eventos.php')
    .then(res => res.json())
    .then(result => {
      if (result.status === 'success') {
        eventosCargados = result.data; 
        container.innerHTML = ""; 
        
        if (result.data.length === 0) {
          container.innerHTML = "<p>No hay eventos registrados.</p>";
          return;
        }

        // Dentro de loadEventos en assets/js/admin.js
        result.data.forEach(evento => {
          const card = `
            <div class="event-admin-card ${evento.estado === 'cerrado' ? 'event-admin-card--inactive' : ''}">
              <div class="event-admin-img" style="background-image:url('${evento.imagen || '../assets/images/download.jpg'}');"></div>
              <div class="event-admin-body">
                <div class="event-admin-top">
                  <h3 class="event-admin-title">${evento.nombre}</h3>
                  <span class="badge ${evento.estado === 'activo' ? 'badge--green' : 'badge--gray'}">${evento.estado}</span>
                </div>
                <p class="event-admin-meta">${evento.fecha} · ${evento.ubicacion}</p>
                <div class="event-admin-actions">
                  <button class="btn btn-secondary btn-sm" onclick="openModalEvento(${evento.id})">Editar</button>
                  <button class="btn btn-secondary btn-sm" onclick="verQR(${evento.id}, '${evento.nombre}')">Ver QR</button>
                  <a href="participantes.php?id=${evento.id}" class="btn btn-secondary btn-sm">Participantes</a>
                  <button class="btn btn-secondary btn-sm" onclick="openModalRecordatorio('${evento.nombre}')">Recordatorio</button>
                  <button class="btn btn-secondary btn-sm btn-delete-hover" onclick="confirmarEliminar(${evento.id}, '${evento.nombre}')">Eliminar</button>
                </div>
              </div>
            </div>
          `;
          container.innerHTML += card;
        });
      }
    })
    .catch(err => console.error("Error al cargar eventos:", err));
}

// ─── FORMULARIOS (AL CARGAR LA PÁGINA) ───
document.addEventListener('DOMContentLoaded', () => {
  
  loadEventos();
  
  // 1. Manejo del formulario de EVENTOS
  const formEvento = document.getElementById('form-evento');
  if (formEvento) {
    formEvento.addEventListener('submit', async (e) => { // <-- Se agregó "async"
      e.preventDefault();
      
      const idInput = document.getElementById('ev-id');
      const id = idInput ? idInput.value : "";
      const btnSubmit = formEvento.querySelector('button[type="submit"]');
      const originalText = btnSubmit.textContent;

      btnSubmit.disabled = true;
      btnSubmit.textContent = 'Subiendo imagen...'; // Avisamos al usuario

      try {
          // Por defecto, tomamos la imagen que ya tenía (si estamos editando)
          let imageUrl = document.getElementById('ev-imagen-actual').value;

          // 1. ¿El usuario seleccionó un archivo nuevo?
          const fileInput = document.getElementById('ev-imagen');
          if (fileInput.files.length > 0) {
              const file = fileInput.files[0];
              
              // Preparamos los datos para Cloudinary
              const cloudinaryData = new FormData();
              cloudinaryData.append('file', file);
              
              cloudinaryData.append('upload_preset', window.AppConfig.cloudinaryPreset); 

              const cloudResponse = await fetch(`https://api.cloudinary.com/v1_1/${window.AppConfig.cloudinaryCloudName}/image/upload`, {
                    method: 'POST',
                    body: cloudinaryData
                });

              if (!cloudResponse.ok) throw new Error("Error al subir la imagen a Cloudinary");

              const cloudResult = await cloudResponse.json();
              imageUrl = cloudResult.secure_url; // ¡Obtenemos la URL de Cloudinary!
          }

          btnSubmit.textContent = id ? 'Actualizando BD...' : 'Guardando BD...';

          // 2. Ahora sí, preparamos los datos para TU base de datos
          const formData = new FormData();
          if (id) formData.append('id', id);
          formData.append('campus_id', document.getElementById('field-campus-evento').value);
          formData.append('nombre', document.getElementById('ev-nombre').value);
          formData.append('descripcion', document.getElementById('ev-descripcion').value);
          formData.append('fecha', document.getElementById('ev-fecha').value);
          formData.append('hora', document.getElementById('ev-hora').value);
          formData.append('ubicacion', document.getElementById('ev-ubicacion').value);
          formData.append('estado', document.getElementById('ev-estado').value);
          
          // Agregamos la URL final (la nueva de Cloudinary o la que ya tenía)
          formData.append('imagen', imageUrl);

          const url = id ? 'php/actualizar_evento.php' : 'php/crear_evento.php';

          const dbResponse = await fetch(url, { method: 'POST', body: formData });
          const dbData = await dbResponse.json();

          if (dbData.status === 'success') {
            if (!id) {
              showSuccessWithQR('EVT-' + dbData.eventId);
            } else {
              // 1. Cerramos el modal de edición
              closeAdminModal('modal-evento');
              
              // 2. Abrimos el modal de éxito con el mensaje personalizado
              const modalExito = document.getElementById('modal-exito');
              const msjExito = document.getElementById('mensaje-exito');
              if (modalExito && msjExito) {
                  msjExito.textContent = 'El evento se ha actualizado correctamente.';
                  modalExito.hidden = false;
              }
            }
            formEvento.reset();
            loadEventos(); 
          } else {
            alert('Error BD: ' + dbData.message);
          }
      } catch (error) {
          console.error('Error general:', error);
          alert('Hubo un problema: ' + error.message);
      } finally {
          // Devolvemos el botón a la normalidad pase lo que pase
          btnSubmit.disabled = false;
          btnSubmit.textContent = originalText;
      }
    });
  }
});   
// ─── CARGAR ESTADÍSTICAS DEL DASHBOARD ───
function cargarDashboardStats() {
  fetch('php/get_dashboard_stats.php')
    .then(response => response.json())
    .then(result => {
      if (result.status === 'success') {
        // Enlaza los datos de PHP con los atributos data-kpi de tu HTML
        const kpiRegistros = document.querySelector('[data-kpi="total-registros"]');
        const kpiEventos = document.querySelector('[data-kpi="total-eventos"]');
        const kpiConfirmados = document.querySelector('[data-kpi="confirmados"]');
        const kpiCorreos = document.querySelector('[data-kpi="correos"]');

        if (kpiRegistros) kpiRegistros.textContent = result.data.total_registros;
        if (kpiEventos) kpiEventos.textContent = result.data.eventos_activos;
        if (kpiConfirmados) kpiConfirmados.textContent = result.data.qr_confirmados;
        if (kpiCorreos) kpiCorreos.textContent = result.data.correos_enviados;
      } else {
        console.error('Error BD:', result.message);
      }
    })
    .catch(error => console.error('Error al cargar stats:', error));
}

// ─── CARGAR TABLA DE ASISTENTES (QR ESCANEADO) ───
// Variable global para guardar a los asistentes sin tener que consultar la BD a cada rato
let asistentesCargados = [];

// ─── CARGAR TABLA DE ASISTENTES ───
function cargarTablaAsistentes() {
  fetch('php/get_asistentes.php')
    .then(response => response.json())
    .then(result => {
      if (result.status === 'success') {
        asistentesCargados = result.data; // Guardamos en la variable global
        renderTablaAsistentes(asistentesCargados); // Dibujamos la tabla completa
        poblarFiltrosEventos(); // Llenamos los <select> con los nombres reales de los eventos
      } else {
        console.error('Error al cargar tabla:', result.message);
      }
    })
    .catch(error => console.error('Error de red:', error));
}

// ─── DIBUJAR LA TABLA (Con soporte para filtros) ───
function renderTablaAsistentes(datos) {
  const tbody = document.getElementById('tabla-asistentes-body');
  if (!tbody) return;
  tbody.innerHTML = ''; 

  if (datos.length === 0) {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No se encontraron registros para este filtro.</td></tr>';
      return;
  }

  datos.forEach(asistente => {
      const tr = document.createElement('tr');
      
      const tipoAsistente = asistente.tipo_asistente || 'N/A';
      const badgeTipo = tipoAsistente.toLowerCase().includes('docente') ? 'badge--gold' : 'badge--green';
      
      const qrEnviado = parseInt(asistente.correo_enviado) === 1;
      const badgeQR = qrEnviado 
          ? '<span class="badge badge--green">Enviado</span>' 
          : '<span class="badge badge--gold">Pendiente</span>';
          
      const yaAsistio = parseInt(asistente.asistencia) === 1;
      const badgeEstatus = yaAsistio 
          ? '<span class="badge badge--success">Asistió</span>' 
          : '<span class="badge badge--gray">Registrado</span>';

      const nombreEvento = asistente.evento_nombre || 'Evento no asignado';

      // NUEVO: Validamos de dónde viene el nombre de la carrera
      const nombreCarrera = asistente.carrera_nombre || asistente.carrera || asistente.carrera_otra || 'No especificada';

      tr.innerHTML = `
          <td>
            <div class="user-info">
              <div class="user-avatar" aria-hidden="true">${asistente.nombre.charAt(0)}${asistente.apellidos.charAt(0)}</div>
              <div><p class="user-name">${asistente.nombre} ${asistente.apellidos}</p></div>
            </div>
          </td>
          <td>${asistente.correo}</td>
          <td>${asistente.campus || 'N/A'}</td>
          <td>
             <span style="font-weight: 600;">${nombreCarrera}</span><br>
             <small style="color: #666;">Gen: ${asistente.generacion || 'N/A'}</small>
          </td>
          <td><span class="badge ${badgeTipo}">${tipoAsistente}</span></td>
          <td><small>${nombreEvento}</small></td>
          <td>${badgeQR}</td>
          <td>${badgeEstatus}</td>
      `;
      tbody.appendChild(tr);
  });
}

// ─── POBLAR LOS SELECTS DINÁMICAMENTE ───
function poblarFiltrosEventos() {
    // 1. Filtro de la tabla de asistentes
    const selectFiltro = document.getElementById('filtro-evento');
    if (selectFiltro && eventosCargados) {
        selectFiltro.innerHTML = '<option value="">Todos los eventos</option>';
        eventosCargados.forEach(evento => {
            const opt = document.createElement('option');
            opt.value = evento.nombre; 
            opt.textContent = evento.nombre;
            selectFiltro.appendChild(opt);
        });
    }

    // 2. Select del modal de crear/editar FAQ
    const selectFaqModal = document.getElementById('faq-evento-asoc');
    if (selectFaqModal && eventosCargados) {
        selectFaqModal.innerHTML = '<option value="">General (Aplica para todos)</option>';
        eventosCargados.forEach(evento => {
            const opt = document.createElement('option');
            opt.value = evento.id; 
            opt.textContent = `${evento.nombre} (${evento.campus_nombre})`;
            selectFaqModal.appendChild(opt);
        });
    }

    // 3. Filtro de la tabla de FAQs
    const selectFiltroFaq = document.getElementById('faq-filtro-evento');
    const selectFiltroFaqCampus = document.getElementById('faq-filtro-campus'); // Agregamos el de campus
    
    if (selectFiltroFaq && eventosCargados) {
        selectFiltroFaq.innerHTML = '<option value="">Todos los eventos</option><option value="general">Generales (Aplica a todos)</option>';
        eventosCargados.forEach(evento => {
            const opt = document.createElement('option');
            opt.value = evento.id; 
            opt.textContent = `${evento.nombre} (${evento.campus_nombre})`;
            selectFiltroFaq.appendChild(opt);
        });

        // Agregamos el "escuchador" a AMBOS filtros para que reaccionen al cambio
        selectFiltroFaq.addEventListener('change', aplicarFiltrosFAQ);
        if(selectFiltroFaqCampus) selectFiltroFaqCampus.addEventListener('change', aplicarFiltrosFAQ);
    }

    // 4. Filtro de facultad de la tabla de asistentes (carga dinámica según campus)
    const selectFacultad = document.getElementById('filtro-facultad');
    if (selectFacultad) {
        cargarFacultadesPorCampus('');
    }
}

// ─── LÓGICA DE MULTI-FILTROS PARA FAQ ───
function aplicarFiltrosFAQ() {
    const selectEvento = document.getElementById('faq-filtro-evento');
    const selectCampus = document.getElementById('faq-filtro-campus');
    
    const eventoSeleccionado = selectEvento ? selectEvento.value : "";
    const campusSeleccionado = selectCampus ? selectCampus.value : "";

    let filtradas = faqsCargadas;

    // 1. Filtro por Evento
    if (eventoSeleccionado === "general") {
        // Solo las que NO tienen evento asignado
        filtradas = filtradas.filter(faq => !faq.evento_id); 
    } else if (eventoSeleccionado !== "") {
        // Las del evento específico
        filtradas = filtradas.filter(faq => faq.evento_id == eventoSeleccionado);
    }

    // 2. Filtro por Campus
    if (campusSeleccionado !== "") {
        // Mostrar las del campus elegido Y las generales (porque las generales aplican a todos)
        filtradas = filtradas.filter(faq => faq.campus_nombre === campusSeleccionado || !faq.evento_id);
    }

    // Dibujamos la tabla con los resultados
    renderTablaFAQs(filtradas);
}

// ─── CARGA DINÁMICA DE FACULTADES POR CAMPUS ───
function cargarFacultadesPorCampus(campusNombre) {
    const selectFacultad = document.getElementById('filtro-facultad');
    if (!selectFacultad) return;

    const url = campusNombre
        ? `php/get_facultades_por_campus.php?campus=${encodeURIComponent(campusNombre)}`
        : 'php/get_facultades_por_campus.php';

    fetch(url)
        .then(r => r.json())
        .then(result => {
            selectFacultad.innerHTML = '<option value="">Todas las facultades</option>';
            if (result.status === 'success') {
                result.data.forEach(fac => {
                    const opt = document.createElement('option');
                    opt.value = fac.nombre;
                    opt.textContent = fac.nombre;
                    selectFacultad.appendChild(opt);
                });
            }
        })
        .catch(err => console.error('Error al cargar facultades:', err));
}

// ─── LÓGICA DE MULTI-FILTROS (EVENTO, CAMPUS Y FACULTAD) ───
document.addEventListener('DOMContentLoaded', () => {
    const selectEvento   = document.getElementById('filtro-evento');
    const selectCampus   = document.getElementById('filtro-campus');
    const selectFacultad = document.getElementById('filtro-facultad');

    if (selectEvento)   selectEvento.addEventListener('change', aplicarFiltros);
    if (selectFacultad) selectFacultad.addEventListener('change', aplicarFiltros);

    // Cuando cambia campus: recargamos las facultades de ese campus Y aplicamos filtros
    if (selectCampus) {
        selectCampus.addEventListener('change', () => {
            const campusVal = selectCampus.value;
            // Reset facultad al cambiar campus
            if (selectFacultad) selectFacultad.value = '';
            cargarFacultadesPorCampus(campusVal);
            aplicarFiltros();
        });
    }
});

function aplicarFiltros() {
    const selectEvento   = document.getElementById('filtro-evento');
    const selectCampus   = document.getElementById('filtro-campus');
    const selectFacultad = document.getElementById('filtro-facultad');
    
    const eventoSeleccionado   = selectEvento   ? selectEvento.value   : "";
    const campusSeleccionado   = selectCampus   ? selectCampus.value   : "";
    const facultadSeleccionada = selectFacultad ? selectFacultad.value : "";

    // Empezamos con todos los asistentes
    let filtrados = asistentesCargados;

    // 1. Filtrar por evento (si hay uno seleccionado)
    if (eventoSeleccionado !== "") {
        filtrados = filtrados.filter(a => a.evento_nombre === eventoSeleccionado);
    }

    // 2. Filtrar por campus (si hay uno seleccionado)
    if (campusSeleccionado !== "") {
        filtrados = filtrados.filter(a => a.campus === campusSeleccionado);
    }

    // 3. Filtrar por facultad (si hay una seleccionada)
    if (facultadSeleccionada !== "") {
        filtrados = filtrados.filter(a => a.facultad_nombre === facultadSeleccionada);
    }

    // Dibujamos la tabla con el resultado de los filtros
    renderTablaAsistentes(filtrados);
}

// ─── EXPORTAR A EXCEL: ASISTENTES (Con filtros: evento, campus, facultad) ───
function exportarExcelAsistentes() {
    const selectEvento   = document.getElementById('filtro-evento');
    const selectCampus   = document.getElementById('filtro-campus');
    const selectFacultad = document.getElementById('filtro-facultad');
    
    const evento   = selectEvento   ? selectEvento.value   : "";
    const campus   = selectCampus   ? selectCampus.value   : "";
    const facultad = selectFacultad ? selectFacultad.value : "";

    // Armamos la URL pasándole los tres filtros seleccionados
    const url = `php/exportar_asistentes.php?evento=${encodeURIComponent(evento)}&campus=${encodeURIComponent(campus)}&facultad=${encodeURIComponent(facultad)}`;
    
    // Redirigir inicia la descarga del archivo sin cambiar de página
    window.location.href = url;
}

// ─── EXPORTAR A EXCEL: DASHBOARD EVENTOS ───
function exportarExcelDashboard() {
    // Redirigimos directo al archivo PHP que genera el reporte de eventos
    window.location.href = 'php/exportar_dashboard.php';
}

// ─── MODIFICAR LA CARGA INICIAL ───
document.addEventListener('DOMContentLoaded', () => {
  // Primero cargamos las estadísticas de los cuadros de colores
  if (typeof cargarDashboardStats === 'function') {
      cargarDashboardStats();
  }
  
  // Cargamos la tabla de asistentes de la pestaña "Registros"
  if (typeof cargarTablaAsistentes === 'function') {
      cargarTablaAsistentes();
  }

  // NUEVO: Cargamos la tabla dinámica del Dashboard principal
  if (typeof cargarDashboardEventos === 'function') {
      cargarDashboardEventos();
  }
});

// Variable global para saber qué ID de evento vamos a borrar cuando se confirme la acción
let idEventoAEliminar = null;

// 1. Esta función se ejecuta al darle clic al botón "Eliminar" de la tarjeta del evento
function confirmarEliminar(id, nombre) {
    idEventoAEliminar = id; // Guardamos el ID temporalmente
    
    // Personalizamos el texto del modal dinámicamente con el nombre del evento
    const textoModal = document.getElementById('texto-confirmar-eliminar-evento');
    if (textoModal) {
        textoModal.innerHTML = `¿Estás seguro de que deseas eliminar el evento <strong>"${nombre}"</strong>? Esta acción no se puede deshacer y removerá permanentemente su imagen en Cloudinary.`;
    }
    
    openAdminModal('modal-confirmar-eliminar-evento'); // Abrimos el modal elegante
}

// 2. Esta función se ejecuta al darle clic al botón rojo "Eliminar" DENTRO del modal
async function ejecutarEliminarEvento() {
    if (!idEventoAEliminar) return;

    const btn = document.getElementById('btn-confirmar-eliminar-evento');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Eliminando...';
    btn.disabled = true;

    try {
        const response = await fetch('php/eliminar_evento.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: idEventoAEliminar })
        });
        
        if (!response.ok) throw new Error('Error en la respuesta del servidor');
        
        const result = await response.json();
        
        if (result.status === 'success') {
            closeAdminModal('modal-confirmar-eliminar-evento'); // Cerramos el modal de advertencia
            loadEventos(); // Recargamos instantáneamente las tarjetas del panel sin recargar la página
            
            // Mostramos el modal de éxito con la palomita verde que ya tienes integrado
            const modalExito = document.getElementById('modal-exito');
            const msjExito = document.getElementById('mensaje-exito');
            if (modalExito && msjExito) {
                msjExito.textContent = 'El evento y su imagen de Cloudinary han sido eliminados correctamente.';
                modalExito.hidden = false;
            }
        } else {
            alert('Error al eliminar: ' + result.message);
        }
    } catch (error) {
        console.error('Error al eliminar:', error);
        alert('Ocurrió un error al intentar conectar con el servidor.');
    } finally {
        // Regresamos el botón y la variable a su estado original
        btn.innerHTML = originalText;
        btn.disabled = false;
        idEventoAEliminar = null; 
    }
}

// ─── ENVIAR CORREOS DE RECORDATORIO ───
function enviarCorreosRecordatorio() {
    const tipoEnvio = document.querySelector('input[name="rec-tipo"]:checked').value;
    const mensaje = document.getElementById('rec-mensaje').value;
    const btn = document.getElementById('btn-enviar-rec');
    
    // Cambiar el botón a estado de carga para que el usuario no le pique dos veces
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Enviando... (Puede tardar)';
    btn.disabled = true;

    // Crear los datos a enviar
    const formData = new FormData();
    formData.append('tipo', tipoEnvio);
    formData.append('mensaje', mensaje);

    fetch('php/enviar_recordatorio.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            // 1. Cerrar el modal de configuración de recordatorio
            const modalRecordatorio = document.getElementById('modal-recordatorio');
            if(modalRecordatorio) modalRecordatorio.hidden = true;
            
            // 2. Limpiar el texto que escribiste
            document.getElementById('rec-mensaje').value = ''; 
            
            // 3. Inyectar el número de correos enviados en el modal de éxito
            document.getElementById('mensaje-exito').textContent = `Se enviaron ${result.enviados} correos correctamente.`;
            
            // 4. Mostrar el modal de éxito elegante
            document.getElementById('modal-exito').hidden = false;
        } else {
            // Si hay un error, actualizamos el mismo modal pero con texto de error
            document.getElementById('mensaje-exito').textContent = 'Ocurrió un error: ' + result.message;
            document.getElementById('modal-exito').hidden = false;
        }
    })
    .catch(error => {
        console.error('Error al enviar:', error);
        document.getElementById('mensaje-exito').textContent = 'Hubo un error de conexión al enviar los correos.';
        document.getElementById('modal-exito').hidden = false;
    })
    .finally(() => {
        // Regresar el botón a la normalidad
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// ─── SECCIÓN: PREGUNTAS FRECUENTES (FAQ) ───

// Variable global para guardar las preguntas y poder editarlas sin volver a consultar la BD
let faqsCargadas = [];

document.addEventListener('DOMContentLoaded', () => {
    cargarFAQs(); // Cargar la tabla al iniciar
    
    // Configurar el formulario para guardar o actualizar la pregunta
    const formFaq = document.getElementById('form-faq');
    if (formFaq) {
        formFaq.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Leemos el ID oculto (si tiene valor es porque estamos editando, si está vacío estamos creando)
            const inputId = document.getElementById('faq-id');
            const id = inputId ? inputId.value : '';
            
            const pregunta = document.getElementById('faq-pregunta').value;
            const respuesta = document.getElementById('faq-respuesta').value;
            const evento_id = document.getElementById('faq-evento-asoc').value;
            const btnSubmit = formFaq.querySelector('button[type="submit"]');
            
            btnSubmit.innerHTML = id ? 'Actualizando...' : 'Guardando...';
            btnSubmit.disabled = true;

            const formData = new FormData();
            if (id) formData.append('id', id); // Si hay ID, lo mandamos
            formData.append('pregunta', pregunta);
            formData.append('respuesta', respuesta);
            if(evento_id) formData.append('evento_id', evento_id);

            // Decidimos a qué archivo PHP mandarlo
            const url = id ? 'php/actualizar_faq.php' : 'php/crear_faq.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    formFaq.reset(); 
                    if(inputId) inputId.value = ''; // Limpiamos el ID
                    closeAdminModal('modal-faq'); 
                    cargarFAQs(); 
                    
                    const modalExito = document.getElementById('modal-exito');
                    const msjExito = document.getElementById('mensaje-exito');
                    if(modalExito && msjExito) {
                        msjExito.textContent = id ? 'La pregunta se ha actualizado correctamente.' : 'La pregunta se ha guardado correctamente.';
                        modalExito.hidden = false;
                    }
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                btnSubmit.innerHTML = 'Guardar Pregunta';
                btnSubmit.disabled = false;
            });
        });
    }
});

// Función que SOLO trae los datos de la BD
function cargarFAQs() {
    fetch('php/get_faqs.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                faqsCargadas = result.data; // Guardamos en la variable global
                renderTablaFAQs(faqsCargadas); // Mandamos a dibujar todas al inicio
            }
        })
        .catch(error => console.error('Error al cargar FAQs:', error));
}

// NUEVA: Función que dibuja la tabla de FAQs en base a lo que se le envíe (todas o filtradas)
function renderTablaFAQs(datos) {
    const tbody = document.getElementById('tabla-faq-body'); 
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    if (datos.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No hay preguntas para este filtro.</td></tr>';
        return;
    }

    datos.forEach((faq, index) => {
        const tr = document.createElement('tr');
        const nombreEvento = faq.evento_nombre ? `${faq.evento_nombre} (${faq.campus_nombre})` : 'General (Todos)';
        
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td><strong>${faq.pregunta}</strong></td>
            <td><span style="display:inline-block; max-width:250px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="${faq.respuesta}">${faq.respuesta}</span></td>
            <td><span class="badge badge--gray">${nombreEvento}</span></td>
            <td class="table-actions">
                <button class="btn-icon" title="Editar" onclick="openModalFaq(${faq.id})">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button class="btn-icon btn-icon--danger" title="Eliminar" onclick="confirmarEliminarFaq(${faq.id})">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// ─── FUNCIONES PARA ABRIR Y ELIMINAR ───

// Reemplaza la antigua función openModalFaq
function openModalFaq(id) {
    const title = document.getElementById('modal-faq-title');
    const form = document.getElementById('form-faq');
    const inputId = document.getElementById('faq-id');
    
    form.reset(); // Limpiar el formulario
    if(inputId) inputId.value = ''; // Limpiar el ID oculto

    if (id) {
        if(title) title.textContent = 'Editar Pregunta Frecuente';
        
        // Buscar los datos de la pregunta que queremos editar
        const faq = faqsCargadas.find(f => f.id === id);
        if (faq) {
            if(inputId) inputId.value = faq.id;
            document.getElementById('faq-pregunta').value = faq.pregunta;
            document.getElementById('faq-respuesta').value = faq.respuesta;
            document.getElementById('faq-evento-asoc').value = faq.evento_id || '';
        }
    } else {
        if(title) title.textContent = 'Nueva Pregunta Frecuente';
    }
    openAdminModal('modal-faq');
}

// Variable global para saber qué ID vamos a borrar cuando le demos clic al botón rojo
let idFaqAEliminar = null;

// 1. Esta función se ejecuta al darle clic al bote de basura en la tabla
function confirmarEliminarFaq(id) {
    idFaqAEliminar = id; // Guardamos el ID temporalmente
    openAdminModal('modal-confirmar-eliminar'); // Abrimos el modal bonito de advertencia
}

// 2. Esta función se ejecuta al darle clic al botón rojo "Eliminar" dentro del modal
function ejecutarEliminarFaq() {
    if (!idFaqAEliminar) return;

    const btn = document.getElementById('btn-confirmar-eliminar');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Eliminando...';
    btn.disabled = true;

    fetch('php/eliminar_faq.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: idFaqAEliminar })
    })
    .then(res => res.json())
    .then(result => {
        if (result.status === 'success') {
            closeAdminModal('modal-confirmar-eliminar'); // Cerramos el modal de advertencia
            cargarFAQs(); // Recargamos la tabla
            
            // Mostramos el modal de éxito con la palomita verde
            const modalExito = document.getElementById('modal-exito');
            const msjExito = document.getElementById('mensaje-exito');
            if(modalExito && msjExito) {
                msjExito.textContent = 'La pregunta ha sido eliminada correctamente.';
                modalExito.hidden = false;
            }
        } else {
            alert('Error al eliminar: ' + result.message);
        }
    })
    .catch(err => console.error('Error:', err))
    .finally(() => {
        // Regresamos el botón a la normalidad y limpiamos la variable
        btn.innerHTML = originalText;
        btn.disabled = false;
        idFaqAEliminar = null; 
    });
}

// ─── CARGAR TABLA RESUMEN EN EL DASHBOARD ───
function cargarDashboardEventos() {
    fetch('php/get_dashboard_eventos.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const tbody = document.getElementById('dashboard-eventos-body');
                if (!tbody) return;

                tbody.innerHTML = '';

                result.data.forEach(evento => {
                    const tr = document.createElement('tr');
                    
                    // Formatear la fecha para que se vea más limpia
                    const fechaObj = new Date(evento.fecha);
                    const fechaFormateada = fechaObj.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });

                    // Determinar el color del punto de estado
                    const statusClass = evento.estado === 'activo' ? 'status-dot--active' : 'status-dot--pending';

                    tr.innerHTML = `
                        <td><strong>${evento.nombre}</strong></td>
                        <td>${evento.campus}</td>
                        <td>${fechaFormateada}</td>
                        <td><span class="badge badge--green">${evento.total_registros}</span></td>
                        <td><span class="status-dot ${statusClass}"></span> ${evento.estado.charAt(0).toUpperCase() + evento.estado.slice(1)}</td>
                        <td class="table-actions">
                            <button class="btn-icon" title="Ver asistentes" onclick="irARegistrosConFiltro('${evento.nombre}')">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                            <button class="btn-icon" title="Editar evento" onclick="openModalEvento(${evento.id})">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        })
        .catch(error => console.error('Error al cargar tabla dashboard:', error));
}

// Función extra para que el botón de "ojo" te mande a la pestaña de asistentes ya filtrado
function irARegistrosConFiltro(nombreEvento) {
    showSection('registros');
    const select = document.getElementById('filtro-evento');
    if (select) {
        select.value = nombreEvento;
        // Disparamos el evento de cambio manualmente para que se aplique el filtro
        select.dispatchEvent(new Event('change'));
    }
}

function loadCampusForm() {
  const selectCampus = document.getElementById('field-campus-evento');
  if (!selectCampus) return;

  // Hacemos la petición a nuestro nuevo archivo PHP
  fetch('php/get_campus.php')
    .then(res => {
      if (!res.ok) throw new Error('Error al conectar con get_campus.php');
      return res.json();
    })
    .then(result => {
      if (result.status === 'success') {
        // Limpiamos el select y ponemos la opción por defecto
        selectCampus.innerHTML = '<option value="">Selecciona el campus</option>';
        
        // Iteramos los campus que vienen de la base de datos
        result.data.forEach(campus => {
          const option = document.createElement('option');
          option.value = campus.id; // El ID numérico (1, 2, 3)
          option.textContent = campus.nombre; // El nombre del campus (Tijuana, Ensenada, etc.)
          selectCampus.appendChild(option);
        });
      } else {
        selectCampus.innerHTML = '<option value="">Error al cargar los campus</option>';
      }
    })
    .catch(err => {
      console.error("Error crítico al cargar campus:", err);
      selectCampus.innerHTML = '<option value="">Error de conexión</option>';
    });
}

// Aseguramos que la función se ejecute en cuanto cargue el documento
document.addEventListener('DOMContentLoaded', () => {
  loadCampusForm();
});

// ==========================================
// MÓDULO: GESTIÓN DE USUARIOS
// ==========================================

function cargarUsuarios() {
  const tbody = document.getElementById('tabla-usuarios-body');
  if (!tbody) return;
  tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Cargando usuarios...</td></tr>';

  fetch('php/get_usuarios.php')
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        tbody.innerHTML = '';
        if (res.data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">No hay usuarios registrados.</td></tr>';
          return;
        }
        res.data.forEach(user => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td><strong>${user.nombre}</strong></td>
            <td>${user.correo}</td>
            <td>
              <button class="btn btn-ghost btn-sm" onclick='openModalUsuario(${JSON.stringify(user)})'>Editar</button>
              <button class="btn btn-ghost btn-sm" style="color:var(--color-error);" onclick="confirmarEliminarUsuario(${user.id})">Eliminar</button>
            </td>
          `;
          tbody.appendChild(tr);
        });
      }
    })
    .catch(err => console.error(err));
}

function openModalUsuario(user) {
  const form = document.getElementById('form-usuario');
  form.reset();
  const helpText = document.getElementById('user-password-help');
  const inputPass = document.getElementById('user-password');

  if (user) {
    // Modo Edición
    document.getElementById('modal-usuario-title').textContent = 'Editar Usuario';
    document.getElementById('user-id').value = user.id;
    document.getElementById('user-nombre').value = user.nombre;
    document.getElementById('user-correo').value = user.correo;
    inputPass.required = false; // No es obligatorio cambiar contraseña
    helpText.style.display = 'block';
  } else {
    // Modo Creación
    document.getElementById('modal-usuario-title').textContent = 'Nuevo Usuario';
    document.getElementById('user-id').value = '';
    inputPass.required = true;
    helpText.style.display = 'none';
  }
  
  const modal = document.getElementById('modal-usuario');
  modal.hidden = false;
}

// Interceptar envío del formulario de Usuario
const formUsuario = document.getElementById('form-usuario');
if (formUsuario) {
  formUsuario.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    // Cambiamos el texto del botón para que se vea que está cargando
    const btnSubmit = formUsuario.querySelector('button[type="submit"]');
    const originalText = btnSubmit.textContent;
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Guardando...';
    
    fetch('php/guardar_usuario.php', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(res => {
        if (res.status === 'success') {
          closeAdminModal('modal-usuario');
          if (typeof cargarUsuarios === 'function') cargarUsuarios(); // Recargamos la tabla
          
          // 🟢 LLAMAR AL MODAL ELEGANTE
          const modalExito = document.getElementById('modal-exito');
          const modalTitle = modalExito.querySelector('.modal-title');
          const modalMsg = document.getElementById('mensaje-exito');
          const modalIconContainer = modalExito.querySelector('div[style*="justify-content: center"]');
          
          if(modalTitle) {
              modalTitle.textContent = "¡Guardado!";
              modalTitle.style.color = "var(--uabc-verde)";
          }
          if(modalIconContainer) {
              modalIconContainer.style.color = "var(--uabc-verde)";
              modalIconContainer.innerHTML = '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
          }
          if(modalMsg) modalMsg.textContent = res.message; 
          
          if(modalExito) {
              modalExito.hidden = false;
              // Se cierra solito a los 2.5 segundos
              setTimeout(() => { modalExito.hidden = true; }, 2500); 
          }
          
        } else {
          alert('Error: ' + res.message);
        }
      })
      .catch(err => alert('Error al conectar con el servidor'))
      .finally(() => {
         // Devolvemos el botón a la normalidad
         btnSubmit.disabled = false;
         btnSubmit.textContent = originalText;
      });
  });
}

let usuarioAEliminar = null;
function confirmarEliminarUsuario(id) {
  usuarioAEliminar = id;
  document.getElementById('modal-confirmar-eliminar-usuario').hidden = false;
}

function ejecutarEliminarUsuario() {
  if (!usuarioAEliminar) return;
  
  const btn = document.getElementById('btn-confirmar-eliminar-usuario');
  const originalText = btn.innerHTML;
  btn.innerHTML = 'Eliminando...';
  btn.disabled = true;

  const formData = new FormData();
  formData.append('id', usuarioAEliminar);

  fetch('php/eliminar_usuario.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(res => {
      closeAdminModal('modal-confirmar-eliminar-usuario');
      if (res.status === 'success') {
        if (typeof cargarUsuarios === 'function') cargarUsuarios();
        
        // 🟢 LLAMAR AL MODAL ELEGANTE (ELIMINADO)
        const modalExito = document.getElementById('modal-exito');
        const modalTitle = modalExito.querySelector('.modal-title');
        const modalMsg = document.getElementById('mensaje-exito');
        const modalIconContainer = modalExito.querySelector('div[style*="justify-content: center"]');
        
        if(modalTitle) {
            modalTitle.textContent = "¡Eliminado!";
            modalTitle.style.color = "var(--uabc-verde)";
        }
        if(modalIconContainer) {
            modalIconContainer.style.color = "var(--uabc-verde)";
            modalIconContainer.innerHTML = '<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
        }
        if(modalMsg) modalMsg.textContent = res.message; 
        
        if(modalExito) {
            modalExito.hidden = false;
            // Se cierra solito a los 2.5 segundos
            setTimeout(() => { modalExito.hidden = true; }, 2500);
        }
        
      } else {
        alert('Error: ' + res.message);
      }
    })
    .catch(err => alert('Error al eliminar'))
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        usuarioAEliminar = null;
    });
}

// Cargar la tabla cuando se le de clic al botón de la sección
document.addEventListener('DOMContentLoaded', () => {
  const btnUsuarios = document.querySelector('[data-section="usuarios"]');
  if (btnUsuarios) {
    btnUsuarios.addEventListener('click', cargarUsuarios);
  }
});

// ==========================================
// MÓDULO: VALIDACIÓN DE QR Y BÚSQUEDA MANUAL
// ==========================================

function validarQR(codigoManual = null, idManual = null) {
  const input = document.getElementById('qr-input');
  const codigo = codigoManual || (input ? input.value.trim() : '');

  // Referencias a la tarjeta rápida en línea
  const resultDiv = document.getElementById('qr-result');
  const resultCard = document.getElementById('qr-result-content');
  const resultStatus = document.getElementById('qr-result-status');
  const resultName = document.getElementById('qr-result-name');
  const resultDetail = document.getElementById('qr-result-detail');
  const resultIcon = document.querySelector('.qr-result-icon');

  // Función interna para mostrar errores rápido en la misma tarjeta
  function mostrarTarjetaError(mensajeError) {
    if(resultDiv) resultDiv.hidden = false;
    if(resultCard) resultCard.style.borderLeft = "4px solid var(--color-error)";
    if(resultStatus) {
        resultStatus.textContent = "Error de validación";
        resultStatus.style.color = "var(--color-error)";
    }
    if(resultName) resultName.textContent = "";
    if(resultDetail) resultDetail.textContent = mensajeError;
    if(resultIcon) {
        resultIcon.style.color = "var(--color-error)";
        resultIcon.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
    }
    setTimeout(() => { if (resultDiv) resultDiv.hidden = true; }, 3000);
  }

  // Validar que manden algo
  if (!codigo && !idManual) {
    mostrarTarjetaError('Por favor, ingresa un código QR o busca un nombre.');
    return;
  }

  const formData = new FormData();
  if (idManual) formData.append('id', idManual);
  else formData.append('codigo', codigo);

  fetch('php/validar_qr.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(result => {
    
    if (result.status === 'success') {
      // 🟢 ÉXITO: Tarjeta en Verde
      if(resultDiv) resultDiv.hidden = false;
      if(resultCard) resultCard.style.borderLeft = "4px solid var(--uabc-verde)";
      if(resultStatus) {
          resultStatus.textContent = "¡Acceso Concedido!";
          resultStatus.style.color = "var(--uabc-verde)";
      }
      if(resultName) resultName.textContent = `${result.data.nombre} ${result.data.apellidos}`;
      if(resultDetail) resultDetail.textContent = `${result.data.campus} · ${result.data.carrera || 'Asistente'}`;
      if(resultIcon) {
          resultIcon.style.color = "var(--uabc-verde)";
          resultIcon.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
      }

      // Recargamos datos en silencio
      if (typeof cargarTablaAsistentes === 'function') cargarTablaAsistentes();
      if (typeof cargarDashboardStats === 'function') cargarDashboardStats();
      
      // Auto-ocultar a los 2.5 segundos
      setTimeout(() => { if (resultDiv) resultDiv.hidden = true; }, 2500);

    } else if (result.status === 'already_scanned') {
      // 🟡 ADVERTENCIA: Tarjeta en Dorado
      if(resultDiv) resultDiv.hidden = false;
      if(resultCard) resultCard.style.borderLeft = "4px solid #F2A900"; // Dorado UABC
      if(resultStatus) {
          resultStatus.textContent = "Código Ya Escaneado";
          resultStatus.style.color = "#F2A900";
      }
      if(resultName) resultName.textContent = `${result.data.nombre} ${result.data.apellidos}`;
      if(resultDetail) resultDetail.textContent = "Esta persona ya registró su entrada previamente.";
      if(resultIcon) {
          resultIcon.style.color = "#F2A900";
          resultIcon.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
      }

      // Auto-ocultar a los 3 segundos
      setTimeout(() => { if (resultDiv) resultDiv.hidden = true; }, 3000);

    } else {
      // 🔴 ERROR (Ej. Código Falso): Tarjeta en Rojo
      mostrarTarjetaError(result.message || 'Código inválido o no encontrado en el sistema');
    }

    // Limpiamos los inputs
    if (input) input.value = '';
    const searchInput = document.getElementById('search-nombre-qr');
    if (searchInput) searchInput.value = '';
    const resultsDiv = document.getElementById('resultados-nombre-qr');
    if (resultsDiv) resultsDiv.innerHTML = '';

  })
  .catch(err => {
    console.error(err);
    mostrarTarjetaError('Error de conexión con el servidor.');
  });
}

function buscarManualQR() {
  const query = document.getElementById('search-nombre-qr').value.toLowerCase();
  const container = document.getElementById('resultados-nombre-qr');
  container.innerHTML = '';

  if (query.length < 3) return; // Esperar a que escriban al menos 3 letras

  // Aprovechamos que la tabla de asistentes ya cargó todos los datos
  if (!asistentesCargados || asistentesCargados.length === 0) {
      cargarTablaAsistentes(); // Forzar carga si estaba vacía
      container.innerHTML = '<small style="color:var(--color-text-muted);">Cargando base de datos...</small>';
      return;
  }

  // NUEVO: Agregamos la condición de que la asistencia NO sea 1
  const filtrados = asistentesCargados.filter(a => 
      parseInt(a.asistencia) !== 1 && // <-- ESTA LÍNEA ES LA MAGIA QUE LOS OCULTA
      (`${a.nombre} ${a.apellidos}`.toLowerCase().includes(query) || 
      (a.correo && a.correo.toLowerCase().includes(query)))
  ).slice(0, 5);

  if (filtrados.length === 0) {
      // Modificamos ligeramente el texto para que el usuario entienda
      container.innerHTML = '<small style="color:var(--color-text-muted);">No se encontraron coincidencias o ya ingresaron.</small>';
      return;
  }

  // Imprimir botones elegantes para dar acceso
  filtrados.forEach(a => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-ghost btn-sm';
      btn.style.justifyContent = 'space-between';
      btn.style.width = '100%';
      btn.style.textAlign = 'left';
      btn.innerHTML = `
          <div style="line-height: 1.3;">
              <strong>${a.nombre} ${a.apellidos}</strong><br>
              <small style="color:var(--color-text-muted);">${a.correo || 'Sin correo'}</small>
          </div>
          <span class="badge badge--green">Dar Acceso</span>
      `;
      // Al hacer clic, ejecuta la misma función de validar pero usando el ID
      btn.onclick = () => validarQR(null, a.id);
      container.appendChild(btn);
  });
}