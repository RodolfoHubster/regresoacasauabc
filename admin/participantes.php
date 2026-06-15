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
  <link rel="stylesheet" href="../assets/css/toast.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-body">

  <header class="p-header">
    <div class="p-container p-header-inner">
      <a href="admin.php" class="back-link" aria-label="Volver al Panel">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Volver al Panel
      </a>
      <h1 id="event-title" class="p-header-title">Cargando evento…</h1>
    </div>
  </header>

  <main class="p-container p-main">

    <nav class="tabs-nav" role="tablist" aria-label="Secciones">
      <button class="tab-btn active" id="tab-lista" role="tab" aria-selected="true" aria-controls="content-lista" onclick="switchTab('lista')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
          <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
          <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
        </svg>
        Lista de Asistentes
      </button>
      <button class="tab-btn" id="tab-stats" role="tab" aria-selected="false" aria-controls="content-stats" onclick="switchTab('stats')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <line x1="18" y1="20" x2="18" y2="10"/>
          <line x1="12" y1="20" x2="12" y2="4"/>
          <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        Estadísticas y Gráficas
      </button>
    </nav>

    <!-- ── LISTA ── -->
    <div id="content-lista">
      <div class="filters-bar card">
        <div class="filter-group">
          <label class="filter-label" for="search-nombre">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Buscar
          </label>
          <input class="filter-input" type="search" id="search-nombre" placeholder="Nombre o correo…" autocomplete="off">
        </div>

        <div class="filter-group">
          <label class="filter-label" for="filter-tipo">Tipo</label>
          <select class="filter-select" id="filter-tipo">
            <option value="">Todos</option>
            <option value="egresado">Egresado</option>
            <option value="estudiante">Estudiante</option>
            <option value="docente">Docente</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label" for="filter-estatus">Estatus</label>
          <select class="filter-select" id="filter-estatus">
            <option value="">Todos</option>
            <option value="confirmado">Confirmado</option>
            <option value="pendiente">Pendiente</option>
            <option value="cancelado">Cancelado</option>
          </select>
        </div>

        <div class="filter-group">
          <label class="filter-label" for="filter-qr">QR</label>
          <select class="filter-select" id="filter-qr">
            <option value="">Todos</option>
            <option value="enviado">Enviado</option>
            <option value="no_enviado">No enviado</option>
          </select>
        </div>

        <div class="filter-actions">
          <div class="filter-count">
            <span id="result-count">0</span> registros visibles &nbsp;|&nbsp;
            <strong id="total-count">0</strong> total en el evento
          </div>
          <div class="filter-buttons">
            <button class="btn-icon" id="btn-exportar-excel" title="Exportar Excel" aria-label="Exportar a Excel">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Excel
            </button>
            <button class="btn-icon" id="btn-imprimir" title="Imprimir tabla" aria-label="Imprimir">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
              Imprimir
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state fuera de la filters-bar -->
      <div class="table-empty" id="table-empty" hidden>
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <p>No se encontraron registros con los filtros aplicados.</p>
        <button class="btn-ghost" id="btn-clear-filters">Limpiar filtros</button>
      </div>

      <!-- Skeleton -->
      <div class="admin-table-wrapper card" id="skeleton-wrapper">
        <table class="admin-table skeleton-table" aria-hidden="true">
          <thead>
            <tr><th>Nombre</th><th>Correo</th><th>Campus</th><th>Facultad</th><th>Carrera</th><th>Tipo</th><th>Estatus</th><th>QR</th></tr>
          </thead>
          <tbody>
            <?php for ($i = 0; $i < 8; $i++): ?>
            <tr class="skeleton-row">
              <td><span class="skeleton skeleton-text" style="width:140px"></span></td>
              <td><span class="skeleton skeleton-text" style="width:180px"></span></td>
              <td><span class="skeleton skeleton-text" style="width:80px"></span></td>
              <td><span class="skeleton skeleton-text" style="width:100px"></span></td>
              <td><span class="skeleton skeleton-text" style="width:120px"></span></td>
              <td><span class="skeleton skeleton-text" style="width:70px"></span></td>
              <td><span class="skeleton skeleton-text" style="width:80px"></span></td>
              <td><span class="skeleton skeleton-text" style="width:75px"></span></td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>

      <!-- Tabla real -->
      <div class="admin-table-wrapper card" id="tabla-wrapper" hidden>
        <table class="admin-table" id="tabla-principal">
          <thead>
            <tr>
              <th class="sortable" data-col="0">Nombre <span class="sort-icon" aria-hidden="true"><svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg></span></th>
              <th class="sortable" data-col="1">Correo <span class="sort-icon" aria-hidden="true"><svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg></span></th>
              <th>Campus</th>
              <th class="sortable" data-col="3">Facultad <span class="sort-icon" aria-hidden="true"><svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg></span></th>
              <th class="sortable" data-col="4">Carrera <span class="sort-icon" aria-hidden="true"><svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg></span></th>
              <th class="sortable" data-col="5">Tipo <span class="sort-icon" aria-hidden="true"><svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg></span></th>
              <th class="sortable" data-col="6">Estatus <span class="sort-icon" aria-hidden="true"><svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg></span></th>
              <th class="sortable" data-col="7">QR <span class="sort-icon" aria-hidden="true"><svg class="sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 9l4-4 4 4M16 15l-4 4-4-4"/></svg></span></th>
            </tr>
          </thead>
          <tbody id="tabla-asistentes"></tbody>
        </table>
      </div>
    </div>

    <!-- ── ESTADÍSTICAS ── -->
    <div id="content-stats" hidden>
      <div class="stats-container">

        <div class="card stats-card">
          <div class="stats-card-header">
            <label class="filter-label" for="search-facultad-input">Filtrar por Facultad</label>
            <input type="text" id="search-facultad-input" class="filter-input" placeholder="Escribe el nombre de la facultad…" autocomplete="off">
          </div>
          <div class="table-scroll-y">
            <table class="mini-table">
              <thead>
                <tr>
                  <th>Facultad / Unidad Académica</th>
                  <th class="col-center col-narrow">Registros</th>
                </tr>
              </thead>
              <tbody id="tabla-resumen-facultades"></tbody>
            </table>
          </div>
        </div>

        <div class="mini-charts-grid">
          <div class="card mini-chart"><canvas id="chart-estatus"></canvas></div>
          <div class="card mini-chart"><canvas id="chart-qr"></canvas></div>
        </div>

        <div class="card chart-wrapper">
          <div class="chart-scroll-area">
            <div id="chart-container-inner" style="position:relative;width:100%;">
              <canvas id="chart-facultades"></canvas>
            </div>
          </div>
        </div>

      </div>
    </div>

  </main>

