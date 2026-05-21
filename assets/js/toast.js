/**
 * toast.js — Sistema de notificaciones no bloqueantes
 * Uso: showToast('Mensaje', 'success' | 'error' | 'warning' | 'info')
 */
(function () {
  'use strict';

  function getContainer() {
    let c = document.getElementById('toast-container');
    if (!c) {
      c = document.createElement('div');
      c.id = 'toast-container';
      c.setAttribute('aria-live', 'polite');
      c.setAttribute('aria-atomic', 'false');
      document.body.appendChild(c);
    }
    return c;
  }

  const ICONS = {
    success: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>',
    error:   '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    warning: '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    info:    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
  };

  const DURATION = { success: 3500, error: 5000, warning: 4500, info: 3500 };

  window.showToast = function (message, type, duration) {
    type = type || 'info';
    const ms = (duration !== undefined) ? duration : DURATION[type];
    const container = getContainer();

    const toast = document.createElement('div');
    toast.className = 'toast toast--' + type;
    toast.setAttribute('role', 'status');
    toast.innerHTML = [
      '<span class="toast-icon">' + (ICONS[type] || ICONS.info) + '</span>',
      '<span class="toast-message">' + message + '</span>',
      '<button class="toast-close" aria-label="Cerrar notificación">',
      '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
      '</button>'
    ].join('');

    toast.querySelector('.toast-close').addEventListener('click', function () {
      dismiss(toast);
    });

    container.appendChild(toast);
    void toast.offsetWidth;
    toast.classList.add('toast--visible');

    if (ms > 0) setTimeout(function () { dismiss(toast); }, ms);
    return toast;
  };

  function dismiss(toast) {
    if (toast.classList.contains('toast--leaving')) return;
    toast.classList.add('toast--leaving');
    toast.addEventListener('animationend', function () { toast.remove(); }, { once: true });
  }
})();
