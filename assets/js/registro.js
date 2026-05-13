/* registro.js – Validación y envío del formulario de registro */

/**
 * Valida que el número de teléfono sea válido o esté vacío (es opcional).
 * Acepta: vacío, dígitos, espacios, guiones, paréntesis, prefijo +52.
 * Rechaza: letras, caracteres especiales, menos de 10 dígitos si se llena.
 * @param {string} valor
 * @returns {boolean}
 */
function validarTelefono(valor) {
  const limpio = typeof valor === 'string' ? valor.trim() : '';
  if (limpio === '') return true;
  if (/[a-zA-Z]/.test(limpio)) return false;
  if (/[^0-9+\-\s()]/.test(limpio)) return false;
  const soloDigitos = limpio.replace(/[^0-9]/g, '');
  if (soloDigitos.length < 10) return false;
  return true;
}

/**
 * Muestra u oculta el error de un campo y aplica clase visual.
 * @param {HTMLElement} input
 * @param {HTMLElement} errorEl
 * @param {string|null} msg  – null = sin error
 */
function setFieldState(input, errorEl, msg) {
  if (!input || !errorEl) return;
  if (msg) {
    errorEl.textContent = msg;
    input.setAttribute('aria-invalid', 'true');
    input.classList.add('is-error');
    input.classList.remove('is-ok');
  } else {
    errorEl.textContent = '';
    input.removeAttribute('aria-invalid');
    input.classList.remove('is-error');
    // Solo poner verde si el campo tiene valor (no en campos vacíos opcionales)
    if (input.value.trim() !== '') {
      input.classList.add('is-ok');
    } else {
      input.classList.remove('is-ok');
    }
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-registro');
  if (!form) return;

  // ── Validación en tiempo real: Nombre ──
  const nombreInput = document.getElementById('field-nombre');
  const nombreError = document.getElementById('error-nombre');
  if (nombreInput) {
    nombreInput.addEventListener('input', () => {
      const v = nombreInput.value.trim();
      if (!v) setFieldState(nombreInput, nombreError, null);
      else if (v.length < 2) setFieldState(nombreInput, nombreError, 'El nombre debe tener al menos 2 caracteres.');
      else if (!/^[A-Za-záéíóúÁÉÍÓÚüÜñÑ\s'-]+$/.test(v)) setFieldState(nombreInput, nombreError, 'Solo se permiten letras y acentos.');
      else setFieldState(nombreInput, nombreError, null);
    });
    nombreInput.addEventListener('blur', () => {
      if (!nombreInput.value.trim()) setFieldState(nombreInput, nombreError, 'El nombre es requerido.');
    });
  }

  // ── Validación en tiempo real: Apellidos ──
  const apellidosInput = document.getElementById('field-apellidos');
  const apellidosError = document.getElementById('error-apellidos');
  if (apellidosInput) {
    apellidosInput.addEventListener('input', () => {
      const v = apellidosInput.value.trim();
      if (!v) setFieldState(apellidosInput, apellidosError, null);
      else if (v.length < 2) setFieldState(apellidosInput, apellidosError, 'Los apellidos deben tener al menos 2 caracteres.');
      else if (!/^[A-Za-záéíóúÁÉÍÓÚüÜñÑ\s'-]+$/.test(v)) setFieldState(apellidosInput, apellidosError, 'Solo se permiten letras y acentos.');
      else setFieldState(apellidosInput, apellidosError, null);
    });
    apellidosInput.addEventListener('blur', () => {
      if (!apellidosInput.value.trim()) setFieldState(apellidosInput, apellidosError, 'Los apellidos son requeridos.');
    });
  }

  // ── Validación en tiempo real: Correo ──
  const emailInput = document.getElementById('field-email');
  const emailError = document.getElementById('error-email');
  if (emailInput) {
    emailInput.addEventListener('input', () => {
      const v = emailInput.value.trim();
      if (!v) setFieldState(emailInput, emailError, null);
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) setFieldState(emailInput, emailError, 'Ingresa un correo válido, ej: nombre@dominio.com');
      else setFieldState(emailInput, emailError, null);
    });
    emailInput.addEventListener('blur', () => {
      if (!emailInput.value.trim()) setFieldState(emailInput, emailError, 'El correo electrónico es requerido.');
    });
  }

  // ── Validación en tiempo real: Teléfono (OPCIONAL) ──
  const telefonoInput = document.getElementById('field-telefono');
  const telefonoError = document.getElementById('error-telefono');
  if (telefonoInput) {
    telefonoInput.addEventListener('input', () => {
      const v = telefonoInput.value;
      if (!v.trim()) {
        // Campo vacío = válido (es opcional), limpiar estados
        setFieldState(telefonoInput, telefonoError, null);
        telefonoInput.classList.remove('is-ok'); // vacío no pone verde
        return;
      }
      if (/[a-zA-Z]/.test(v)) {
        setFieldState(telefonoInput, telefonoError, 'El teléfono no puede contener letras.');
        return;
      }
      if (/[^0-9+\-\s()]/.test(v)) {
        setFieldState(telefonoInput, telefonoError, 'Solo se permiten números, espacios, guiones y paréntesis.');
        return;
      }
      const soloDigitos = v.replace(/[^0-9]/g, '');
      if (soloDigitos.length > 0 && soloDigitos.length < 10) {
        setFieldState(telefonoInput, telefonoError, `Faltan ${10 - soloDigitos.length} dígito(s). Mínimo 10 requeridos.`);
        return;
      }
      // 10+ dígitos y sin caracteres inválidos = válido
      setFieldState(telefonoInput, telefonoError, null);
    });
  }

  // ── Validación en tiempo real: Generación ──
  const generacionInput = document.getElementById('field-generacion');
  const generacionError = document.getElementById('error-generacion');
  if (generacionInput) {
    generacionInput.addEventListener('input', () => {
      const v = generacionInput.value.trim();
      if (!v) {
        setFieldState(generacionInput, generacionError, null);
        return;
      }
      if (/^\d{1,3}$/.test(v)) {
        setFieldState(generacionInput, generacionError, `Año incompleto, ej: 2018`);
        return;
      }
      if (!/^[0-9]{4}(-[0-9]{4})?$/.test(v)) {
        setFieldState(generacionInput, generacionError, 'Formato inválido. Usa: 2018 o 2016-2020');
        return;
      }
      const anio = parseInt(v.substring(0, 4));
      if (anio < 1960 || anio > 2026) {
        setFieldState(generacionInput, generacionError, 'El año debe estar entre 1960 y 2026.');
        return;
      }
      setFieldState(generacionInput, generacionError, null);
    });
    generacionInput.addEventListener('blur', () => {
      if (!generacionInput.value.trim()) setFieldState(generacionInput, generacionError, 'La generación es requerida.');
    });
  }

  // ── Validación en tiempo real: Selects ──
  ['field-campus', 'field-facultad', 'field-carrera', 'field-tipo'].forEach(id => {
    const el = document.getElementById(id);
    const errId = 'error-' + id.replace('field-', '');
    const errEl = document.getElementById(errId);
    if (el && errEl) {
      el.addEventListener('change', () => {
        if (el.value) setFieldState(el, errEl, null);
      });
    }
  });

  // ── Submit ──
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (validarFormulario()) {
      enviarRegistro();
    }
  });

  // ── Selectores Dinámicos (Campus -> Facultad -> Carrera) ──
  const campusSelect = document.getElementById('field-campus');
  const facultadSelect = document.getElementById('field-facultad');
  const carreraSelect = document.getElementById('field-carrera');

  if (campusSelect && facultadSelect && carreraSelect) {
    campusSelect.addEventListener('change', function() {
      const campusId = this.value;
      facultadSelect.innerHTML = '<option value="">Selecciona tu facultad</option>';
      carreraSelect.innerHTML = '<option value="">Primero selecciona una facultad</option>';
      carreraSelect.disabled = true;
      if (campusId) {
        facultadSelect.disabled = false;
        fetch(`php/get_facultades.php?campus_id=${campusId}`)
          .then(r => r.json())
          .then(result => {
            if (result.status === 'success') {
              result.data.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.id;
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

    facultadSelect.addEventListener('change', function() {
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
                opt.value = c.id;
                opt.textContent = c.nombre;
                carreraSelect.appendChild(opt);
              });
            }
          })
          .catch(err => console.error('Error cargando carreras:', err));
      } else {
        carreraSelect.disabled = true;
      }
    });
  }
});

function validarFormulario() {
  const eventoId = document.getElementById('field-evento-id');
  if (!eventoId || !eventoId.value) {
    alert('Error: No se detectó el evento. Por favor cierra este formulario y vuelve a hacer clic en "Registrarme".');
    return false;
  }

  let valido = true;
  let primerError = null;

  const requeridos = [
    { id: 'field-nombre',     errId: 'error-nombre',     msg: 'El nombre es requerido.' },
    { id: 'field-apellidos',  errId: 'error-apellidos',  msg: 'Los apellidos son requeridos.' },
    { id: 'field-email',      errId: 'error-email',      msg: 'El correo electrónico es requerido.' },
    { id: 'field-campus',     errId: 'error-campus',     msg: 'Selecciona tu campus de egreso.' },
    { id: 'field-facultad',   errId: 'error-facultad',   msg: 'La facultad es requerida.' },
    { id: 'field-carrera',    errId: 'error-carrera',    msg: 'La carrera es requerida.' },
    { id: 'field-generacion', errId: 'error-generacion', msg: 'La generación es requerida.' },
    { id: 'field-tipo',       errId: 'error-tipo',       msg: 'Selecciona el tipo de asistente.' },
  ];

  requeridos.forEach(c => {
    const input = document.getElementById(c.id);
    const errEl = document.getElementById(c.errId);
    if (!input || !errEl) return;
    if (!input.value.trim()) {
      setFieldState(input, errEl, c.msg);
      valido = false;
      if (!primerError) primerError = input;
    }
  });

  // Validar formato correo si tiene valor
  const emailInput = document.getElementById('field-email');
  const emailError = document.getElementById('error-email');
  if (emailInput && emailInput.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
    setFieldState(emailInput, emailError, 'Ingresa un correo electrónico válido.');
    valido = false;
    if (!primerError) primerError = emailInput;
  }

  // Validar teléfono
  const telefonoInput = document.getElementById('field-telefono');
  const telefonoError = document.getElementById('error-telefono');
  if (telefonoInput && telefonoError && !validarTelefono(telefonoInput.value)) {
    const digits = telefonoInput.value.replace(/[^0-9]/g, '').length;
    const msg = digits > 0 && digits < 10
      ? `Faltan ${10 - digits} dígito(s). Mínimo 10 requeridos.`
      : 'Ingresa solo números. Formato válido: 664 000 0000 o +52 664 000 0000.';
    setFieldState(telefonoInput, telefonoError, msg);
    valido = false;
    if (!primerError) primerError = telefonoInput;
  }

  // Scroll al primer error
  if (primerError) {
    primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    primerError.focus();
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

  fetch('php/procesar_registro.php', { method: 'POST', body: datos })
    .then(r => r.json())
    .then(res => {
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;
      if (res.status === 'success') {
        closeModal('modal-registro');
        form.reset();
        form.querySelectorAll('.form-input, .form-select').forEach(el => {
          el.classList.remove('is-error', 'is-ok');
          el.removeAttribute('aria-invalid');
        });
        document.querySelectorAll('.form-error').forEach(el => el.textContent = '');
        const facultadSelect = document.getElementById('field-facultad');
        const carreraSelect  = document.getElementById('field-carrera');
        if (facultadSelect) { facultadSelect.innerHTML = '<option value="">Primero selecciona un campus</option>'; facultadSelect.disabled = true; }
        if (carreraSelect)  { carreraSelect.innerHTML  = '<option value="">Primero selecciona una facultad</option>'; carreraSelect.disabled = true; }
        setTimeout(() => openModal('modal-confirmacion'), 200);
      } else {
        alert('Ocurrió un error al guardar: ' + res.message);
      }
    })
    .catch(err => {
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;
      console.error('[registro.js] Error de red:', err);
      alert('Error de comunicación con el servidor.');
    });
}

// Exportar para tests unitarios
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { validarTelefono };
}
