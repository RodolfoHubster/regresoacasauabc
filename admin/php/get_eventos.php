<?php
require_once 'conexion.php'; // Verifica que la ruta a tu conexión sea correcta
header('Content-Type: application/json');

try {
    // Solo traemos eventos que NO estén cerrados (opcional, puedes quitar el WHERE)
    $sql = "SELECT * FROM evento WHERE estado != 'cerrado' ORDER BY fecha ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $eventos]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>