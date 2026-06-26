<?php
// Script de Pruebas Automatizadas para API de QR y Correos
error_reporting(E_ALL);
ini_set('display_errors', 1);

$baseURL = 'http://localhost/regresoacasauabc/admin/php/';
$results = [];

// Función para hacer peticiones HTTP con cURL
function makeRequest($endpoint, $postData = null, $cookie = null) {
    global $baseURL;
    $ch = curl_init($baseURL . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($postData !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    }
    if ($cookie) {
        curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=$cookie");
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['code' => $httpCode, 'body' => $response];
}

// Crear script mock_login dinámicamente para que no falte
$mockScript = "<?php\nsession_start();\n\$_SESSION['admin_logeado'] = true;\n\$_SESSION['ultimo_acceso'] = time();\necho json_encode(['status' => 'success', 'session_id' => session_id()]);\n";
file_put_contents(__DIR__ . '/admin/php/mock_login.php', $mockScript);

// 1. Obtener Sesión (Mock Login)
$login = makeRequest('mock_login.php');
$sessionCookie = null;
if ($login['code'] == 200) {
    $data = json_decode($login['body'], true);
    if (isset($data['session_id'])) {
        $sessionCookie = $data['session_id'];
        $results[] = ["Obtener Sesión Admin", "Éxito", "Sesión iniciada correctamente.", "pass"];
    } else {
        $results[] = ["Obtener Sesión Admin", "Fallo", "No se obtuvo session_id.", "fail"];
    }
} else {
    $results[] = ["Obtener Sesión Admin", "Fallo", "HTTP Code: " . $login['code'], "fail"];
}

// 2. Preparar Datos de Prueba en BD
require_once __DIR__ . '/admin/php/conexion.php';
$testEventId = null;
$testParticipantId = null;
$testQr = 'UABC-TEST' . rand(1000,9999);

try {
    // Crear evento falso
    $conexion->query("INSERT INTO evento (campus_id, nombre, fecha, hora, ubicacion, estado) VALUES (1, 'Evento de Prueba API', '2030-01-01', '12:00:00', 'Ubicación Test', 'activo')");
    $testEventId = $conexion->lastInsertId();

    // Crear asistente falso (Agregado campus_id, facultad_id y carrera_id para evitar error de FK)
    $conexion->query("INSERT INTO registro_asistente (nombre, apellidos, correo, campus_id, facultad_id, carrera_id, evento_id, qr_codigo, asistencia, correo_enviado) VALUES ('Asistente', 'Prueba', 'test@example.com', 1, 1, 1, $testEventId, '$testQr', 0, 0)");
    $testParticipantId = $conexion->lastInsertId();
    
    $results[] = ["Preparar Datos DB", "Éxito", "Evento #$testEventId y Asistente #$testParticipantId ($testQr) creados.", "pass"];
} catch (Exception $e) {
    $results[] = ["Preparar Datos DB", "Fallo", $e->getMessage(), "fail"];
}

if ($sessionCookie && $testParticipantId) {
    // 3. Pruebas de validar_qr.php
    
    // Prueba A: Sin parámetros
    $resA = makeRequest('validar_qr.php', [], $sessionCookie);
    $jsonA = json_decode($resA['body'], true);
    if ($jsonA && $jsonA['status'] === 'error') {
        $results[] = ["Validar QR: Sin parámetros", "Éxito", "Rechazado correctamente: " . $jsonA['message'], "pass"];
    } else {
        $results[] = ["Validar QR: Sin parámetros", "Fallo", "Respuesta inesperada: " . $resA['body'], "fail"];
    }

    // Prueba B: QR Falso
    $resB = makeRequest('validar_qr.php', ['codigo' => 'UABC-FAKE99'], $sessionCookie);
    $jsonB = json_decode($resB['body'], true);
    if ($jsonB && $jsonB['status'] === 'error' && strpos($jsonB['message'], 'coincidencia') !== false) {
        $results[] = ["Validar QR: Código Inexistente", "Éxito", "Rechazado correctamente.", "pass"];
    } else {
        $results[] = ["Validar QR: Código Inexistente", "Fallo", "Respuesta inesperada: " . $resB['body'], "fail"];
    }

    // Prueba C: QR Válido (Minúsculas y sin guion para probar normalización)
    $codigoSucio = strtolower(str_replace('-', '', $testQr));
    $resC = makeRequest('validar_qr.php', ['codigo' => $codigoSucio], $sessionCookie);
    $jsonC = json_decode($resC['body'], true);
    if ($jsonC && $jsonC['status'] === 'success') {
        $results[] = ["Validar QR: Código Válido (Normalización)", "Éxito", "Aceptado correctamente: " . json_encode($jsonC['data']), "pass"];
    } else {
        $results[] = ["Validar QR: Código Válido (Normalización)", "Fallo", "Respuesta inesperada: " . $resC['body'], "fail"];
    }

    // Prueba D: QR Duplicado (Ya escaneado en Prueba C)
    $resD = makeRequest('validar_qr.php', ['codigo' => $testQr], $sessionCookie);
    $jsonD = json_decode($resD['body'], true);
    if ($jsonD && $jsonD['status'] === 'already_scanned') {
        $results[] = ["Validar QR: Código Ya Escaneado", "Éxito", "Detectado como duplicado correctamente.", "pass"];
    } else {
        $results[] = ["Validar QR: Código Ya Escaneado", "Fallo", "Respuesta inesperada: " . $resD['body'], "fail"];
    }
    
    // Prueba E: Prueba de Endpoint Correo Sin Parámetros
    $resE = makeRequest('enviar_recordatorio.php', ['tipo' => 'todos'], $sessionCookie);
    $jsonE = json_decode($resE['body'], true);
    if ($jsonE && $jsonE['status'] === 'error') {
        $results[] = ["Enviar Correo: Sin ID Evento", "Éxito", "Rechazado correctamente: " . $jsonE['message'], "pass"];
    } else {
        $results[] = ["Enviar Correo: Sin ID Evento", "Fallo", "Respuesta inesperada: " . $resE['body'], "fail"];
    }
}

// Limpiar Datos de Prueba
if ($testParticipantId) {
    $conexion->query("DELETE FROM registro_asistente WHERE id = $testParticipantId");
}
if ($testEventId) {
    $conexion->query("DELETE FROM evento WHERE id = $testEventId");
}
$results[] = ["Limpieza DB", "Éxito", "Datos de prueba eliminados.", "pass"];

// Limpiar mock_login
@unlink(__DIR__ . '/admin/php/mock_login.php');

// Renderizar Resultados HTML
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados de Pruebas API</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f4f4f4; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #00723F; }
        .test-item { padding: 1rem; margin-bottom: 1rem; border-left: 4px solid #ccc; background: #fafafa; border-radius: 0 8px 8px 0; }
        .test-item.pass { border-color: #2e7d32; background: #f0fdf4; }
        .test-item.fail { border-color: #d32f2f; background: #fef2f2; }
        .test-title { font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between; }
        .badge { padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; color: white; }
        .badge.pass { background: #2e7d32; }
        .badge.fail { background: #d32f2f; }
        .test-details { font-size: 0.9rem; color: #555; font-family: monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Resultados Pruebas API (QR y Correos)</h1>
        <?php foreach ($results as $res): ?>
            <div class="test-item <?= $res[3] ?>">
                <div class="test-title">
                    <?= htmlspecialchars($res[0]) ?>
                    <span class="badge <?= $res[3] ?>"><?= strtoupper($res[1]) ?></span>
                </div>
                <div class="test-details"><?= htmlspecialchars($res[2]) ?></div>
            </div>
        <?php endforeach; ?>
        <p style="text-align:center; color:#666; margin-top: 2rem;"><i>Las pruebas han concluido y la base de datos se ha limpiado.</i></p>
    </div>
</body>
</html>
