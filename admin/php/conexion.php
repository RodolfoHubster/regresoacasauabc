<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

try {
    // Le indicamos a Dotenv dónde está el archivo .env (en la raíz)
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();

    // Leemos las credenciales usando $_ENV
    $host = $_ENV['DB_HOST'];
    $db   = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];

    // Creamos la conexión PDO
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $conexion->exec("ALTER TABLE faq ADD COLUMN orden INT DEFAULT 0");
        $conexion->exec("ALTER TABLE faq ADD COLUMN oculto TINYINT(1) DEFAULT 0");
    } catch(Exception $e) {}

} catch (Exception $e) {
    // Si hay error en el .env o en la base de datos, lo capturamos
    error_log("Error de conexión: " . $e->getMessage());
    die("Error real de BD: " . $e->getMessage());
}
?>