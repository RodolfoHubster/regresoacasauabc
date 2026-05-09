/* main.js – Inicialización global */

document.addEventListener('DOMContentLoaded', () => {
  // Resaltar nav link activo al hacer scroll
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-link');

  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        navLinks.forEach(link => {
          link.classList.toggle('active', link.getAttribute('href') === '#' + entry.target.id);
        });
      }
    });
  }, { threshold: 0.4 });

  sections.forEach(sec => observer.observe(sec));

  // FAQ accordion
  document.querySelectorAll('.faq-trigger').forEach(trigger => {
    trigger.addEventListener('click', () => {
      const expanded = trigger.getAttribute('aria-expanded') === 'true';
      const answerId = trigger.getAttribute('aria-controls');
      const answer = document.getElementById(answerId);

      // Cerrar todos
      document.querySelectorAll('.faq-trigger').forEach(t => {
        t.setAttribute('aria-expanded', 'false');
        const id = t.getAttribute('aria-controls');
        const a = document.getElementById(id);
        if (a) a.hidden = true;
      });

      // Abrir el actual si estaba cerrado
      if (!expanded) {
        trigger.setAttribute('aria-expanded', 'true');
        if (answer) answer.hidden = false;
      }
    });
  });
});

// ─── MODAL HELPERS ───
function openModal(id, eventoNombre) {
  const overlay = document.getElementById(id);
  if (!overlay) return;
  overlay.hidden = false;
  document.body.style.overflow = 'hidden';

  if (eventoNombre) {
    const label = document.getElementById('modal-event-label');
    const hidden = document.getElementById('field-evento');
    if (label) label.textContent = '📅 ' + eventoNombre;
    if (hidden) hidden.value = eventoNombre;
  }

  // Focus primer elemento interactivo
  const firstFocusable = overlay.querySelector('input, select, textarea, button:not(.modal-close)');
  if (firstFocusable) setTimeout(() => firstFocusable.focus(), 100);

  // Cerrar con Escape
  overlay._escHandler = (e) => { if (e.key === 'Escape') closeModal(id); };
  document.addEventListener('keydown', overlay._escHandler);

  // Cerrar al click en overlay
  overlay._clickHandler = (e) => { if (e.target === overlay) closeModal(id); };
  overlay.addEventListener('click', overlay._clickHandler);
}

function closeModal(id) {
  const overlay = document.getElementById(id);
  if (!overlay) return;
  overlay.hidden = true;
  document.body.style.overflow = '';
  if (overlay._escHandler) document.removeEventListener('keydown', overlay._escHandler);
  if (overlay._clickHandler) overlay.removeEventListener('click', overlay._clickHandler);
}
