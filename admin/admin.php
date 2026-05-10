<?php include 'php/auth_check.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin – Regresa a Casa UABC</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">

  <!-- ===== SIDEBAR ===== -->
  <aside class="admin-sidebar" id="admin-sidebar" aria-label="Menú de administración">
    <div class="sidebar-header">
      <a href="../index.html" class="sidebar-logo" aria-label="Ir al sitio público">
        <img src="../assets/images/AlumniTransparente.png" alt="Logo UABC Alumni" width="36" height="36" loading="lazy">
        <div class="sidebar-logo-text">
          <span class="sidebar-logo-title">Regresa a Casa</span>
          <span class="sidebar-logo-sub">Panel Admin</span>
        </div>
      </a>
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
      </ul>
    </nav>

    <div class="sidebar-footer">
      <a href="php/logout.php" class="sidebar-link" style="color: #d32f2f;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Cerrar Sesión
      </a>
    </div>

    
  </aside>

  <!-- ===== LAYOUT PRINCIPAL ===== -->
  <div class="admin-layout">

    <!-- TOPBAR -->
    <header class="admin-topbar">
      <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Abrir menú" onclick="toggleSidebar()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <h1 class="admin-topbar-title" id="topbar-title">Dashboard</h1>
      <div class="admin-topbar-actions">
        <span class="admin-user-badge">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Administrador
        </span>
      </div>
    </header>

    <!-- MAIN -->
    <main class="admin-main" id="admin-main">

      <!-- ==============================
           SECCIÓN: DASHBOARD
      ============================== -->
      <section class="admin-section active" id="section-dashboard">
        <div class="admin-section-header">
          <h2 class="admin-section-title">Dashboard</h2>
          <p class="admin-section-desc">Resumen general de todos los eventos</p>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
          <div class="kpi-card">
            <div class="kpi-icon kpi-icon--green">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div class="kpi-data">
              <span class="kpi-value" data-kpi="total-registros">248</span>
              <span class="kpi-label">Total Registros</span>
            </div>
          </div>
          <div class="kpi-card">
            <div class="kpi-icon kpi-icon--gold">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="kpi-data">
              <span class="kpi-value" data-kpi="total-eventos">3</span>
              <span class="kpi-label">Eventos Activos</span>
            </div>
          </div>
          <div class="kpi-card">
            <div class="kpi-icon kpi-icon--green">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <div class="kpi-data">
              <span class="kpi-value" data-kpi="confirmados">231</span>
              <span class="kpi-label">QR Confirmados</span>
            </div>
          </div>
          <div class="kpi-card">
            <div class="kpi-icon kpi-icon--gold">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div class="kpi-data">
              <span class="kpi-value" data-kpi="correos">248</span>
              <span class="kpi-label">Correos Enviados</span>
            </div>
          </div>
        </div>

        <!-- Tabla resumen por evento -->
        <div class="admin-card">
          <div class="admin-card-header">
            <h3 class="admin-card-title">Registros por Evento</h3>
            <button class="btn btn-primary btn-sm" onclick="alert('Función Excel: se implementará con PHP')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Exportar Excel
            </button>
          </div>
          <div class="table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Evento</th>
                  <th>Campus</th>
                  <th>Fecha</th>
                  <th>Registros</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Regresa a Casa – Tijuana 2025</strong></td>
                  <td>Tijuana</td>
                  <td>15 Ago 2025</td>
                  <td><span class="badge badge--green">142</span></td>
                  <td><span class="status-dot status-dot--active"></span> Activo</td>
                  <td class="table-actions">
                    <button class="btn-icon" title="Ver asistentes" onclick="showSection('registros')" aria-label="Ver asistentes">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button class="btn-icon" title="Editar evento" onclick="openModalEvento('Regresa a Casa – Tijuana 2025')" aria-label="Editar evento">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-icon btn-icon--reminder" title="Enviar recordatorio" onclick="openModalRecordatorio('Regresa a Casa – Tijuana 2025')" aria-label="Enviar recordatorio">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td><strong>Regresa a Casa – Mexicali 2025</strong></td>
                  <td>Mexicali</td>
                  <td>22 Oct 2025</td>
                  <td><span class="badge badge--green">106</span></td>
                  <td><span class="status-dot status-dot--active"></span> Activo</td>
                  <td class="table-actions">
                    <button class="btn-icon" title="Ver asistentes" onclick="showSection('registros')" aria-label="Ver asistentes">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                    <button class="btn-icon" title="Editar evento" onclick="openModalEvento('Regresa a Casa – Mexicali 2025')" aria-label="Editar evento">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-icon btn-icon--reminder" title="Enviar recordatorio" onclick="openModalRecordatorio('Regresa a Casa – Mexicali 2025')" aria-label="Enviar recordatorio">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </button>
                  </td>
                </tr>
                <tr class="row--muted">
                  <td><strong>Regresa a Casa – Ensenada 2025</strong></td>
                  <td>Ensenada</td>
                  <td>Dic 2025</td>
                  <td><span class="badge badge--gray">0</span></td>
                  <td><span class="status-dot status-dot--pending"></span> Próximamente</td>
                  <td class="table-actions">
                    <button class="btn-icon" title="Editar evento" onclick="openModalEvento('Regresa a Casa – Ensenada 2025')" aria-label="Editar evento">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ==============================
           SECCIÓN: EVENTOS
      ============================== -->
      <section class="admin-section" id="section-eventos">
        <div class="admin-section-header">
          <h2 class="admin-section-title">Gestión de Eventos</h2>
          <button class="btn btn-primary" onclick="openModalEvento(null)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nuevo Evento
          </button>
        </div>

        <div class="events-admin-grid" id="events-container">
          <p>Cargando eventos...</p>
        </div>
      </section>

      <!-- ==============================
           SECCIÓN: REGISTROS / ASISTENTES
      ============================== -->
      <section class="admin-section" id="section-registros">
        <div class="admin-section-header">
          <h2 class="admin-section-title">Asistentes Registrados</h2>
          <div class="admin-section-actions">
            <select class="form-select form-select--sm" id="filtro-evento" aria-label="Filtrar por evento">
              <option value="">Todos los eventos</option>
              <option value="tijuana">Tijuana 2025</option>
              <option value="mexicali">Mexicali 2025</option>
              <option value="ensenada">Ensenada 2025</option>
            </select>
            <button class="btn btn-primary btn-sm" onclick="alert('Exportar Excel – requiere backend PHP')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Exportar Excel
            </button>
            <button class="btn btn-ghost btn-sm" onclick="openModalRecordatorio('evento seleccionado')">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              Enviar Recordatorio
            </button>
          </div>
        </div>

        <div class="admin-card">
          <div class="table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Correo</th>
                  <th>Campus</th>
                  <th>Carrera</th>
                  <th>Generación</th>
                  <th>Tipo</th>
                  <th>Evento</th>
                  <th>QR</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="tabla-asistentes-body">
                <tr>
                  <td colspan="9" style="text-align: center; padding: 20px;">
                    <span class="spinner" style="display:inline-block; width:20px; height:20px; border:2px solid #ccc; border-top-color:#00713d; border-radius:50%; animation:spin 1s linear infinite;"></span>
                    Cargando asistentes...
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ==============================
           SECCIÓN: FAQ
      ============================== -->
      <section class="admin-section" id="section-faq">
        <div class="admin-section-header">
          <h2 class="admin-section-title">Preguntas Frecuentes</h2>
          <button class="btn btn-primary" onclick="openModalFaq(null)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Nueva Pregunta
          </button>
        </div>

        <div class="admin-card">
          <div class="faq-admin-filter">
            <label for="faq-filtro-evento" class="form-label">Evento:</label>
            <select id="faq-filtro-evento" class="form-select form-select--sm" aria-label="Filtrar FAQ por evento">
              <option value="">Todos los eventos</option>
              <option value="tijuana">Tijuana 2025</option>
              <option value="mexicali">Mexicali 2025</option>
              <option value="ensenada">Ensenada 2025</option>
            </select>
          </div>

          <div class="table-wrap">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Pregunta</th>
                  <th>Respuesta</th>
                  <th>Evento</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>1</td>
                  <td>¿Hay estacionamiento disponible?</td>
                  <td class="td-truncate">Sí, estacionamiento gratuito para egresados registrados...</td>
                  <td>Todos</td>
                  <td class="table-actions">
                    <button class="btn-icon" title="Editar" onclick="openModalFaq('estacionamiento')" aria-label="Editar pregunta">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-icon btn-icon--danger" title="Eliminar" onclick="confirmarEliminarFaq(1)" aria-label="Eliminar pregunta">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                  </td>
                </tr>
                <tr>
                  <td>2</td>
                  <td>¿A qué hora puedo ingresar?</td>
                  <td class="td-truncate">Las puertas abren 30 minutos antes del evento...</td>
                  <td>Todos</td>
                  <td class="table-actions">
                    <button class="btn-icon" title="Editar" onclick="openModalFaq('acceso')" aria-label="Editar">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <button class="btn-icon btn-icon--danger" title="Eliminar" onclick="confirmarEliminarFaq(2)" aria-label="Eliminar">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- ==============================
           SECCIÓN: VALIDAR QR
      ============================== -->
      <section class="admin-section" id="section-qr">
        <div class="admin-section-header">
          <h2 class="admin-section-title">Validar QR de Acceso</h2>
          <p class="admin-section-desc">Ingresa o escanea el código QR del asistente para verificar su registro.</p>
        </div>

        <div class="qr-layout">
          <div class="admin-card qr-card">
            <h3 class="admin-card-title">Escanear con Cámara</h3>
            <p style="margin-bottom: 15px; font-size: 0.9rem; color: #666;">Abre esta página en tu celular para usar la cámara trasera.</p>
            
            <div id="lector-camara" style="width: 100%; max-width: 400px; margin: 0 auto;"></div>
            
        </div>

        <div class="qr-layout">
          <div class="admin-card qr-card">
            <h3 class="admin-card-title">Verificar por código</h3>
            <div class="form-group">
              <label for="qr-input" class="form-label">Código QR o ID de registro</label>
              <div class="qr-input-row">
                <input type="text" id="qr-input" class="form-input" placeholder="Ej. UABC-TJ-2025-00142" aria-label="Ingresa el código QR">
                <button class="btn btn-primary" onclick="validarQR()">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                  Validar
                </button>
              </div>
            </div>

            <div class="qr-result" id="qr-result" hidden>
              <div class="qr-result-card" id="qr-result-content">
                <div class="qr-result-icon">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <div>
                  <p class="qr-result-status">Registro válido</p>
                  <p class="qr-result-name" id="qr-result-name">Ana Martínez López</p>
                  <p class="qr-result-detail" id="qr-result-detail">Tijuana 2025 · Egresado · Ing. Sistemas</p>
                </div>
              </div>
            </div>
          </div>

          <div class="admin-card qr-devices-card">
            <h3 class="admin-card-title">Dispositivos compatibles</h3>
            <ul class="qr-devices-list">
              <li>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                <span><strong>Celular / Tablet</strong> – Mostrar QR en pantalla o lector de cámara</span>
              </li>
              <li>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span><strong>Laptop / PC</strong> – Código manual o lector externo USB</span>
              </li>
              <li>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <span><strong>Lector QR dedicado</strong> – Conectar por USB, funciona como teclado</span>
              </li>
            </ul>
            <p class="qr-devices-note">El lector dedicado envía automáticamente el código al campo de texto y dispara la validación.</p>
          </div>
        </div>
      </section>

    </main>
  </div>

  <!-- ===== MODAL: CREAR / EDITAR EVENTO ===== -->
  <div class="modal-overlay" id="modal-evento" role="dialog" aria-modal="true" aria-labelledby="modal-evento-title" hidden>
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title" id="modal-evento-title">Nuevo Evento</h2>
        <button class="modal-close" onclick="closeAdminModal('modal-evento')" aria-label="Cerrar">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-evento" novalidate>
          <input type="hidden" id="ev-id" name="id">

          <div class="form-group">
            <label for="ev-nombre" class="form-label">Nombre del evento <span class="required">*</span></label>
            <input type="text" id="ev-nombre" class="form-input" placeholder="Ej. Regresa a Casa – Tijuana 2026">
          </div>
      
          <div class="form-group">
            <label for="ev-descripcion" class="form-label">Descripción</label>
            <textarea id="ev-descripcion" class="form-input" rows="3" placeholder="Descripción del evento..."></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="ev-fecha" class="form-label">Fecha <span class="required">*</span></label>
              <input type="date" id="ev-fecha" class="form-input">
            </div>
            <div class="form-group">
              <label for="ev-hora" class="form-label">Hora <span class="required">*</span></label>
              <input type="time" id="ev-hora" class="form-input">
            </div>
          </div>
          <div class="form-group">
            <label for="ev-ubicacion" class="form-label">Ubicación <span class="required">*</span></label>
            <input type="text" id="ev-ubicacion" class="form-input" placeholder="Ej. Campus Tijuana – Auditorio Central">
          </div>
          <div class="form-group">
            <label for="ev-imagen" class="form-label">URL de imagen del evento</label>
            <input type="url" id="ev-imagen" class="form-input" placeholder="https://...">
          </div>
          <div class="form-group">
            <label for="ev-estado" class="form-label">Estado</label>
            <select id="ev-estado" class="form-select">
              <option value="activo">Activo</option>
              <option value="proximo">Próximamente</option>
              <option value="cerrado">Cerrado</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeAdminModal('modal-evento')">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Evento</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ===== MODAL: RECORDATORIO ===== -->
  <div class="modal-overlay" id="modal-recordatorio" role="dialog" aria-modal="true" aria-labelledby="modal-rec-title" hidden>
    <div class="modal modal--sm">
      <div class="modal-header">
        <h2 class="modal-title" id="modal-rec-title">Enviar Recordatorio</h2>
        <button class="modal-close" onclick="closeAdminModal('modal-recordatorio')" aria-label="Cerrar">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="modal-body">
        <p class="modal-event-name" id="modal-rec-evento"></p>
        <div class="form-group">
          <label class="form-label">Tipo de envío</label>
          <div class="radio-group">
            <label class="radio-label"><input type="radio" name="rec-tipo" value="todos" checked> Todos los registrados</label>
            <label class="radio-label"><input type="radio" name="rec-tipo" value="sin-qr"> Solo los que no tienen QR</label>
          </div>
        </div>
        <div class="form-group">
          <label for="rec-mensaje" class="form-label">Mensaje personalizado (opcional)</label>
          <textarea id="rec-mensaje" class="form-input" rows="3" placeholder="Recuerda que el evento es en 2 días..."></textarea>
        </div>
        <div class="reminder-info">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <span>El correo incluirá automáticamente el QR, fecha, hora y ubicación del evento.</span>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" onclick="closeAdminModal('modal-recordatorio')">Cancelar</button>
          <button type="button" class="btn btn-primary" onclick="alert('Envío de recordatorio – requiere backend PHP'); closeAdminModal('modal-recordatorio')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Enviar Recordatorio
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== MODAL: DETALLE ASISTENTE ===== -->
  <div class="modal-overlay" id="modal-asistente" role="dialog" aria-modal="true" aria-labelledby="modal-asi-title" hidden>
    <div class="modal modal--sm">
      <div class="modal-header">
        <h2 class="modal-title" id="modal-asi-title">Detalle del Asistente</h2>
        <button class="modal-close" onclick="closeAdminModal('modal-asistente')" aria-label="Cerrar">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="modal-body">
        <div class="asistente-detail" id="asistente-detail-content">
          <div class="asistente-avatar" aria-hidden="true">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <h3 class="asistente-name" id="asi-nombre">Ana Martínez López</h3>
          <div class="asistente-fields">
            <div class="asistente-field"><span class="asistente-field-label">Correo</span><span id="asi-email">ana.martinez@uabc.edu.mx</span></div>
            <div class="asistente-field"><span class="asistente-field-label">Teléfono</span><span id="asi-tel">(664) 123-4567</span></div>
            <div class="asistente-field"><span class="asistente-field-label">Campus</span><span id="asi-campus">Tijuana</span></div>
            <div class="asistente-field"><span class="asistente-field-label">Facultad</span><span id="asi-facultad">FCITEC</span></div>
            <div class="asistente-field"><span class="asistente-field-label">Carrera</span><span id="asi-carrera">Ing. en Sistemas Computacionales</span></div>
            <div class="asistente-field"><span class="asistente-field-label">Generación</span><span id="asi-gen">2015-2020</span></div>
            <div class="asistente-field"><span class="asistente-field-label">Tipo</span><span id="asi-tipo">Egresado</span></div>
            <div class="asistente-field"><span class="asistente-field-label">Evento</span><span id="asi-evento">Tijuana 2025</span></div>
            <div class="asistente-field"><span class="asistente-field-label">QR</span><span class="badge badge--green">Enviado</span></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" onclick="closeAdminModal('modal-asistente')">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== MODAL: FAQ ===== -->
  <div class="modal-overlay" id="modal-faq" role="dialog" aria-modal="true" aria-labelledby="modal-faq-title" hidden>
    <div class="modal modal--sm">
      <div class="modal-header">
        <h2 class="modal-title" id="modal-faq-title">Nueva Pregunta Frecuente</h2>
        <button class="modal-close" onclick="closeAdminModal('modal-faq')" aria-label="Cerrar">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="modal-body">
        <form id="form-faq" novalidate>
          <div class="form-group">
            <label for="faq-pregunta" class="form-label">Pregunta <span class="required">*</span></label>
            <input type="text" id="faq-pregunta" class="form-input" placeholder="¿Cuál es la pregunta?">
          </div>
          <div class="form-group">
            <label for="faq-respuesta" class="form-label">Respuesta <span class="required">*</span></label>
            <textarea id="faq-respuesta" class="form-input" rows="4" placeholder="Escribe la respuesta..."></textarea>
          </div>
          <div class="form-group">
            <label for="faq-evento-asoc" class="form-label">Asociar a evento</label>
            <select id="faq-evento-asoc" class="form-select">
              <option value="">Todos los eventos</option>
              <option value="tijuana">Tijuana 2025</option>
              <option value="mexicali">Mexicali 2025</option>
              <option value="ensenada">Ensenada 2025</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="closeAdminModal('modal-faq')">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar Pregunta</button>
          </div>
        </form>
      </div>
    </div>
  </div>
  
  <div class="modal-overlay" id="modal-evento-exito" role="dialog" aria-modal="true" hidden>
    <div class="modal modal--sm">
      <div class="modal-header">
        <h2 class="modal-title">Evento Creado</h2>
        <button class="modal-close" onclick="closeAdminModal('modal-evento-exito')">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="modal-body modal-body--center" style="align-items: center; text-align: center;">
        <p class="modal-event-name" style="margin-bottom: var(--space-4);">¡El evento se ha creado correctamente!</p>
        
        <div id="qrcode-container" style="display: flex; justify-content: center; background: white; padding: 10px; border-radius: 8px; margin-bottom: var(--space-4);"></div>
        
        <div class="form-group" style="width: 100%; text-align: left;">
          <label class="form-label">Link del formulario:</label>
          <div class="qr-input-row" style="display: flex; gap: 10px;">
            <input type="text" id="event-link-input" class="form-input" readonly>
            <button type="button" class="btn btn-secondary btn-sm" onclick="copyEventLink()">Copiar</button>
          </div>
        </div>

        <div class="modal-footer" style="width: 100%; margin-top: var(--space-4); padding: 0;">
          <button type="button" class="btn btn-primary" onclick="downloadQR()" style="width: 100%; justify-content: center;">Descargar QR</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="../assets/js/main.js"></script>
  <script src="../assets/js/admin.js"></script>
  <script src="../assets/js/qr-scanner.js"></script>
</body>
</html>
