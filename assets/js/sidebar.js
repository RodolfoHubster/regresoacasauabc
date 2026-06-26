(function () {
  var sidebar  = document.getElementById('admin-sidebar');
  var overlay  = document.getElementById('sidebar-overlay');
  var toggle   = document.getElementById('sidebar-toggle');
  var closeBtn = document.getElementById('sidebar-close-btn');

  if (!sidebar) return; // Exit if no sidebar is present

  function openSidebar() {
    sidebar.classList.add('open');
    if (overlay) overlay.classList.add('active');
    if (toggle) toggle.setAttribute('aria-expanded', 'true');
  }

  function closeSidebar() {
    sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('active');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
  }

  // Botón hamburguesa abre/cierra (si existe, suele estar en el admin-header)
  if (toggle) {
    toggle.addEventListener('click', function () {
      sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
  }

  // Botón X dentro del sidebar cierra
  if (closeBtn) {
    closeBtn.addEventListener('click', closeSidebar);
  }

  // Clic en el overlay (zona de contenido) cierra sidebar
  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
  }

  // Cierra sidebar al hacer clic en un ítem de nav (solo en móvil)
  document.querySelectorAll('.sidebar-link[data-section]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (window.innerWidth <= 900) closeSidebar();
    });
  });
})();
