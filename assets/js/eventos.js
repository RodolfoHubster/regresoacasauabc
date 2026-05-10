/* eventos.js – Funciones de modal para eventos y carga dinámica */

// Variable global para almacenar los eventos públicos
let publicEventos = [];

// ─── CARGA DINÁMICA DE EVENTOS ───
function loadPublicEventos() {
  const grid = document.getElementById('events-grid');
  if (!grid) return;

  // CORRECCIÓN DE RUTA: Detectamos la carpeta del proyecto dinámicamente
  const path = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
  const apiRuta = path + 'admin/php/get_eventos.php';

  console.log("Cargando eventos desde:", apiRuta);

  fetch(apiRuta)
    .then(res => {
      if (!res.ok) throw new Error('Error al conectar con get_eventos.php en: ' + apiRuta);
      return res.json();
    })
    .then(result => {
      if (result.status === 'success' && result.data.length > 0) {
        publicEventos = result.data;
        grid.innerHTML = ''; 

        result.data.forEach(evento => {
          const isProximo = evento.estado === 'proximo';
          const isCerrado = evento.estado === 'cerrado';
          
          let badgeClass = '';
          let badgeText = 'Disponible';
          if (isProximo) { badgeClass = 'event-badge--pronto'; badgeText = 'Próximamente'; }
          if (isCerrado) { badgeClass = 'event-badge--cerrado'; badgeText = 'Cerrado'; }

          const btnText = isProximo ? 'Próximamente' : (isCerrado ? 'Cerrado' : 'Registrarme');
          const btnDisabled = (isProximo || isCerrado) ? 'disabled' : '';
          const btnClass = (isProximo || isCerrado) ? 'btn-secondary' : 'btn-primary';

          const imgUrl = evento.imagen || 'assets/images/download.jpg';

          // IMPORTANTE: Clase "btn-registrar" y "data-id" con el ID numérico puro
          const card = `
            <article class="event-card">
              <div class="event-card-img" style="background-image: url('${imgUrl}');" role="img">
                <span class="event-badge ${badgeClass}">${badgeText}</span>
              </div>
              <div class="event-card-body">
                <h3 class="event-card-title">${evento.nombre}</h3>
                <ul role="list" class="event-meta">
                  <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>${evento.fecha} · ${evento.hora}</span>
                  </li>
                  <li>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>${evento.ubicacion}</span>
                  </li>
                </ul>
                <p class="event-card-desc">${evento.descripcion || 'Sin descripción'}</p>
                <div class="event-card-actions">
                  <button class="btn btn-outline-primary" ${btnDisabled}>Información</button>
                  <button class="btn ${btnClass} btn-registrar" ${btnDisabled} data-id="${evento.id}" onclick="openModalRegistro(${evento.id})">${btnText}</button>
                </div>
              </div>
            </article>
          `;
          grid.innerHTML += card;
        });

        // Intentamos detectar el QR una vez que los eventos están en el DOM
        checkQRUrl();

      } else {
        grid.innerHTML = '';
        const emptyState = document.getElementById('events-empty');
        if (emptyState) emptyState.hidden = false;
      }
    })
    .catch(err => {
      console.error("Error crítico:", err);
      grid.innerHTML = `<p style="color:red; text-align:center;">${err.message}</p>`;
    });
}

function openModalRegistro(id) {
  const evento = publicEventos.find(e => e.id == id);
  if (!evento) return;

  const modal = document.getElementById('modal-registro');
  if (!modal) return;

  const banner = document.getElementById('modal-event-banner');
  const label  = document.getElementById('modal-event-label');
  if (banner && evento.imagen) banner.style.backgroundImage = `url('${evento.imagen}')`;
  if (label) label.textContent = '📅 ' + evento.nombre;

  const hiddenName = document.getElementById('field-evento');
  const hiddenId = document.getElementById('field-evento-id');
  if (hiddenName) hiddenName.value = evento.nombre;
  if (hiddenId) hiddenId.value = evento.id;

  modal.removeAttribute('hidden');
  document.body.style.overflow = 'hidden';
}

function checkQRUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const eventoParam = urlParams.get('evento'); // Esto leerá el "5"
    
    if (eventoParam) {
        // Intentamos sacar el número, ya sea que venga como "5" o como "EVT-2026-5"
        const idMatch = eventoParam.match(/\d+$/);
        const id = idMatch ? idMatch[0] : null;

        if (id) {
            let intentos = 0;
            const interval = setInterval(() => {
                const btn = document.querySelector(`.btn-registrar[data-id="${id}"]`);
                if (btn) {
                    btn.click();
                    clearInterval(interval);
                } else if (intentos > 15) { // 3 segundos de espera
                    openModalRegistro(id);
                    clearInterval(interval);
                }
                intentos++;
            }, 200);
        }
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

// ─── LÓGICA DE MODALES (Asegurando compatibilidad con index.html) ───
function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (!modal) return;
  modal.setAttribute('hidden', '');
  document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', () => {
  loadPublicEventos();

  // Cerrar modales con clic en el fondo
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeModal(overlay.id);
    });
  });

  // Cerrar con tecla Escape
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay:not([hidden])').forEach(m => closeModal(m.id));
    }
  });
});