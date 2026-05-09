/* registro.js – Validación y envío del formulario de registro */

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-registro');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (validarFormulario()) {
      enviarRegistro();
    }
  });
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
  // TODO: Reemplazar con fetch() a api/registro.php
  // const datos = new FormData(document.getElementById('form-registro'));
  // fetch('api/registro.php', { method: 'POST', body: datos })
  //   .then(r => r.json())
  //   .then(res => { if(res.ok) mostrarConfirmacion(); })

  // Simulación (eliminar cuando esté el backend)
  closeModal('modal-registro');
  document.getElementById('form-registro').reset();
  setTimeout(() => openModal('modal-confirmacion'), 200);
}
