<?php
require_once 'conexion.php';
header('Content-Type: application/json');

try {
    // Consultamos todos los campus ordenados alfabéticamente
    $sql = "SELECT * FROM campus ORDER BY nombre ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $campus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $campus]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>