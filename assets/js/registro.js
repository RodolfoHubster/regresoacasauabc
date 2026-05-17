/* registro.js – Validación en tiempo real + envío del formulario */

/* ─── REGLAS DE VALIDACIÓN ──────────────────────────────────────────────── */
// FIX: 'field-tipo' removido de REGLAS — es un input hidden con valor fijo
// ('egresado'), nunca estará vacío y no requiere validación. Su span #error-tipo
// sigue existiendo en el HTML para evitar fallo silencioso en validarCampo().
const REGLAS = {
  'field-nombre': {
    requerido: true,
    minLen: 2,
    maxLen: 80,
    regex: /^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ\s'-]+$/,
    msgs: {
      vacio:  'El nombre es requerido.',
      corto:  'Mínimo 2 caracteres.',
      largo:  'Máximo 80 caracteres.',
      regex:  'Solo se permiten letras y espacios.',
    }
  },
  'field-apellidos': {
    requerido: true,
    minLen: 2,
    maxLen: 100,
    regex: /^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ\s'-]+$/,
    msgs: {
      vacio:  'Los apellidos son requeridos.',
      corto:  'Mínimo 2 caracteres.',
      largo:  'Máximo 100 caracteres.',
      regex:  'Solo se permiten letras y espacios.',
    }
  },
  'field-email': {
    requerido: true,
    regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
    msgs: {
      vacio:  'El correo electrónico es requerido.',
      regex:  'Ingresa un correo válido (ej. nombre@correo.com).',
    }
  },
  'field-telefono': {
    requerido: false,
    minLen: 10,
    maxLen: 15,
    regex: /^[0-9 ()+-]*$/,
    msgs: {
      corto:  'Mínimo 10 dígitos.',
      largo:  'Máximo 15 dígitos.',
      regex:  'Solo se permiten números, espacios y los símbolos + ( ) -',
    }
  },
  'field-campus': {
    requerido: true,
    msgs: { vacio: 'Selecciona tu campus de egreso.' }
  },
  'field-facultad': {
    requerido: true,
    msgs: { vacio: 'Selecciona tu facultad.' }
  },
  'field-carrera': {
    requerido: true,
    msgs: { vacio: 'Selecciona tu carrera.' }
  },
  'field-generacion': {
    requerido: true,
    regex: /^[0-9]{4}(-[0-9]{4})?$/,
    msgs: {
      vacio:  'La generación es requerida.',
      regex:  'Formato válido: 2015 o 2015-2020.',
    }
  },
};

/* ─── VALIDAR UN CAMPO INDIVIDUAL ──────────────────────────────────────── */
function validarCampo(id) {
  const input   = document.getElementById(id);
  const errorEl = document.getElementById('error-' + id.replace('field-', ''));
  const regla   = REGLAS[id];
  if (!input || !errorEl || !regla) return true;

  const valor = input.value.trim();
  let msg = '';

  if (regla.requerido && valor === '') {
    msg = regla.msgs.vacio;
  } else if (valor !== '') {
    if (regla.minLen && valor.replace(/\D/g, '').length < regla.minLen && id === 'field-telefono') {
      msg = regla.msgs.corto;
    } else if (regla.minLen && id !== 'field-telefono' && valor.length < regla.minLen) {
      msg = regla.msgs.corto;
    } else if (regla.maxLen && valor.length > regla.maxLen) {
      msg = regla.msgs.largo;
    } else if (regla.regex && !regla.regex.test(valor)) {
      msg = regla.msgs.regex;
    }
  }

  const esCorrecto = msg === '';

  errorEl.textContent = msg;
  input.setAttribute('aria-invalid', String(!esCorrecto));

  // Clases visuales
  input.classList.toggle('input--error', !esCorrecto);
  input.classList.toggle('input--ok',    esCorrecto && (regla.requerido || valor !== ''));

  return esCorrecto;
}

