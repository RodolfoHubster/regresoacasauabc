/* ==========================================================
   ADMIN.JS — Lógica del panel administrador
   ========================================================== */

// ---- Navegación entre secciones ----
const SECTION_TITLES = {
  dashboard:  'Dashboard',
  eventos:    'Gestión de Eventos',
  registros:  'Asistentes Registrados',
  faq:        'Preguntas Frecuentes',
  qr:         'Validar QR',
};

function showSection(id) {
  // Ocultar todas
  document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
  document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));

  // Mostrar la seleccionada
  const section = document.getElementById('section-' + id);
  if (section) section.classList.add('active');

  // Marcar link activo
  const link = document.querySelector(`.sidebar-link[data-section="${id}"]`);
  if (link) link.classList.add('active');

  // Actualizar título en topbar
  const topbarTitle = document.getElementById('topbar-title');
  if (topbarTitle) topbarTitle.textContent = SECTION_TITLES[id] || id;

  // Cerrar sidebar en mobile tras navegar
  if (window.innerWidth <= 900) closeSidebar();
}

// ---- Sidebar toggle (mobile) ----
function toggleSidebar() {
  const sidebar  = document.getElementById('admin-sidebar');
  const overlay  = document.getElementById('sidebar-overlay');
  const isOpen   = sidebar.classList.contains('is-open');
  if (isOpen) {
    closeSidebar();
  } else {
    sidebar.classList.add('is-open');
    sidebar.classList.remove('is-closed');
    if (overlay) overlay.classList.add('active');
  }
}

function closeSidebar() {
  const sidebar = document.getElementById('admin-sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  sidebar.classList.remove('is-open');
  sidebar.classList.add('is-closed');
  if (overlay) overlay.classList.remove('active');
}

// ---- Modals ----
function openAdminModal(id) {
  const modal = document.getElementById(id);
  if (!modal) return;
  modal.removeAttribute('hidden');
  // Focus al primer input del modal
  setTimeout(() => {
    const first = modal.querySelector('input, textarea, select, button');
    if (first) first.focus();
  }, 100);
}

function closeAdminModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.setAttribute('hidden', '');
}

// Cerrar modal con Escape o click en overlay
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay:not([hidden])').forEach(m => {
      m.setAttribute('hidden', '');
    });
  }
});

document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.setAttribute('hidden', '');
  }
});

// ---- Modal Evento ----
function openModalEvento(nombre) {
  const title = document.getElementById('modal-evento-title');
  if (title) title.textContent = nombre ? 'Editar Evento: ' + nombre : 'Nuevo Evento';
  openAdminModal('modal-evento');
}

document.getElementById('form-evento')?.addEventListener('submit', (e) => {
  e.preventDefault();
  // TODO: enviar datos al backend PHP via fetch
  alert('Evento guardado (requiere backend PHP)');
  closeAdminModal('modal-evento');
});

// ---- Modal Recordatorio ----
function openModalRecordatorio(eventoNombre) {
  const span = document.getElementById('modal-rec-evento');
  if (span) span.textContent = eventoNombre;
  openAdminModal('modal-recordatorio');
}

// ---- Modal Asistente ----
function openModalAsistente(nombre) {
  // TODO: cargar datos reales desde PHP
  const el = document.getElementById('asi-nombre');
  if (el) el.textContent = nombre;
  openAdminModal('modal-asistente');
}

// ---- Modal FAQ ----
function openModalFaq(id) {
  const title = document.getElementById('modal-faq-title');
  if (title) title.textContent = id ? 'Editar Pregunta' : 'Nueva Pregunta Frecuente';
  openAdminModal('modal-faq');
}

document.getElementById('form-faq')?.addEventListener('submit', (e) => {
  e.preventDefault();
  // TODO: guardar FAQ en BD via PHP
  alert('Pregunta guardada (requiere backend PHP)');
  closeAdminModal('modal-faq');
});

// ---- Confirmar eliminar FAQ ----
function confirmarEliminarFaq(id) {
  if (confirm('¿Eliminar esta pregunta frecuente?')) {
    // TODO: DELETE via PHP fetch
    alert('FAQ #' + id + ' eliminada (requiere backend PHP)');
  }
}

// ---- QR Validator ----
function validarQR() {
  const input  = document.getElementById('qr-input');
  const result = document.getElementById('qr-result');
  const card   = document.getElementById('qr-result-content');
  const name   = document.getElementById('qr-result-name');
  const detail = document.getElementById('qr-result-detail');

  if (!input || !input.value.trim()) return;

  // TODO: validar contra BD PHP via fetch('/api/validar-qr?code=' + input.value)
  // Por ahora simulamos respuesta de ejemplo
  const codigo = input.value.trim().toUpperCase();
  const esValido = codigo.startsWith('UABC-') || codigo.length > 5;

  result.removeAttribute('hidden');
  if (esValido) {
    card.className = 'qr-result-card qr-result-card--ok';
    if (name)   name.textContent   = 'Asistente Registrado';
    if (detail) detail.textContent = 'Registro válido · ' + codigo;
  } else {
    card.className = 'qr-result-card qr-result-card--error';
    if (name)   name.textContent   = 'Código no encontrado';
    if (detail) detail.textContent = 'Verifica el código e inténtalo de nuevo';
  }
}

// Enter en el input de QR dispara validación automática
document.getElementById('qr-input')?.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') validarQR();
});

// ---- Overlay sidebar click ----
document.getElementById('sidebar-overlay')?.addEventListener('click', closeSidebar);

// ---- Init ----
document.addEventListener('DOMContentLoaded', () => {
  showSection('dashboard');
});
