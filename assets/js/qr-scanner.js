let html5QrcodeScanner;

document.addEventListener('DOMContentLoaded', () => {
    // Inicializar el escáner
    html5QrcodeScanner = new Html5QrcodeScanner(
      "lector-camara", 
      { fps: 10, qrbox: {width: 250, height: 250} }, 
      false
    );
  
    // Esta función se ejecuta mágicamente cuando la cámara detecta un QR
    function onScanSuccess(decodedText, decodedResult) {
      // 1. Pausar el escáner para que no lea el mismo QR 10 veces por segundo
      html5QrcodeScanner.pause();
  
      // 2. Opcional: Hacer un sonido de "Beep"
      tocarBeep();
  
      // 3. Poner el código en el input manual para que el usuario lo vea
      document.getElementById('qr-input').value = decodedText;
  
      // 4. Enviar al servidor para registrar la asistencia
      validarQRBD(decodedText);
    }
  
    // Renderizar la cámara en el div que creamos
    html5QrcodeScanner.render(onScanSuccess);
});
  
// Función para hacer un ruidito cuando escanea (tipo cajero de súper)
function tocarBeep() {
    const context = new (window.AudioContext || window.webkitAudioContext)();
    const oscillator = context.createOscillator();
    oscillator.type = 'sine';
    oscillator.frequency.value = 800;
    oscillator.connect(context.destination);
    oscillator.start();
    setTimeout(() => { oscillator.stop(); }, 150);
}

// Función que manda el código a PHP
function validarQRBD(codigoQR) {
    const resultDiv = document.getElementById('qr-result');
    const resultName = document.getElementById('qr-result-name');
    const resultDetail = document.getElementById('qr-result-detail');
    const resultStatus = document.querySelector('.qr-result-status');

    fetch('../admin/php/validar_qr.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'codigo=' + encodeURIComponent(codigoQR)
    })
    .then(response => response.json())
    .then(res => {
        resultDiv.hidden = false;
        
        if (res.status === 'success') {
            resultStatus.textContent = "¡Acceso Permitido!";
            resultStatus.style.color = "green";
            resultName.textContent = res.data.nombre + ' ' + res.data.apellidos;
            resultDetail.textContent = res.data.campus + ' · ' + res.data.carrera;
            
            // ACTUALIZACIÓN EN TIEMPO REAL:
            // Llamamos a las funciones que ya tienes definidas en admin.js
            if (typeof cargarTablaAsistentes === 'function') {
                cargarTablaAsistentes(); 
            }
            if (typeof cargarDashboardStats === 'function') {
                cargarDashboardStats();
            }

        } else if (res.status === 'already_scanned') {
            resultStatus.textContent = "⚠️ QR Ya fue utilizado";
            resultStatus.style.color = "orange";
            resultName.textContent = res.data.nombre + ' ' + res.data.apellidos;
            resultDetail.textContent = "Este asistente ya había registrado su entrada.";
        } else {
            resultStatus.textContent = "❌ QR Inválido";
            resultStatus.style.color = "red";
            resultName.textContent = "Código no encontrado";
            resultDetail.textContent = "Verifica que pertenezca a este evento.";
        }

        // Reactivar la cámara después de 3 segundos para el siguiente asistente
        // Busca esta parte al final de la función validarQRBD
      setTimeout(() => {
          // 1. Limpiamos el campo de texto
          document.getElementById('qr-input').value = '';
          
          // 2. Ocultamos el cuadro con el resultado del escaneo anterior
          resultDiv.hidden = true;
          
          // 3. ¡LA CLAVE! Reactivamos el escáner sin recargar la página
          // Esto permite que la cámara siga encendida y lista para el siguiente
          if (html5QrcodeScanner) {
              html5QrcodeScanner.resume(); 
          }
          
          // Quitamos el location.reload(); <-- BORRA ESA LÍNEA
      }, 3000); // 3 segundos es ideal para que alcances a leer el nombre y pase el siguiente
    })
    .catch(err => console.error("Error validando:", err));
}

// Para el botón manual que ya tenías
function validarQR() {
    const input = document.getElementById('qr-input').value;
    if(input.trim() !== '') {
        validarQRBD(input);
    }
}

// Escuchar la tecla "Enter" en el campo de texto (Ideal para pistolas USB)
document.addEventListener('DOMContentLoaded', () => {
    const inputQR = document.getElementById('qr-input');
    if (inputQR) {
        inputQR.addEventListener('keypress', function(event) {
            // Si la tecla presionada es "Enter"
            if (event.key === 'Enter') {
                event.preventDefault(); // Evita que la página se recargue
                validarQR(); // Ejecuta la misma función que el botón
            }
        });
    }
});