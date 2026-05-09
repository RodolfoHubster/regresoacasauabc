/* qr-scanner.js – Validación de QR desde distintos dispositivos */

function validarQR() {
  const input = document.getElementById('qr-input');
  const resultContainer = document.getElementById('qr-result');
  const resultContent = document.getElementById('qr-result-content');
  if (!input || !resultContainer) return;

  const codigo = input.value.trim();
  if (!codigo) {
    input.focus();
    input.style.borderColor = 'var(--color-error)';
    return;
  }
  input.style.borderColor = '';

  // TODO: Reemplazar con fetch() a api/validar-qr.php
  // fetch('api/validar-qr.php?code=' + encodeURIComponent(codigo))
  //   .then(r => r.json())
  //   .then(data => mostrarResultadoQR(data))

  // Simulación de respuesta
  const esValido = codigo.startsWith('UABC') || codigo.length > 5;

  resultContainer.hidden = false;
  if (esValido) {
    resultContent.className = 'qr-result-card qr-result-card--ok';
    resultContent.innerHTML = `
      <div class="qr-result-icon" style="color:var(--uabc-verde)">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
          <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
      </div>
      <div class="qr-result-info">
        <p class="qr-result-status">✓ Registro válido</p>
        <p class="qr-result-name">Asistente verificado</p>
        <p class="qr-result-detail">Código: ${codigo}</p>
      </div>
    `;
  } else {
    resultContent.className = 'qr-result-card qr-result-card--error';
    resultContent.innerHTML = `
      <div class="qr-result-icon" style="color:var(--color-error)">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="15" y1="9" x2="9" y2="15"/>
          <line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
      </div>
      <div class="qr-result-info">
        <p class="qr-result-status" style="color:var(--color-error)">× Código no encontrado</p>
        <p class="qr-result-detail">Verifica el código e intenta nuevamente.</p>
      </div>
    `;
  }
}

// Soporte para lector QR USB (funciona como teclado, dispara Enter)
document.addEventListener('DOMContentLoaded', () => {
  const qrInput = document.getElementById('qr-input');
  if (!qrInput) return;

  qrInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      validarQR();
    }
  });
});
