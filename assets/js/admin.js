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
      cargarTablaAsistentes(); // Recarga los datos reales al hacer clic en el menú
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
      document.getElementById('ev-nombre').value = evento.nombre;
      document.getElementById('ev-descripcion').value = evento.descripcion;
      document.getElementById('ev-fecha').value = evento.fecha;
      document.getElementById('ev-hora').value = evento.hora;
      document.getElementById('ev-ubicacion').value = evento.ubicacion;
      document.getElementById('ev-imagen').value = evento.imagen;
      document.getElementById('ev-estado').value = evento.estado;
    }
  } else {
    title.textContent = 'Nuevo Evento';
  }
  openAdminModal('modal-evento');
}

function openModalRecordatorio(evento) {
  const label = document.getElementById('modal-rec-evento');
  if (label) label.textContent = '📅 ' + evento;
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
// Función para cuando se CREA un evento nuevo
function showSuccessWithQR(eventId) {
  const container = document.getElementById('qrcode-container');
  const linkInput = document.getElementById('event-link-input');
  
  // Extraemos solo el número del ID (ej. de EVT-2026-5 sacamos el 5)
  const soloId = eventId.split('-').pop();
  
  container.innerHTML = "";
  
  // Forzamos la ruta de tu carpeta específica
  const eventUrl = `${window.location.origin}/regresoacasauabc/index.html?evento=${soloId}`;
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
  const eventUrl = `${window.location.origin}/regresoacasauabc/index.html?evento=${id}`;
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

        result.data.forEach(evento => {
          // ... dentro de la función loadEventos, en el forEach de los eventos:
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
                <button class="btn btn-primary btn-sm" onclick="verQR(${evento.id}, '${evento.nombre}')">Ver QR</button>
                
                <a href="participantes.html?id=${evento.id}" class="btn btn-dark btn-sm">Participantes</a>
                
                <button class="btn btn-ghost btn-sm" onclick="openModalRecordatorio('${evento.nombre}')">Recordatorio</button>
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
    formEvento.addEventListener('submit', (e) => {
      e.preventDefault();
      
      const idInput = document.getElementById('ev-id');
      const id = idInput ? idInput.value : "";
      
      const formData = new FormData();
      if (id) formData.append('id', id);
      formData.append('nombre', document.getElementById('ev-nombre').value);
      formData.append('descripcion', document.getElementById('ev-descripcion').value);
      formData.append('fecha', document.getElementById('ev-fecha').value);
      formData.append('hora', document.getElementById('ev-hora').value);
      formData.append('ubicacion', document.getElementById('ev-ubicacion').value);
      formData.append('imagen', document.getElementById('ev-imagen').value);
      formData.append('estado', document.getElementById('ev-estado').value);

      const btnSubmit = formEvento.querySelector('button[type="submit"]');
      const originalText = btnSubmit.textContent;
      btnSubmit.disabled = true;
      btnSubmit.textContent = id ? 'Actualizando...' : 'Guardando...';

      const url = id ? 'php/actualizar_evento.php' : 'php/crear_evento.php';

      fetch(url, {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        btnSubmit.disabled = false;
        btnSubmit.textContent = originalText;

        if (data.status === 'success') {
          if (!id) {
            showSuccessWithQR('EVT-' + data.eventId);
          } else {
            alert('Evento actualizado con éxito');
            closeAdminModal('modal-evento');
          }
          formEvento.reset();
          loadEventos(); 
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        btnSubmit.disabled = false;
        btnSubmit.textContent = originalText;
        console.error('Error:', error);
        alert('Hubo un problema de conexión.');
      });
    });
  }

  // 2. Manejo del formulario de FAQ
  const formFaq = document.getElementById('form-faq');
  if (formFaq) {
    formFaq.addEventListener('submit', (e) => {
      e.preventDefault();
      alert('Pregunta guardada (simulación). Requiere backend PHP.');
      closeAdminModal('modal-faq');
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
function cargarTablaAsistentes() {
  fetch('php/get_asistentes.php')
    .then(response => response.json())
    .then(result => {
      if (result.status === 'success') {
        const tbody = document.getElementById('tabla-asistentes-body');
        if (!tbody) return; // Si no encuentra la tabla, se detiene
        
        tbody.innerHTML = ''; // Limpiar la tabla antes de llenarla

        if (result.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Aún no hay asistentes confirmados (QR escaneados).</td></tr>';
            return;
        }

        // Recorrer los datos y crear las filas HTML
        result.data.forEach(asistente => {
          const tr = document.createElement('tr');
          
          tr.innerHTML = `
            <td>
              <div class="user-info">
                <div class="user-avatar" aria-hidden="true">${asistente.nombre.charAt(0)}${asistente.apellidos.charAt(0)}</div>
                <div>
                  <p class="user-name">${asistente.nombre} ${asistente.apellidos}</p>
                </div>
              </div>
            </td>
            <td>${asistente.correo}</td>
            <td>${asistente.campus}</td>
            <td>
               <span style="font-weight: 600;">${asistente.facultad_nombre || ''}</span><br>
               <small style="color: #666;">${asistente.carrera}</small>
            </td>
            <td>${asistente.generacion}</td>
            <td>
              <span class="badge badge--success">Asistió</span>
            </td>
          `;
          tbody.appendChild(tr);
        });
      } else {
        console.error('Error al cargar tabla:', result.message);
      }
    })
    .catch(error => console.error('Error de red:', error));
}

// ─── MODIFICAR LA CARGA INICIAL ───
// Busca el DOMContentLoaded que pusimos antes y agrégale la nueva función:
// Al final de admin.js
document.addEventListener('DOMContentLoaded', () => {
  // Primero cargamos las estadísticas de los cuadros de colores
  if (typeof cargarDashboardStats === 'function') {
      cargarDashboardStats();
  }
  
  // LUEGO cargamos la tabla de asistentes
  if (typeof cargarTablaAsistentes === 'function') {
      cargarTablaAsistentes();
  }
});