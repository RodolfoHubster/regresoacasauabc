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
      document.getElementById('qr-input').value = decodedText;
      
      // Llamamos directo a la función de admin.js
      if (typeof validarQR === 'function') {
          validarQR(decodedText);
      }

      setTimeout(() => {
          if (html5QrcodeScanner) html5QrcodeScanner.resume(); 
      }, 3000);
    }
  
    html5QrcodeScanner.render(onScanSuccess);
    escanerRenderizado = true;
};

function tocarBeep() {
    const context = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = context.createOscillator();
    oscillator.type = 'sine';
    oscillator.frequency.value = 800;
    oscillator.connect(context.destination);
    oscillator.start();
    setTimeout(() => { oscillator.stop(); }, 150);
}

// Escuchar la tecla "Enter" para pistolas USB (Esto sí se queda activo siempre)
document.addEventListener('DOMContentLoaded', () => {
    const inputQR = document.getElementById('qr-input');
    if (inputQR) {
        inputQR.addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); 
                if (typeof validarQR === 'function') validarQR(); 
            }
        });
    }
});