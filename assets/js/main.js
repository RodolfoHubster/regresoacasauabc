/**
 * main.js — Inicialización global, tema, navbar mobile, FAQ accordion
 * Proyecto: Regresa a Casa UABC
 */

document.addEventListener('DOMContentLoaded', () => {

  // ---- TEMA CLARO / OSCURO ----
  const html = document.documentElement;
  const themeToggle = document.querySelector('[data-theme-toggle]');
  let currentTheme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  html.setAttribute('data-theme', currentTheme);
  updateThemeIcon();

  themeToggle?.addEventListener('click', () => {
    currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', currentTheme);
    updateThemeIcon();
    lucide.createIcons();
  });

  function updateThemeIcon() {
    if (!themeToggle) return;
    const icon = currentTheme === 'dark' ? 'sun' : 'moon';
    themeToggle.setAttribute('aria-label', currentTheme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
    themeToggle.innerHTML = `<i data-lucide="${icon}" width="18" height="18"></i>`;
  }

  // ---- NAVBAR MOBILE ----
  const navToggle = document.getElementById('nav-toggle');
  const mobileMenu = document.getElementById('mobile-menu');

  navToggle?.addEventListener('click', () => {
    const isOpen = !mobileMenu.hidden;
    mobileMenu.hidden = isOpen;
    navToggle.setAttribute('aria-expanded', String(!isOpen));
    navToggle.innerHTML = isOpen
      ? '<i data-lucide="menu" width="22" height="22"></i>'
      : '<i data-lucide="x" width="22" height="22"></i>';
    lucide.createIcons();
  });

  // Cerrar menú móvil al hacer clic en un link
  mobileMenu?.querySelectorAll('.mobile-nav-link').forEach(link => {
    link.addEventListener('click', () => {
      mobileMenu.hidden = true;
      navToggle.setAttribute('aria-expanded', 'false');
      navToggle.innerHTML = '<i data-lucide="menu" width="22" height="22"></i>';
      lucide.createIcons();
    });
  });

  // ---- FAQ ACCORDION ----
  document.querySelectorAll('.faq-question').forEach(btn => {
    btn.addEventListener('click', () => {
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      const targetId = btn.getAttribute('aria-controls');
      const answer = document.getElementById(targetId);

      // Cerrar todos los demás
      document.querySelectorAll('.faq-question').forEach(other => {
        if (other !== btn) {
          other.setAttribute('aria-expanded', 'false');
          const otherId = other.getAttribute('aria-controls');
          document.getElementById(otherId).hidden = true;
        }
      });

      btn.setAttribute('aria-expanded', String(!expanded));
      answer.hidden = expanded;
    });
  });

  // ---- MODAL ----
  const backdrop = document.getElementById('modal-backdrop');

  // Abrir modal de registro
  document.querySelectorAll('[data-modal="registro"]').forEach(btn => {
    btn.addEventListener('click', () => openModal('modal-registro'));
  });

  // Cerrar modal
  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', closeAllModals);
  });
  backdrop?.addEventListener('click', closeAllModals);

  // Cerrar con Escape
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeAllModals();
  });

  function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    backdrop.hidden = false;
    backdrop.removeAttribute('aria-hidden');
    modal.hidden = false;
    // Focus al primer input
    setTimeout(() => {
      const first = modal.querySelector('input, select, button, [tabindex]:not([tabindex="-1"])');
      first?.focus();
    }, 50);
  }

  function closeAllModals() {
    document.querySelectorAll('.modal').forEach(m => m.hidden = true);
    if (backdrop) {
      backdrop.hidden = true;
      backdrop.setAttribute('aria-hidden', 'true');
    }
  }

});
