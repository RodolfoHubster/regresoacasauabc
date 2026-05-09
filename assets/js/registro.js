/* registro.js – Validación y envío del formulario de registro */

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-registro');
  if (!form) return;

  // Lógica original para validar y enviar
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (validarFormulario()) {
      enviarRegistro();
    }
  });

  // --- NUEVA LÓGICA: Selectores Dinámicos (Campus -> Facultad -> Carrera) ---
  const campusSelect = document.getElementById('field-campus');
  const facultadSelect = document.getElementById('field-facultad');
  const carreraSelect = document.getElementById('field-carrera');

  // Escuchar cambio en Campus
  if (campusSelect && facultadSelect && carreraSelect) {
    campusSelect.addEventListener('change', function() {
        const campusId = this.value;
        
        // Reiniciar Selects dependientes
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
  // --- FIN LÓGICA SELECTORES DINÁMICOS ---
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

  // Validar formato email
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

  // Cambiar el texto del botón y deshabilitarlo para evitar que hagan doble clic
  const textoOriginal = btnSubmit.textContent;
  btnSubmit.disabled = true;
  btnSubmit.textContent = 'Guardando registro...';

  // Hacemos la petición al backend
  fetch('php/procesar_registro.php', { 
      method: 'POST', 
      body: datos 
  })
  .then(response => response.json())
  .then(res => { 
      // Restauramos el botón
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;

      if(res.status === 'success') {
          // Si guardó bien en la BD, cerramos el formulario
          closeModal('modal-registro');
          form.reset();
          
          // Limpiamos los selects dinámicos para que queden como al inicio
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

          // Mostramos la ventana de "Registro Exitoso"
          setTimeout(() => openModal('modal-confirmacion'), 200);
      } else {
          // Si PHP nos mandó un error, lo mostramos en consola y en un alert
          console.error("Error PHP:", res.message);
          alert("Ocurrió un error al guardar: " + res.message);
      }
  })
  .catch(error => {
      // Error de red o servidor caído
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;
      console.error("Error en la petición:", error);
      alert("Error de comunicación con el servidor. Intenta de nuevo.");
  });
}