// Funciones CRUD para Participantes en Admin

function abrirModalParticipante(id = null) {
  const modal = document.getElementById('modal-participante');
  const form = document.getElementById('form-participante');
  const titulo = document.getElementById('titulo-modal-participante');
  const submitBtn = document.getElementById('btn-submit-participante');

  form.reset();
  document.getElementById('part-id').value = '';
  
  // Cargar catalogos
  cargarCatalogosParticipante().then(() => {
    if (id) {
      titulo.textContent = 'Editar Participante';
      submitBtn.textContent = 'Actualizar Participante';
      
      fetch(`php/obtener_participante.php?id=${id}`)
        .then(r => r.json())
        .then(res => {
          if (res.status === 'success') {
            const data = res.data;
            document.getElementById('part-id').value = data.id;
            document.getElementById('part-nombre').value = data.nombre;
            document.getElementById('part-apellidos').value = data.apellidos;
            document.getElementById('part-correo').value = data.correo;
            document.getElementById('part-tipo').value = data.tipo_asistente || 'egresado';
            document.getElementById('part-evento').value = data.evento_id;
            document.getElementById('part-asistencia').value = data.asistencia || 0;
            document.getElementById('part-generacion').value = data.generacion || '';
            
            if (data.campus_id) {
              document.getElementById('part-campus').value = data.campus_id;
              cargarFacultadesParticipante(data.campus_id).then(() => {
                if (data.facultad_id) {
                  document.getElementById('part-facultad').value = data.facultad_id;
                  cargarCarrerasParticipante(data.facultad_id).then(() => {
                    if (data.carrera_id) document.getElementById('part-carrera').value = data.carrera_id;
                  });
                }
              });
            }
          } else {
            showToast(res.message, 'error');
          }
        });
    } else {
      titulo.textContent = 'Nuevo Participante';
      submitBtn.textContent = 'Guardar Participante';
    }
    
    // Set auto event if in participantes.php (has const EVENTO_ID)
    if (!id && typeof EVENTO_ID !== 'undefined') {
      document.getElementById('part-evento').value = EVENTO_ID;
    }

    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
  });
}

function cargarCatalogosParticipante() {
  return Promise.all([
    fetch('php/get_eventos.php').then(r => r.json()),
    fetch('php/get_campus.php').then(r => r.json())
  ]).then(([resEventos, resCampus]) => {
    const selectEvento = document.getElementById('part-evento');
    const selectCampus = document.getElementById('part-campus');
    
    if (resEventos.status === 'success') {
      selectEvento.innerHTML = '<option value="">Selecciona un evento</option>';
      resEventos.data.forEach(e => {
        selectEvento.innerHTML += `<option value="${e.id}">${e.nombre}</option>`;
      });
    }
    if (resCampus.status === 'success') {
      selectCampus.innerHTML = '<option value="">Selecciona campus</option>';
      resCampus.data.forEach(c => {
        selectCampus.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
      });
    }
  });
}

document.getElementById('part-campus')?.addEventListener('change', function() {
  cargarFacultadesParticipante(this.value);
});

function cargarFacultadesParticipante(campusId) {
  const selectFacultad = document.getElementById('part-facultad');
  const selectCarrera = document.getElementById('part-carrera');
  selectFacultad.innerHTML = '<option value="">Selecciona facultad</option>';
  selectCarrera.innerHTML = '<option value="">Selecciona carrera</option>';
  
  if (!campusId) return Promise.resolve();
  
  return fetch(`../php/get_facultades.php?campus_id=${campusId}`)
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        res.data.forEach(f => {
          selectFacultad.innerHTML += `<option value="${f.id}">${f.codigo || ''} - ${f.nombre}</option>`;
        });
      }
    });
}

document.getElementById('part-facultad')?.addEventListener('change', function() {
  cargarCarrerasParticipante(this.value);
});

function cargarCarrerasParticipante(facultadId) {
  const selectCarrera = document.getElementById('part-carrera');
  selectCarrera.innerHTML = '<option value="">Selecciona carrera</option>';
  
  if (!facultadId) return Promise.resolve();
  
  return fetch(`../php/get_carreras.php?facultad_id=${facultadId}`)
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        res.data.forEach(c => {
          selectCarrera.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
        });
      }
    });
}

document.getElementById('form-participante')?.addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const btn = document.getElementById('btn-submit-participante');
  const originalText = btn.textContent;
  
  btn.disabled = true;
  btn.textContent = 'Guardando...';
  
  fetch('php/guardar_participante.php', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(res => {
    btn.disabled = false;
    btn.textContent = originalText;
    
    if (res.status === 'success') {
      showToast(res.message, 'success');
      closeModal('modal-participante');
      
      // Refresh table depending on which page we are
      if (typeof cargarAsistentes === 'function') {
        cargarAsistentes(); // from admin.js
      } else if (typeof cargarParticipantes === 'function') {
        cargarParticipantes(); // from participantes.php
      }
    } else {
      showToast(res.message, 'error');
    }
  })
  .catch(err => {
    btn.disabled = false;
    btn.textContent = originalText;
    showToast('Error de conexión', 'error');
  });
});

function eliminarParticipante(id) {
  if (confirm("¿Estás seguro de eliminar este participante? Esta acción no se puede deshacer.")) {
    fetch('php/eliminar_participante.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(res => {
      if (res.status === 'success') {
        showToast(res.message, 'success');
        if (typeof cargarAsistentes === 'function') {
          cargarAsistentes(); 
        } else if (typeof cargarParticipantes === 'function') {
          cargarParticipantes();
        }
      } else {
        showToast(res.message, 'error');
      }
    });
  }
}
