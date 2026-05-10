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

  // Cerrar sidebar en móvil
  document.getElementById('admin-sidebar').classList.remove('open');
  // Scroll al inicio
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

function openModalEvento(nombre) {
  const title = document.getElementById('modal-evento-title');
  if (title) title.textContent = nombre ? 'Editar Evento: ' + nombre : 'Nuevo Evento';
  openAdminModal('modal-evento');
}

function openModalRecordatorio(evento) {
  const label = document.getElementById('modal-rec-evento');
  if (label) label.textContent = '📅 ' + evento;
  openAdminModal('modal-recordatorio');
}

function openModalAsistente(nombre) {
  // TODO: cargar datos reales desde API PHP
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
    // TODO: fetch DELETE a api/faq.php
    alert('Pregunta #' + id + ' eliminada (simulación). Requiere backend PHP.');
  }
}

// ─── FORMULARIOS ───
document.addEventListener('DOMContentLoaded', () => {
  const formEvento = document.getElementById('form-evento');
  if (formEvento) {
    formEvento.addEventListener('submit', (e) => {
      e.preventDefault();
      // TODO: fetch POST a api/eventos.php
      alert('Evento guardado (simulación). Requiere backend PHP.');
      closeAdminModal('modal-evento');
    });
  }

  const formFaq = document.getElementById('form-faq');
  if (formFaq) {
    formFaq.addEventListener('submit', (e) => {
      e.preventDefault();
      // TODO: fetch POST a api/faq.php
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