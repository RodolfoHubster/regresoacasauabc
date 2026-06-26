<?php
require_once 'conexion.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT id, nombre FROM facultad ORDER BY nombre ASC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $facultades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $facultades]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
