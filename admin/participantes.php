<?php include 'php/auth_check.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Participantes del Evento - UABC</title>
  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/participantes.css">
</head>
<body class="admin-body">

  <header class="p-header">
    <div class="p-container">
      <a href="admin.php" class="back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Volver al Panel
      </a>
      <h1 id="event-title">Participantes</h1>
    </div>
  </header>

  <main class="p-container">

    <!-- ===== BARRA DE FILTROS ===== -->
    <div class="filters-bar card">

      <!-- Buscar por nombre -->
      <div class="filter-group">
        <label class="filter-label" for="search-nombre">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Buscar
        </label>
        <input class="filter-input" type="search" id="search-nombre" placeholder="Nombre o apellido..." autocomplete="off">
      </div>

      <!-- Filtro: Tipo -->
      <div class="filter-group">
        <label class="filter-label" for="filter-tipo">Tipo</label>
        <select class="filter-select" id="filter-tipo">
          <option value="">Todos</option>
          <option value="egresado">Egresado</option>
          <option value="estudiante">Estudiante</option>
          <option value="docente">Docente</option>
        </select>
      </div>

      <!-- Filtro: Estatus -->
      <div class="filter-group">
        <label class="filter-label" for="filter-estatus">Estatus</label>
        <select class="filter-select" id="filter-estatus">
          <option value="">Todos</option>
          <option value="confirmado">Confirmado</option>
          <option value="pendiente">Pendiente</option>
          <option value="cancelado">Cancelado</option>
        </select>
      </div>

      <!-- Filtro: QR -->
      <div class="filter-group">
        <label class="filter-label" for="filter-qr">QR</label>
        <select class="filter-select" id="filter-qr">
          <option value="">Todos</option>
          <option value="enviado">Enviado</option>
          <option value="no_enviado">No enviado</option>
        </select>
      </div>

      <!-- Contador de resultados -->
      <div class="filter-count">
        <span id="result-count">0</span> registros
      </div>

    </div>
    <!-- /FILTROS -->

    <!-- ===== TABLA ===== -->
    <div class="admin-table-wrapper card">
      <table class="admin-table" id="tabla-principal">
        <thead>
          <tr>
            <th class="sortable" data-col="0" data-type="str">
              Nombre
              <span class="sort-icon" aria-hidden="true">
                <svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg>
              </span>
            </th>
            <th class="sortable" data-col="1" data-type="str">
              Correo
              <span class="sort-icon" aria-hidden="true">
                <svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg>
              </span>
            </th>
            <th>Campus</th>
            <th class="sortable" data-col="3" data-type="str">
              Facultad
              <span class="sort-icon" aria-hidden="true">
                <svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg>
              </span>
            </th>
            <th class="sortable" data-col="4" data-type="str">
              Carrera
              <span class="sort-icon" aria-hidden="true">
                <svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg>
              </span>
            </th>
            <th class="sortable" data-col="5" data-type="str">
              Tipo
              <span class="sort-icon" aria-hidden="true">
                <svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg>
              </span>
            </th>
            <th class="sortable" data-col="6" data-type="str">
              Estatus
              <span class="sort-icon" aria-hidden="true">
                <svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg>
              </span>
            </th>
            <th class="sortable" data-col="7" data-type="str">
              QR
              <span class="sort-icon" aria-hidden="true">
                <svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg>
              </span>
            </th>
          </tr>
        </thead>
        <tbody id="tabla-asistentes">
          <tr><td colspan="8" class="loading-cell">Cargando asistentes...</td></tr>
        </tbody>
      </table>

      <!-- Empty state -->
      <div class="table-empty" id="table-empty" hidden>
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <p>No se encontraron registros con los filtros aplicados.</p>
        <button class="btn btn-ghost" id="btn-clear-filters">Limpiar filtros</button>
      </div>
    </div>

  </main>

