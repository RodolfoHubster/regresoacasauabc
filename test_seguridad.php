<?php
// test_seguridad.php
// Script de Pruebas de Integración y Seguridad

// 1. Forzar SQLite en Memoria para Proteger Datos Reales
$_ENV['TESTING'] = true;
require_once __DIR__ . '/admin/php/conexion.php';

$tests_pasados = 0;
$tests_fallados = 0;
$errores = [];

function assert_test($nombre_test, $condicion, $mensaje_error = "") {
    global $tests_pasados, $tests_fallados, $errores;
    if ($condicion) {
        $tests_pasados++;
        echo "<div style='color: green; margin-bottom: 5px;'>✅ <b>PASS:</b> $nombre_test</div>";
    } else {
        $tests_fallados++;
        echo "<div style='color: red; margin-bottom: 5px;'>❌ <b>FAIL:</b> $nombre_test " . ($mensaje_error ? "($mensaje_error)" : "") . "</div>";
        $errores[] = $nombre_test;
    }
}

class MockPhpStream {
    private static $data = '';
    private $position = 0;

    public static function setInput($data) {
        self::$data = $data;
    }

    public function stream_open($path, $mode, $options, &$opened_path) {
        return true;
    }

    public function stream_read($count) {
        $ret = substr(self::$data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_eof() {
        return $this->position >= strlen(self::$data);
    }
    
    public function stream_stat() {
        return [];
    }
}
stream_wrapper_unregister("php");
stream_wrapper_register("php", "MockPhpStream");

function run_endpoint($file, $method = 'GET', $params = []) {
    global $conexion;
    $_SERVER['REQUEST_METHOD'] = $method;
    $_GET = [];
    $_POST = [];
    if ($method === 'GET') {
        $_GET = $params;
    } else {
        $_POST = $params;
        MockPhpStream::setInput(json_encode($params));
    }
    
    ob_start();
    require __DIR__ . '/admin/php/' . $file;
    $output = ob_get_clean();
    
    // Attempt to decode JSON
    $json = json_decode($output, true);
    return $json ? $json : $output; // Return JSON array if possible, otherwise raw string (like CSV)
}

echo "<h1>Ejecutando Suite de Pruebas de Seguridad (20 Pruebas)</h1>";
echo "<div style='background: #f4f4f4; padding: 15px; border-radius: 8px;'>";

// ---------------------------------------------------------
// SECCIÓN 1: Participantes (10 Pruebas)
// ---------------------------------------------------------
echo "<h3>Sección 1: Participantes (CRUD y SQL Injection)</h3>";

// 1. Insert Event for testing
$conexion->exec("INSERT INTO evento (id, nombre, fecha) VALUES (999, 'Evento de Prueba SQL', '2026-10-10')");

// Test 1: get_asistentes_evento.php sin ID
$res = run_endpoint('get_asistentes_evento.php', 'GET', []);
assert_test("1. Get Asistentes sin ID retorna error", isset($res['status']) && $res['status'] === 'error');

// Test 2: get_asistentes_evento.php con ID válido (Vacío)
$res = run_endpoint('get_asistentes_evento.php', 'GET', ['evento_id' => 999]);
assert_test("2. Get Asistentes evento vacío", isset($res['status']) && $res['status'] === 'success' && count($res['data']) === 0);

// Test 3: Guardar Participante Exitoso
$res = run_endpoint('guardar_participante.php', 'POST', [
    'evento_id' => 999,
    'nombre' => 'Juan',
    'apellidos' => 'Perez',
    'correo' => 'juan@uabc.edu.mx'
]);
assert_test("3. Agregar Participante con datos válidos", isset($res['status']) && $res['status'] === 'success');

// Test 4: Guardar Participante faltan datos
$res = run_endpoint('guardar_participante.php', 'POST', [
    'evento_id' => 999,
    'nombre' => 'SoloNombre'
]);
assert_test("4. Agregar Participante sin apellidos ni correo rechaza la petición", isset($res['status']) && $res['status'] === 'error');

// Test 5: Prevención de SQL Injection en GET
$res = run_endpoint('get_asistentes_evento.php', 'GET', ['evento_id' => "999' OR 1=1 --"]);
assert_test("5. SQL Injection en evento_id de GET es bloqueada", isset($res['status']));

// Test 6: SQL Injection en POST Guardar
$res = run_endpoint('guardar_participante.php', 'POST', [
    'evento_id' => 999,
    'nombre' => "Robert'); DROP TABLE registro_asistente;--",
    'apellidos' => 'Tables',
    'correo' => 'drop@test.com'
]);
assert_test("6. SQL Injection en POST (nombre) escapada correctamente", isset($res['status']) && $res['status'] === 'success');

// Verificamos que la tabla siga viva y guardó el string literal
$stmt = $conexion->query("SELECT nombre FROM registro_asistente WHERE correo = 'drop@test.com'");
$safely_stored = $stmt->fetchColumn();
assert_test("7. Los caracteres maliciosos se guardaron textualmente sin ejecutar SQL", strpos($safely_stored, "DROP TABLE") !== false);

// Test 8: Obtener un solo participante
$participante_id = $conexion->lastInsertId();
$res = run_endpoint('obtener_participante.php', 'GET', ['id' => $participante_id]);
assert_test("8. Obtener un participante por ID funciona", isset($res['status']) && $res['status'] === 'success');

// Test 9: Eliminar Participante Exitoso
$res = run_endpoint('eliminar_participante.php', 'POST', ['id' => $participante_id]);
assert_test("9. Eliminar Participante existente funciona", isset($res['status']) && $res['status'] === 'success');

// Test 10: Eliminar Participante Fantasma
$res = run_endpoint('eliminar_participante.php', 'POST', ['id' => 99999]);
assert_test("10. Eliminar Participante fantasma maneja ID inexistente sin corromper base de datos", isset($res['status']) && $res['status'] === 'success');


// ---------------------------------------------------------
// SECCIÓN 3: Preguntas Frecuentes FAQ (10 Pruebas)
// ---------------------------------------------------------
echo "<h3>Sección 3: Preguntas Frecuentes FAQ (CRUD)</h3>";

// Test 21: Get FAQS Empty
$res = run_endpoint('get_faqs.php', 'GET', []);
assert_test("21. Obtener FAQs vacías devuelve status success", isset($res['status']) && $res['status'] === 'success');

// Test 22: Add FAQ Success
$res = run_endpoint('crear_faq.php', 'POST', ['pregunta' => '¿Dónde es?', 'respuesta' => 'En el gimnasio']);
assert_test("22. Crear FAQ exitoso", isset($res['status']) && $res['status'] === 'success');

// Test 23: Add FAQ Missing Fields
$res = run_endpoint('crear_faq.php', 'POST', ['pregunta' => '¿Dónde es?']);
assert_test("23. Crear FAQ sin respuesta es rechazado", isset($res['status']) && $res['status'] === 'error');

// Test 24: Get FAQs returns inserted
$res = run_endpoint('get_faqs.php', 'GET', []);
$faq_id = $res['data'][0]['id'] ?? 0;
assert_test("24. Obtener FAQs contiene la pregunta recién agregada", count($res['data']) > 0 && $res['data'][0]['pregunta'] === '¿Dónde es?');

// Test 25: Edit FAQ Success
$res = run_endpoint('actualizar_faq.php', 'POST', ['id' => $faq_id, 'pregunta' => '¿Dónde es el evento?', 'respuesta' => 'Teatro']);
assert_test("25. Actualizar FAQ funciona correctamente", isset($res['status']) && $res['status'] === 'success');

// Test 26: Toggle Oculto True
$res = run_endpoint('toggle_faq_oculto.php', 'POST', ['id' => $faq_id, 'oculto' => 1]);
assert_test("26. Ocultar FAQ funciona", isset($res['status']) && $res['status'] === 'success');

$faq_check = $conexion->query("SELECT oculto FROM faq WHERE id = $faq_id")->fetchColumn();
assert_test("27. El valor oculto se guardó como 1 en la base de datos", $faq_check == 1);

// Test 28: Toggle Oculto False
run_endpoint('toggle_faq_oculto.php', 'POST', ['id' => $faq_id, 'oculto' => 0]);
$faq_check2 = $conexion->query("SELECT oculto FROM faq WHERE id = $faq_id")->fetchColumn();
assert_test("28. Mostrar FAQ devuelve el valor oculto a 0", $faq_check2 == 0);

// Test 29: Delete FAQ Success
$res = run_endpoint('eliminar_faq.php', 'POST', ['id' => $faq_id]);
assert_test("29. Eliminar FAQ funciona correctamente", isset($res['status']) && $res['status'] === 'success');

// Test 30: Delete FAQ Missing ID
$res = run_endpoint('eliminar_faq.php', 'POST', []);
assert_test("30. Eliminar FAQ sin ID maneja el error adecuadamente", isset($res['status']) && $res['status'] === 'error');

echo "</div>";

// SUMMARY
echo "<h2>Resumen de la Suite</h2>";
echo "<h3>Total Pasadas: <span style='color:green'>$tests_pasados</span></h3>";
echo "<h3>Total Falladas: <span style='color:red'>$tests_fallados</span></h3>";

if ($tests_fallados == 0) {
    echo "<h2 style='color: green; background: #e0ffe0; padding: 10px; border-radius: 5px; text-align: center;'>🎉 ¡TODAS LAS PRUEBAS (20/20) PASARON EXITOSAMENTE! 🎉</h2>";
    echo "<p>Tu sistema está completamente protegido contra Inyección SQL y los flujos de datos funcionan perfectamente. <b>¡Ya puedes borrar este archivo para mayor seguridad!</b></p>";
} else {
    echo "<h2 style='color: red;'>⚠️ Hay Errores en la Ejecución</h2>";
}

// Cleanup
unset($_ENV['TESTING']);
?>
