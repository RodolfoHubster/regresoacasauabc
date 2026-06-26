/* main.js – Inicialización global */

document.addEventListener('DOMContentLoaded', () => {

  // ─── HAMBURGUESA ───
  const hamburger = document.getElementById('nav-hamburger');
  const mobileNav = document.getElementById('mobile-nav');

  if (hamburger && mobileNav) {
    hamburger.addEventListener('click', () => {
      const isOpen = hamburger.getAttribute('aria-expanded') === 'true';
      hamburger.setAttribute('aria-expanded', String(!isOpen));
      mobileNav.setAttribute('aria-hidden', String(isOpen));
      mobileNav.classList.toggle('is-open', !isOpen);
    });

    // Cerrar menú al tocar un link
    mobileNav.querySelectorAll('.mobile-nav-link').forEach(link => {
      link.addEventListener('click', () => {
        hamburger.setAttribute('aria-expanded', 'false');
        mobileNav.setAttribute('aria-hidden', 'true');
        mobileNav.classList.remove('is-open');
      });
    });
  }

  // ─── NAV ACTIVO POR SCROLL ───
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

// ─── FAQ DINÁMICO Y FILTRADO POR CAMPUS ───
  const faqListContainer = document.getElementById('faq-list');
  const filterBtns = document.querySelectorAll('.faq-filter-btn');
  let allFaqsData = [];

  if (faqListContainer) {
    // 1. Cargar todas las FAQs desde el backend
    const path = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
    fetch(path + 'admin/php/get_faqs.php')
      .then(res => res.json())
      .then(result => {
        if (result.status === 'success') {
          // Filtrar las preguntas ocultas
          allFaqsData = result.data.filter(f => f.oculto == 0);
          renderMainFaqs('Todos'); // Renderizar todas al inicio
        } else {
          faqListContainer.innerHTML = '<p style="text-align:center; color:var(--color-text-muted);">No se pudieron cargar las preguntas.</p>';
        }
      })
      .catch(err => {
        console.error("Error cargando FAQs:", err);
        faqListContainer.innerHTML = '<p style="text-align:center; color:var(--color-error);">Error al conectar con el servidor.</p>';
      });
  }

  // 2. Función para renderizar filtrando por campus
  function renderMainFaqs(campus) {
    if (!faqListContainer) return;
    faqListContainer.innerHTML = '';

    const filtradas = allFaqsData.filter(faq => {
      // 1. Si estamos en la pestaña principal, mostramos SOLO las preguntas generales (sin campus)
      if (campus === 'Todos') {
        return !faq.campus_nombre; 
      }
      
      // 2. Si estamos en un campus específico, mostramos las generales...
      if (!faq.campus_nombre) return true; 
      
      // ... y TAMBIÉN las específicas de ese campus
      return faq.campus_nombre === campus;
    });

    if (filtradas.length === 0) {
      faqListContainer.innerHTML = '<p style="text-align:center; padding:2rem; color:var(--color-text-muted);">No hay preguntas frecuentes específicas para este campus aún.</p>';
      return;
    }

    filtradas.forEach((faq, index) => {
      const faqHtml = `
        <div class="faq-item">
          <button class="faq-trigger" aria-expanded="false" aria-controls="main-faq-${index}">
            <span>${faq.pregunta}</span>
            <svg class="faq-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="faq-answer" id="main-faq-${index}" hidden>
            <p>${faq.respuesta}</p>
          </div>
        </div>
      `;
      faqListContainer.innerHTML += faqHtml;
    });

    // 3. Volver a asignar los eventos de click (Acordeón) al nuevo HTML generado
    document.querySelectorAll('#faq-list .faq-trigger').forEach(trigger => {
      trigger.addEventListener('click', () => {
        const expanded = trigger.getAttribute('aria-expanded') === 'true';
        const answerId = trigger.getAttribute('aria-controls');
        const answer = document.getElementById(answerId);

        // Cerrar todas las demás
        document.querySelectorAll('#faq-list .faq-trigger').forEach(t => {
          t.setAttribute('aria-expanded', 'false');
          const a = document.getElementById(t.getAttribute('aria-controls'));
          if (a) a.hidden = true;
        });

        // Abrir la clickeada
        if (!expanded) {
          trigger.setAttribute('aria-expanded', 'true');
          if (answer) answer.hidden = false;
        }
      });
    });
  }

  // 4. Eventos para los botones de filtro
  filterBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      // Actualizar diseño de botones (poner amarillo el activo y gris los inactivos)
      filterBtns.forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-ghost');
      });
      e.target.classList.remove('btn-ghost');
      e.target.classList.add('btn-primary');

      // Llamar a renderizar pasando el nombre del campus del botón seleccionado
      renderMainFaqs(e.target.getAttribute('data-campus'));
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
    const label  = document.getElementById('modal-event-label');
    const hidden = document.getElementById('field-evento');
    if (label)  label.textContent = '📅 ' + eventoNombre;
    if (hidden) hidden.value = eventoNombre;
  }

  const firstFocusable = overlay.querySelector('input, select, textarea, button:not(.modal-close)');
  if (firstFocusable) setTimeout(() => firstFocusable.focus(), 100);

  overlay._escHandler = (e) => { if (e.key === 'Escape') closeModal(id); };
  document.addEventListener('keydown', overlay._escHandler);

  overlay._clickHandler = (e) => { if (e.target === overlay) closeModal(id); };
  overlay.addEventListener('click', overlay._clickHandler);
}

function closeModal(id) {
  const overlay = document.getElementById(id);
  if (!overlay) return;
  overlay.hidden = true;
  document.body.style.overflow = '';
  if (overlay._escHandler)   document.removeEventListener('keydown', overlay._escHandler);
  if (overlay._clickHandler) overlay.removeEventListener('click', overlay._clickHandler);
}
