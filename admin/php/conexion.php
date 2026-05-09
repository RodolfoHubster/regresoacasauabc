<?php
$host = "localhost";
$user = "root"; // Tu usuario de base de datos
$pass = "";     // Tu contraseña de base de datos
$db   = "regreso_a_casa"; // Asegúrate de que este sea el nombre de tu BD

try {
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // Configurar para que lance excepciones en caso de error
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>