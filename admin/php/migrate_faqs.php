<?php
require_once __DIR__ . '/conexion.php';
try {
    $conexion->exec("ALTER TABLE faq ADD COLUMN orden INT DEFAULT 0");
    $conexion->exec("ALTER TABLE faq ADD COLUMN oculto TINYINT(1) DEFAULT 0");
    echo "Exito";
} catch (Exception $e) {
    echo "Error o ya existen: " . $e->getMessage();
}
?>
