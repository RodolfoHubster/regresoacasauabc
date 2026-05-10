/* registro.js – Validación y envío del formulario de registro */

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-registro');
  if (!form) return;

  // Lógica para validar y enviar
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (validarFormulario()) {
      enviarRegistro();
    }
  });

  // --- LÓGICA: Selectores Dinámicos (Campus -> Facultad -> Carrera) ---
  const campusSelect = document.getElementById('field-campus');
  const facultadSelect = document.getElementById('field-facultad');
  const carreraSelect = document.getElementById('field-carrera');

  if (campusSelect && facultadSelect && carreraSelect) {
    // Escuchar cambio en Campus
    campusSelect.addEventListener('change', function() {
        const campusId = this.value;
        
        facultadSelect.innerHTML = '<option value="">Selecciona tu facultad</option>';
        carreraSelect.innerHTML = '<option value="">Primero selecciona una facultad</option>';
        carreraSelect.disabled = true;

        if (campusId) {
            facultadSelect.disabled = false;
            fetch(`php/get_facultades.php?campus_id=${campusId}`)
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        result.data.forEach(facultad => {
                            const option = document.createElement('option');
                            option.value = facultad.id;
                            option.textContent = `${facultad.codigo || ''} - ${facultad.nombre}`;
                            facultadSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error cargando facultades:', error));
        } else {
            facultadSelect.disabled = true;
        }
    });

    // Escuchar cambio en Facultad
    facultadSelect.addEventListener('change', function() {
        const facultadId = this.value;
        carreraSelect.innerHTML = '<option value="">Selecciona tu carrera</option>';

        if (facultadId) {
            carreraSelect.disabled = false;
            fetch(`php/get_carreras.php?facultad_id=${facultadId}`)
                .then(response => response.json())
                .then(result => {
                    if (result.status === 'success') {
                        result.data.forEach(carrera => {
                            const option = document.createElement('option');
                            option.value = carrera.id;
                            option.textContent = carrera.nombre;
                            carreraSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error cargando carreras:', error));
        } else {
            carreraSelect.disabled = true;
        }
    });
  }
});

function validarFormulario() {
  let valido = true;
  const campos = [
    { id: 'field-nombre',     errorId: 'error-nombre',     msg: 'El nombre es requerido.' },
    { id: 'field-apellidos',  errorId: 'error-apellidos',  msg: 'Los apellidos son requeridos.' },
    { id: 'field-email',      errorId: 'error-email',      msg: 'El correo electrónico es requerido.' },
    { id: 'field-campus',     errorId: 'error-campus',     msg: 'Selecciona tu campus de egreso.' },
    { id: 'field-facultad',   errorId: 'error-facultad',   msg: 'La facultad es requerida.' },
    { id: 'field-carrera',    errorId: 'error-carrera',    msg: 'La carrera es requerida.' },
    { id: 'field-generacion', errorId: 'error-generacion', msg: 'La generación es requerida.' },
    { id: 'field-tipo',       errorId: 'error-tipo',       msg: 'Selecciona el tipo de asistente.' },
  ];

  campos.forEach(campo => {
    const input = document.getElementById(campo.id);
    const errorEl = document.getElementById(campo.errorId);
    if (!input || !errorEl) return;

    const valor = input.value.trim();
    if (!valor) {
      errorEl.textContent = campo.msg;
      input.setAttribute('aria-invalid', 'true');
      input.style.borderColor = 'var(--color-error)';
      valido = false;
    } else {
      errorEl.textContent = '';
      input.removeAttribute('aria-invalid');
      input.style.borderColor = '';
    }
  });

  const emailInput = document.getElementById('field-email');
  const emailError = document.getElementById('error-email');
  if (emailInput && emailInput.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
    if (emailError) emailError.textContent = 'Ingresa un correo electrónico válido.';
    emailInput.setAttribute('aria-invalid', 'true');
    emailInput.style.borderColor = 'var(--color-error)';
    valido = false;
  }
  return valido;
}

function enviarRegistro() {
  const form = document.getElementById('form-registro');
  const datos = new FormData(form);
  const btnSubmit = form.querySelector('button[type="submit"]');
  const textoOriginal = btnSubmit.textContent;

  btnSubmit.disabled = true;
  btnSubmit.textContent = 'Guardando registro...';

  fetch('php/procesar_registro.php', { 
      method: 'POST', 
      body: datos 
  })
  .then(response => response.json())
  .then(res => { 
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;

      if(res.status === 'success') {
          closeModal('modal-registro');
          form.reset();
          
          // Reset de estilos y selects
          const inputs = form.querySelectorAll('.form-input, .form-select');
          inputs.forEach(input => {
              input.style.borderColor = '';
              input.removeAttribute('aria-invalid');
          });
          document.querySelectorAll('.form-error').forEach(msg => msg.textContent = '');

          const facultadSelect = document.getElementById('field-facultad');
          const carreraSelect = document.getElementById('field-carrera');
          if(facultadSelect) {
              facultadSelect.innerHTML = '<option value="">Primero selecciona un campus</option>';
              facultadSelect.disabled = true;
          }
          if(carreraSelect) {
              carreraSelect.innerHTML = '<option value="">Primero selecciona una facultad</option>';
              carreraSelect.disabled = true;
          }

          setTimeout(() => openModal('modal-confirmacion'), 200);
      } else {
          alert("Ocurrió un error al guardar: " + res.message);
      }
  })
  .catch(error => {
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;
      alert("Error de comunicación con el servidor.");
  });
}