/* ─── INICIALIZAR EVENTOS DE TIEMPO REAL ───────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-registro');
  if (!form) return;

  // Adjuntar listeners de tiempo real a cada campo con regla
  Object.keys(REGLAS).forEach(id => {
    const input = document.getElementById(id);
    if (!input) return;

    // 'input' para texto/email/tel/generacion; 'change' para selects
    const evento = (input.tagName === 'SELECT') ? 'change' : 'input';

    input.addEventListener(evento, () => validarCampo(id));

    // También validar al perder el foco (blur) para capturar campos que
    // el usuario salta sin escribir nada
    input.addEventListener('blur', () => validarCampo(id));
  });

  // ─── SELECTORES DINÁMICOS: Campus → Facultad → Carrera ───
  const campusSelect   = document.getElementById('field-campus');
  const facultadSelect = document.getElementById('field-facultad');
  const carreraSelect  = document.getElementById('field-carrera');

  if (campusSelect && facultadSelect && carreraSelect) {

    campusSelect.addEventListener('change', function () {
      const campusId = this.value;

      facultadSelect.innerHTML = '<option value="">Selecciona tu facultad</option>';
      carreraSelect.innerHTML  = '<option value="">Primero selecciona una facultad</option>';
      carreraSelect.disabled   = true;

      if (campusId) {
        facultadSelect.disabled = false;
        fetch(`php/get_facultades.php?campus_id=${campusId}`)
          .then(r => r.json())
          .then(result => {
            if (result.status === 'success') {
              result.data.forEach(f => {
                const opt = document.createElement('option');
                opt.value       = f.id;
                opt.textContent = `${f.codigo || ''} - ${f.nombre}`;
                facultadSelect.appendChild(opt);
              });
            }
          })
          .catch(err => console.error('Error cargando facultades:', err));
      } else {
        facultadSelect.disabled = true;
      }
    });

    facultadSelect.addEventListener('change', function () {
      const facultadId = this.value;
      carreraSelect.innerHTML = '<option value="">Selecciona tu carrera</option>';

      if (facultadId) {
        carreraSelect.disabled = false;
        fetch(`php/get_carreras.php?facultad_id=${facultadId}`)
          .then(r => r.json())
          .then(result => {
            if (result.status === 'success') {
              result.data.forEach(c => {
                const opt = document.createElement('option');
                opt.value       = c.id;
                opt.textContent = c.nombre;
                carreraSelect.appendChild(opt);
              });
              carreraSelect.insertAdjacentHTML('beforeend', '<option value="otra" style="font-weight: bold; color: var(--uabc-verde);">Otra (Escribir manual)</option>');
            }
          })
          .catch(err => console.error('Error cargando carreras:', err));
      } else {
        carreraSelect.disabled = true;
      }
    });
  }

  // ─── SUBMIT ───
  form.addEventListener('submit', e => {
    e.preventDefault();
    // Validar todos los campos al enviar (por si el usuario nunca tocó alguno)
    const todosOk = Object.keys(REGLAS).map(id => validarCampo(id)).every(Boolean);
    if (todosOk) enviarRegistro();
    else {
      // Hacer scroll al primer campo con error
      const primerError = form.querySelector('.input--error');
      if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
});

/* ─── ENVIAR REGISTRO ───────────────────────────────────────────────────── */
function enviarRegistro() {
  const form        = document.getElementById('form-registro');
  const datos       = new FormData(form);
  const btnSubmit   = form.querySelector('button[type="submit"]');
  const textoOrig   = btnSubmit.textContent;

  btnSubmit.disabled    = true;
  btnSubmit.textContent = 'Guardando registro…';

  fetch('php/procesar_registro.php', { method: 'POST', body: datos })
    .then(r => r.json())
    .then(res => {
      btnSubmit.disabled    = false;
      btnSubmit.textContent = textoOrig;

      if (res.status === 'success') {
        closeModal('modal-registro');
        resetFormulario(form);
        setTimeout(() => openModal('modal-confirmacion'), 200);
      } else {
        alert('Ocurrió un error al guardar: ' + res.message);
      }
    })
    .catch(() => {
      btnSubmit.disabled    = false;
      btnSubmit.textContent = textoOrig;
      alert('Error de comunicación con el servidor.');
    });
}

/* ─── RESET COMPLETO ────────────────────────────────────────────────────── */
function resetFormulario(form) {
  form.reset();

  // Limpiar clases visuales y mensajes de error
  form.querySelectorAll('.form-input, .form-select').forEach(el => {
    el.classList.remove('input--error', 'input--ok');
    el.removeAttribute('aria-invalid');
  });
  form.querySelectorAll('.form-error').forEach(el => el.textContent = '');

  // Resetear selects dependientes
  const fac = document.getElementById('field-facultad');
  const car = document.getElementById('field-carrera');
  if (fac) { fac.innerHTML = '<option value="">Primero selecciona un campus</option>'; fac.disabled = true; }
  if (car) { car.innerHTML = '<option value="">Primero selecciona una facultad</option>'; car.disabled = true; }

  // Ocultar input de carrera manual al resetear
  const otraInput = document.getElementById('field-carrera-otra');
  if (otraInput) {
    otraInput.classList.remove('is-visible');
    otraInput.required = false;
    otraInput.value = '';
  }
}

// ─── Mostrar/Ocultar input de "Otra Carrera" ───
const selectCarrera = document.getElementById('field-carrera');
if (selectCarrera) {
  selectCarrera.addEventListener('change', function () {
    const inputOtra = document.getElementById('field-carrera-otra');
    if (inputOtra) {
      if (this.value === 'otra') {
        inputOtra.classList.add('is-visible');
        inputOtra.required = true;
      } else {
        inputOtra.classList.remove('is-visible');
        inputOtra.required = false;
        inputOtra.value = '';
      }
    }
  });
}
