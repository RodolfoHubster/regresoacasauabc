  <!-- Overlay: clic fuera del sidebar lo cierra -->
  <div class="sidebar-overlay" id="sidebar-overlay"></div>

  <!-- ===== SIDEBAR ===== -->
  <aside class="admin-sidebar" id="admin-sidebar" aria-label="Menú de administración">
    <div class="sidebar-header">
      <!-- Solo logo, sin texto "Regresa a Casa / Panel Admin" -->
      <a href="admin.php" class="sidebar-logo" aria-label="Ir al sitio público">
        <img src="../assets/images/AlumniTransparente.png" alt="Logo UABC Alumni" width="128" height="128" loading="lazy">
      </a>
      <!-- Botón X para cerrar sidebar en móvil -->
      <button class="sidebar-close" id="sidebar-close-btn" aria-label="Cerrar menú">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>

    <nav class="sidebar-nav" aria-label="Navegación admin">
      <ul role="list">
        <li>
          <button class="sidebar-link active" data-section="dashboard" onclick="showSection('dashboard')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
          </button>
        </li>
        <li>
          <button class="sidebar-link" data-section="eventos" onclick="showSection('eventos')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Eventos
          </button>
        </li>
        <li>
          <button class="sidebar-link" data-section="registros" onclick="showSection('registros')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Asistentes
          </button>
        </li>
        <li>
          <button class="sidebar-link" data-section="faq" onclick="showSection('faq')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            FAQ
          </button>
        </li>
        <li>
          <button class="sidebar-link" data-section="qr" onclick="showSection('qr')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="5" height="5"/><rect x="16" y="3" width="5" height="5"/><rect x="3" y="16" width="5" height="5"/><path d="M21 16h-3v3"/><path d="M18 21h3"/><path d="M11 3v3"/><path d="M3 11h3"/><path d="M11 11h3v3"/><path d="M14 14v3"/><path d="M11 17h3"/><path d="M11 21v-3"/></svg>
            Validar QR
          </button>
        </li>
        <li>
          <button class="sidebar-link" data-section="usuarios" onclick="showSection('usuarios')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Usuarios
          </button>
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <a href="php/logout.php" class="sidebar-link" style="color: #d32f2f;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Cerrar Sesión
      </a>
    </div>
  </aside>