<script src="../assets/js/toast.js"></script>
<script>
function switchTab(tab) {
  const tabLista    = document.getElementById('tab-lista');
  const tabStats    = document.getElementById('tab-stats');
  const contentLista = document.getElementById('content-lista');
  const contentStats = document.getElementById('content-stats');

  if (tab === 'lista') {
    tabLista.classList.add('active');    tabStats.classList.remove('active');
    tabLista.setAttribute('aria-selected', 'true');
    tabStats.setAttribute('aria-selected', 'false');
    contentLista.hidden = false;        contentStats.hidden = true;
  } else {
    tabStats.classList.add('active');   tabLista.classList.remove('active');
    tabStats.setAttribute('aria-selected', 'true');
    tabLista.setAttribute('aria-selected', 'false');
    contentStats.hidden = false;        contentLista.hidden = true;
  }
}

document.addEventListener('DOMContentLoaded', () => {

  const params   = new URLSearchParams(window.location.search);
  const eventoId = params.get('id');
  if (!eventoId) {
    showToast('ID de evento no encontrado', 'error');
    setTimeout(() => { window.location.href = 'admin.php'; }, 2000);
    return;
  }

  let allData = [];
  let sortCol = -1;
  let sortDir = 'asc';
  let myChartInstance = null;
  let chartEstatus    = null;
  let chartQR         = null;

  const skeletonWrapper = document.getElementById('skeleton-wrapper');
  const tablaWrapper    = document.getElementById('tabla-wrapper');
  const tbody           = document.getElementById('tabla-asistentes');
  const emptyBox        = document.getElementById('table-empty');
  const countEl         = document.getElementById('result-count');
  const totalCountEl    = document.getElementById('total-count');
  const inputNombre     = document.getElementById('search-nombre');
  const selTipo         = document.getElementById('filter-tipo');
  const selEstatus      = document.getElementById('filter-estatus');
  const selQr           = document.getElementById('filter-qr');
  const inputSearchFac  = document.getElementById('search-facultad-input');

  function showSkeleton() { skeletonWrapper.hidden = false; tablaWrapper.hidden = true;  emptyBox.hidden = true; }
  function showTable()    { skeletonWrapper.hidden = true;  tablaWrapper.hidden = false; emptyBox.hidden = true; }
  function showEmpty()    { skeletonWrapper.hidden = true;  tablaWrapper.hidden = true;  emptyBox.hidden = false; }

  showSkeleton();

  fetch(`php/get_asistentes_evento.php?evento_id=${eventoId}`)
    .then(r => r.json())
    .then(result => {
      if (result.status === 'success') {
        allData = result.data;
        if (result.evento_nombre) {
          document.getElementById('event-title').textContent = result.evento_nombre;
          document.title = result.evento_nombre + ' – Participantes';
        }
        if (totalCountEl) totalCountEl.textContent = allData.length;
        renderTable(allData);
        renderEstadisticasYGrafica(allData);
        showToast(`${allData.length} registro(s) cargados`, 'success');
      } else {
        showEmpty();
        showToast(result.message || 'No se pudieron cargar los datos', 'error');
      }
    })
    .catch(() => {
      showEmpty();
      showToast('Error de conexión al cargar participantes', 'error');
    });

  function renderTable(data) {
    tbody.innerHTML = '';
    if (data.length === 0) { showEmpty(); countEl.textContent = '0'; return; }
    showTable();
    countEl.textContent = data.length;

    data.forEach(a => {
      const facultad = a.facultad_nombre || a.facultad_otra || 'N/A';
      const carrera  = a.carrera_nombre  || a.carrera_otra  || 'N/A';
      const nombre   = `${a.apellidos || ''}, ${a.nombre || ''}`;
      const estatus  = a.estatus || 'pendiente';
      const qr       = a.correo_enviado == 1 ? 'enviado' : 'no_enviado';
      const tipo     = a.tipo_asistente || 'N/A';

      const badgeEstatus = `<span class="badge badge--${estatus}">${cap(estatus)}</span>`;
      const badgeQr      = qr === 'enviado'
        ? `<span class="badge badge--success">Enviado</span>`
        : `<span class="badge badge--gray">No enviado</span>`;
      const badgeTipo = `<span class="badge badge--gray">${cap(tipo)}</span>`;

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
      tr.dataset.nombre  = nombre.toLowerCase();
      tr.dataset.correo  = (a.correo || '').toLowerCase();
      tr.dataset.tipo    = tipo.toLowerCase();
      tr.dataset.estatus = estatus.toLowerCase();
      tr.dataset.qr      = qr;
      tbody.appendChild(tr);
    });
  }

  function renderEstadisticasYGrafica(data) {
    const conteoFacultad = {};
    const conteoEstatus  = {};
    const conteoQR       = { 'Enviados': 0, 'No Enviados': 0 };

    data.forEach(a => {
      const facultad = a.facultad_nombre || a.facultad_otra || 'No especificada';
      const estatus  = cap(a.estatus) || 'Pendiente';
      const qr       = a.correo_enviado == 1 ? 'Enviados' : 'No Enviados';
      conteoFacultad[facultad] = (conteoFacultad[facultad] || 0) + 1;
      conteoEstatus[estatus]   = (conteoEstatus[estatus]   || 0) + 1;
      conteoQR[qr]++;
    });

    const facultadesOrdenadas = Object.entries(conteoFacultad).sort((a, b) => b[1] - a[1]);

    const tbodyResumen = document.getElementById('tabla-resumen-facultades');
    tbodyResumen.innerHTML = '';
    facultadesOrdenadas.forEach(([facultad, total]) => {
      const tr = document.createElement('tr');
      tr.className = 'faculty-row-item';
      tr.dataset.facultadName = facultad.toLowerCase();
      tr.innerHTML = `
        <td><strong>${facultad}</strong></td>
        <td class="col-center"><span class="badge-count">${total}</span></td>
      `;
      tbodyResumen.appendChild(tr);
    });

    if (myChartInstance) myChartInstance.destroy();
    if (chartEstatus)    chartEstatus.destroy();
    if (chartQR)         chartQR.destroy();

    chartEstatus = new Chart(document.getElementById('chart-estatus').getContext('2d'), {
      type: 'doughnut',
      data: { labels: Object.keys(conteoEstatus), datasets: [{ data: Object.values(conteoEstatus), backgroundColor: ['#00723F', '#F2A900', '#D0D0D0'] }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Estatus de Confirmación' } } }
    });

    chartQR = new Chart(document.getElementById('chart-qr').getContext('2d'), {
      type: 'doughnut',
      data: { labels: Object.keys(conteoQR), datasets: [{ data: Object.values(conteoQR), backgroundColor: ['#002855', '#D0D0D0'] }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { title: { display: true, text: 'Envío de Códigos QR' } } }
    });

    const canvas = document.getElementById('chart-facultades');
    const labels = facultadesOrdenadas.map(f => f[0].substring(0, 35) + (f[0].length > 35 ? '…' : ''));
    const valores = facultadesOrdenadas.map(f => f[1]);
    const alturaIdeal = Math.max(350, facultadesOrdenadas.length * 45);
    document.getElementById('chart-container-inner').style.height = alturaIdeal + 'px';

    myChartInstance = new Chart(canvas.getContext('2d'), {
      type: 'bar',
      data: {
        labels,
        datasets: [{ label: 'Registros', data: valores, backgroundColor: '#00723F', borderColor: '#002855', borderWidth: 1, borderRadius: 4 }]
      },
      options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, title: { display: true, text: 'Registros por Unidad Académica', color: '#002855', font: { size: 14, weight: 'bold' }, padding: { bottom: 20 } } },
        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
      }
    });
  }

  if (inputSearchFac) {
    inputSearchFac.addEventListener('input', () => {
      const q = inputSearchFac.value.trim().toLowerCase();
      document.querySelectorAll('.faculty-row-item').forEach(tr => {
        tr.hidden = q && !tr.dataset.facultadName.includes(q);
      });
    });
  }

  function applyFilters() {
    const q       = inputNombre.value.trim().toLowerCase();
    const tipo    = selTipo.value.toLowerCase();
    const estatus = selEstatus.value.toLowerCase();
    const qr      = selQr.value.toLowerCase();
    const rows    = Array.from(tbody.querySelectorAll('tr'));
    let visible   = 0;
    rows.forEach(tr => {
      const match =
        (!q       || tr.dataset.nombre.includes(q)  || tr.dataset.correo.includes(q)) &&
        (!tipo    || tr.dataset.tipo    === tipo)    &&
        (!estatus || tr.dataset.estatus === estatus) &&
        (!qr      || tr.dataset.qr      === qr);
      tr.hidden = !match;
      if (match) visible++;
    });
    countEl.textContent = visible;
    visible === 0 ? showEmpty() : showTable();
  }

  inputNombre.addEventListener('input',  applyFilters);
  selTipo.addEventListener('change',     applyFilters);
  selEstatus.addEventListener('change',  applyFilters);
  selQr.addEventListener('change',       applyFilters);
  document.getElementById('btn-clear-filters').addEventListener('click', () => {
    inputNombre.value = selTipo.value = selEstatus.value = selQr.value = '';
    applyFilters();
  });

  document.querySelectorAll('th.sortable').forEach(th => {
    th.addEventListener('click', () => {
      const col = parseInt(th.dataset.col);
      sortDir = (sortCol === col && sortDir === 'asc') ? 'desc' : 'asc';
      sortCol = col;
      document.querySelectorAll('th.sortable').forEach(t => t.classList.remove('sort-asc','sort-desc'));
      th.classList.add(sortDir === 'asc' ? 'sort-asc' : 'sort-desc');
      const rows = Array.from(tbody.querySelectorAll('tr:not([hidden])'));
      rows.sort((a, b) => {
        const va = (a.querySelectorAll('td')[col]?.innerText || '').trim().toLowerCase();
        const vb = (b.querySelectorAll('td')[col]?.innerText || '').trim().toLowerCase();
        return sortDir === 'asc' ? va.localeCompare(vb, 'es') : vb.localeCompare(va, 'es');
      });
      rows.forEach(r => tbody.appendChild(r));
    });
  });

  document.getElementById('btn-exportar-excel').addEventListener('click', () => {
    window.location.href = `php/exportar_participantes_evento.php?evento_id=${eventoId}`;
  });

  document.getElementById('btn-imprimir').addEventListener('click', () => window.print());

  function cap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }

});
</script>
</body>
</html>
