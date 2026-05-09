/* eventos.js – Funciones de modal para eventos */

/**
 * Abre el modal de información de un evento
 * @param {string} modalId - ID del modal a abrir
 */
function openModalInfo(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.removeAttribute('hidden');
  document.body.style.overflow = 'hidden';
  // Focus al primer elemento interactivo
  const focusable = modal.querySelector('button, [href], input, select');
  if (focusable) setTimeout(() => focusable.focus(), 50);
}

/**
 * Abre el modal de registro precargando el evento
 * @param {string} modalId
 * @param {string} eventoNombre
 * @param {string} eventoImagen
 */
function openModal(modalId, eventoNombre, eventoImagen) {
  const modal = document.getElementById(modalId);
  if (!modal) return;

  // Precargar datos del evento en el modal de registro
  if (modalId === 'modal-registro') {
    const banner = document.getElementById('modal-event-banner');
    const label  = document.getElementById('modal-event-label');
    const hidden = document.getElementById('field-evento');
    if (banner && eventoImagen) banner.style.backgroundImage = `url('${eventoImagen}')`;
    if (label  && eventoNombre) label.textContent = eventoNombre;
    if (hidden && eventoNombre) hidden.value = eventoNombre;
  }

  modal.removeAttribute('hidden');
  document.body.style.overflow = 'hidden';
  const focusable = modal.querySelector('button, [href], input, select');
  if (focusable) setTimeout(() => focusable.focus(), 50);
}

/**
 * Cierra cualquier modal por ID
 * @param {string} modalId
 */
function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.setAttribute('hidden', '');
  document.body.style.overflow = '';
}

// Cerrar modal al hacer clic en el overlay
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.setAttribute('hidden', '');
        document.body.style.overflow = '';
      }
    });
  });

  // Cerrar con Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay:not([hidden])').forEach(m => {
        m.setAttribute('hidden', '');
        document.body.style.overflow = '';
      });
    }
  });

  // Navegación activa al hacer scroll
  const navLinks = document.querySelectorAll('.nav-link');
  const sections = document.querySelectorAll('section[id]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        navLinks.forEach(link => link.classList.remove('active'));
        const activeLink = document.querySelector(`.nav-link[href="#${entry.target.id}"]`);
        if (activeLink) activeLink.classList.add('active');
      }
    });
  }, { threshold: 0.4 });
  sections.forEach(s => observer.observe(s));
});
