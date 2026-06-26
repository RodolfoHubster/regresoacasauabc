<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

try {
    // Le indicamos a Dotenv dónde está el archivo .env (en la raíz)
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();

    // SOPORTE PARA PRUEBAS UNITARIAS EN MEMORIA
    if (isset($_ENV['TESTING']) && $_ENV['TESTING'] === true) {
        $conexion = new PDO("sqlite::memory:");
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear tablas necesarias para los tests
        $conexion->exec("CREATE TABLE IF NOT EXISTS faq (id INTEGER PRIMARY KEY AUTOINCREMENT, pregunta TEXT, respuesta TEXT, orden INTEGER DEFAULT 0, oculto INTEGER DEFAULT 0, evento_id INTEGER)");
        $conexion->exec("CREATE TABLE IF NOT EXISTS evento (id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT, fecha TEXT, campus_id INTEGER)");
        $conexion->exec("CREATE TABLE IF NOT EXISTS registro_asistente (id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT, apellidos TEXT, correo TEXT, evento_id INTEGER, asistencia INTEGER DEFAULT 0, correo_enviado INTEGER DEFAULT 0, necesidad_movilidad TEXT, necesidad_especificacion TEXT, tipo_asistente TEXT, generacion TEXT, campus_id INTEGER, facultad_id INTEGER, carrera_id INTEGER, facultad_otra TEXT, carrera_otra TEXT)");
        $conexion->exec("CREATE TABLE IF NOT EXISTS campus (id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT)");
        $conexion->exec("CREATE TABLE IF NOT EXISTS facultad (id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT)");
        $conexion->exec("CREATE TABLE IF NOT EXISTS carrera (id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT)");
    } else {
        // Leemos las credenciales usando $_ENV para MySQL
        $host = $_ENV['DB_HOST'];
        $db   = $_ENV['DB_NAME'];
        $user = $_ENV['DB_USER'];
        $pass = $_ENV['DB_PASS'];

        $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    try { $conexion->exec("ALTER TABLE faq ADD COLUMN orden INT DEFAULT 0"); } catch(Exception $e) {}
    try { $conexion->exec("ALTER TABLE faq ADD COLUMN oculto TINYINT(1) DEFAULT 0"); } catch(Exception $e) {}
    try { $conexion->exec("ALTER TABLE registro_asistente ADD COLUMN facultad_otra VARCHAR(255) NULL"); } catch(Exception $e) {}
    try { $conexion->exec("ALTER TABLE registro_asistente ADD COLUMN carrera_otra VARCHAR(255) NULL"); } catch(Exception $e) {}
    try { $conexion->exec("ALTER TABLE registro_asistente ADD COLUMN necesidad_movilidad TINYINT(1) DEFAULT 0"); } catch(Exception $e) {}
    try { $conexion->exec("ALTER TABLE registro_asistente ADD COLUMN necesidad_especificacion VARCHAR(255) NULL"); } catch(Exception $e) {}

} catch (Exception $e) {
    // Si hay error en el .env o en la base de datos, lo capturamos
    error_log("Error de conexión: " . $e->getMessage());
    die("Error real de BD: " . $e->getMessage());
}
?>