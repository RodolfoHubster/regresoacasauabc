let html5QrcodeScanner = null;
let escanerRenderizado = false;

// Esta función SOLO se ejecutará cuando entremos a la pestaña "Validar QR"
window.inicializarLectorQR = function() {
    if (escanerRenderizado) return; // Si ya se dibujó la cámara en la pantalla, no lo vuelve a hacer

    html5QrcodeScanner = new Html5QrcodeScanner(
      "lector-camara", 
      { fps: 10, qrbox: {width: 250, height: 250} }, 
      false
    );
  
    function onScanSuccess(decodedText, decodedResult) {
      html5QrcodeScanner.pause();
      tocarBeep();
      
      // LIMPIEZA: Solo permite letras, números y el guion (-)
      // También limpia comillas, espacios, saltos de línea y caracteres de control
      let codigoLimpio = limpiarCodigo(decodedText);

      // Ponemos el código ya limpio en la cajita visual
      document.getElementById('qr-input').value = codigoLimpio;
      
      // Llamamos a la validación con el código limpio
      if (typeof validarQR === 'function') {
          validarQR(codigoLimpio);
      }

      setTimeout(() => {
          if (html5QrcodeScanner) html5QrcodeScanner.resume(); 
      }, 3000);
    }
  
    html5QrcodeScanner.render(onScanSuccess);
    escanerRenderizado = true;
};

/**
 * Limpia un código QR de cualquier carácter no deseado.
 * Elimina: comillas simples/dobles, espacios, saltos de línea,
 * retornos de carro, caracteres de control (0x00-0x1F, 0x7F) y
 * cualquier símbolo que no sea letra, número o guion.
 * También normaliza a mayúsculas y reinserta el guion de UABC si el
 * scanner lo eliminó (UABC6A... → UABC-6A...).
 */
function limpiarCodigo(str) {
    if (!str) return '';
    let limpio = str
        .replace(/[\x00-\x1F\x7F]/g, '') // Caracteres de control e invisibles
        .replace(/['"]/g, '')             // Comillas simples y dobles
        .replace(/[^a-zA-Z0-9-]/g, '')   // Todo lo que no sea alfanumérico o guion
        .trim()
        .toUpperCase();                   // Normalizar a mayúsculas

    // Algunos scanners eliminan el guion: UABC6A... → restaurar a UABC-6A...
    if (/^UABC[A-Z0-9]/.test(limpio) && limpio.charAt(4) !== '-') {
        limpio = 'UABC-' + limpio.slice(4);
    }

    return limpio;
}

function tocarBeep() {
    const context = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = context.createOscillator();
    oscillator.type = 'sine';
    oscillator.frequency.value = 800;
    oscillator.connect(context.destination);
    oscillator.start();
    setTimeout(() => { oscillator.stop(); }, 150);
}

// ─── MANEJO DE PISTOLA LECTORA USB ───
// Las pistolas USB simulan un teclado y terminan con Enter.
// Usamos un buffer con pequeño delay para capturar el código completo
// antes de procesarlo, evitando procesar caracteres parciales.
document.addEventListener('DOMContentLoaded', () => {
    const inputQR = document.getElementById('qr-input');
    if (!inputQR) return;

    let debounceTimer = null;

    // Usamos 'keydown' (más confiable que el deprecado 'keypress')
    inputQR.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();

            // Cancelamos cualquier timer previo para evitar doble disparo
            if (debounceTimer) clearTimeout(debounceTimer);

            // Pequeño delay para asegurar que la pistola terminó de escribir
            debounceTimer = setTimeout(() => {
                const codigoLimpio = limpiarCodigo(inputQR.value);
                inputQR.value = codigoLimpio;

                if (codigoLimpio && typeof validarQR === 'function') {
                    validarQR(codigoLimpio);
                }
                debounceTimer = null;
            }, 50); // 50ms es suficiente para que la pistola termine de enviar
        }
    });

    // También limpiamos el valor en tiempo real mientras se escribe,
    // para que el usuario no vea caracteres raros en el input
    inputQR.addEventListener('input', function() {
        const pos = inputQR.selectionStart;
        const cleaned = inputQR.value.replace(/['"]/g, '').replace(/[\x00-\x1F\x7F]/g, '');
        if (cleaned !== inputQR.value) {
            inputQR.value = cleaned;
            // Restaurar posición del cursor
            inputQR.setSelectionRange(pos, pos);
        }
    });
});