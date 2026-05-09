/* Overlay DOM para el sidebar en mobile — se inyecta automáticamente */
document.addEventListener('DOMContentLoaded', () => {
  if (!document.getElementById('sidebar-overlay')) {
    const overlay = document.createElement('div');
    overlay.id        = 'sidebar-overlay';
    overlay.className = 'sidebar-overlay';
    overlay.addEventListener('click', () => {
      if (typeof closeSidebar === 'function') closeSidebar();
    });
    document.body.appendChild(overlay);
  }
});