<script>
document.addEventListener('DOMContentLoaded', () => {

  // ── URL params ──
  const params = new URLSearchParams(window.location.search);
  const eventoId = params.get('id');
  if (!eventoId) {
    alert('ID de evento no encontrado');
    window.location.href = 'admin.php';
    return;
  }

  // ── Estado ──
  let allData   = [];
  let sortCol   = -1;
  let sortDir   = 'asc'; // 'asc' | 'desc'

  const tbody     = document.getElementById('tabla-asistentes');
  const emptyBox  = document.getElementById('table-empty');
  const countEl   = document.getElementById('result-count');

  // Filtros
  const inputNombre  = document.getElementById('search-nombre');
  const selTipo      = document.getElementById('filter-tipo');
  const selEstatus   = document.getElementById('filter-estatus');
  const selQr        = document.getElementById('filter-qr');

  // ── Fetch ──
  fetch(`php/get_asistentes_evento.php?evento_id=${eventoId}`)
    .then(r => r.json())
    .then(result => {
      if (result.status === 'success') {
        allData = result.data;
        renderTable(allData);
      } else {
        tbody.innerHTML = `<tr><td colspan="8" style="color:var(--color-error);text-align:center">${result.message}</td></tr>`;
      }
    })
    .catch(() => {
      tbody.innerHTML = '<tr><td colspan="8" style="text-align:center">Error de conexión.</td></tr>';
    });

  // ── Render ──
  function renderTable(data) {
    tbody.innerHTML = '';
    const tableWrapper = document.querySelector('.admin-table-wrapper');

    if (data.length === 0) {
      emptyBox.hidden = false;
      tableWrapper.querySelector('table').style.display = 'none';
      countEl.textContent = '0';
      return;
    }

    emptyBox.hidden = true;
    tableWrapper.querySelector('table').style.display = '';
    countEl.textContent = data.length;

    data.forEach(a => {
      const facultad = a.facultad_nombre || a.facultad_otra || 'N/A';
      const carrera  = a.carrera_nombre  || a.carrera_otra  || 'N/A';
      const nombre   = `${a.apellidos || ''}, ${a.nombre || ''}`;
      const estatus  = a.estatus || 'pendiente';
      // FIX: el campo en BD es correo_enviado, no qr_enviado
      const qr       = a.correo_enviado == 1 ? 'enviado' : 'no_enviado';
      const tipo     = a.tipo_asistente || 'N/A';

      // badges
      const badgeEstatus = `<span class="badge badge--${estatus}">${capitalize(estatus)}</span>`;
      const badgeQr      = qr === 'enviado'
        ? `<span class="badge badge--success">Enviado</span>`
        : `<span class="badge badge--gray">No enviado</span>`;
      const badgeTipo    = `<span class="badge badge--gray">${capitalize(tipo)}</span>`;

      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td data-label="Nombre"><strong>${nombre}</strong></td>
        <td data-label="Correo">${a.correo || 'N/A'}</td>
        <td data-label="Campus">${a.campus_nombre || 'N/A'}</td>
        <td data-label="Facultad">${facultad}</td>
        <td data-label="Carrera">${carrera}</td>
        <td data-label="Tipo">${badgeTipo}</td>
        <td data-label="Estatus">${badgeEstatus}</td>
        <td data-label="QR">${badgeQr}</td>
      `;
      // guardar valores originales para sort/filter
      tr.dataset.nombre  = nombre.toLowerCase();
      tr.dataset.correo  = (a.correo || '').toLowerCase();
      tr.dataset.tipo    = tipo.toLowerCase();
      tr.dataset.estatus = estatus.toLowerCase();
      tr.dataset.qr      = qr;
      tbody.appendChild(tr);
    });
  }

  // ── Filtros: aplicar ──
  function applyFilters() {
    const q       = inputNombre.value.trim().toLowerCase();
    const tipo    = selTipo.value.toLowerCase();
    const estatus = selEstatus.value.toLowerCase();
    const qr      = selQr.value.toLowerCase();

    const rows = Array.from(tbody.querySelectorAll('tr'));
    let visible = 0;

    rows.forEach(tr => {
      const matchNombre  = !q       || tr.dataset.nombre.includes(q);
      const matchTipo    = !tipo    || tr.dataset.tipo    === tipo;
      const matchEstatus = !estatus || tr.dataset.estatus === estatus;
      const matchQr      = !qr      || tr.dataset.qr      === qr;

      const show = matchNombre && matchTipo && matchEstatus && matchQr;
      tr.hidden = !show;
      if (show) visible++;
    });

    countEl.textContent = visible;
    emptyBox.hidden = visible > 0;
    document.querySelector('#tabla-principal').style.display = visible === 0 ? 'none' : '';
  }

  inputNombre.addEventListener('input',  applyFilters);
  selTipo.addEventListener('change',    applyFilters);
  selEstatus.addEventListener('change', applyFilters);
  selQr.addEventListener('change',      applyFilters);

  document.getElementById('btn-clear-filters').addEventListener('click', () => {
    inputNombre.value = '';
    selTipo.value = '';
    selEstatus.value = '';
    selQr.value = '';
    applyFilters();
  });

  // ── Sort ──
  document.querySelectorAll('th.sortable').forEach(th => {
    th.addEventListener('click', () => {
      const col = parseInt(th.dataset.col);

      if (sortCol === col) {
        sortDir = sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        sortCol = col;
        sortDir = 'asc';
      }

      // Actualizar clases de iconos
      document.querySelectorAll('th.sortable').forEach(t => {
        t.classList.remove('sort-asc', 'sort-desc');
      });
      th.classList.add(sortDir === 'asc' ? 'sort-asc' : 'sort-desc');

      // Ordenar filas visibles
      const rows = Array.from(tbody.querySelectorAll('tr:not([hidden])'));
      rows.sort((a, b) => {
        const tdA = a.querySelectorAll('td')[col];
        const tdB = b.querySelectorAll('td')[col];
        const valA = tdA ? tdA.innerText.trim().toLowerCase() : '';
        const valB = tdB ? tdB.innerText.trim().toLowerCase() : '';
        return sortDir === 'asc' ? valA.localeCompare(valB, 'es') : valB.localeCompare(valA, 'es');
      });

      rows.forEach(r => tbody.appendChild(r));
    });
  });

  // ── Helpers ──
  function capitalize(s) {
    return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
  }

});
</script>
</body>
</html>
