<?php
require_once __DIR__ . '/../admin/php/conexion.php'; // Usa tu archivo de conexión seguro

header('Content-Type: application/json');

if (isset($_GET['campus_id'])) {
    $campus_id = (int)$_GET['campus_id'];
    try {
        $stmt = $conexion->prepare("SELECT id, nombre FROM facultad WHERE campus_id = :campus_id ORDER BY nombre ASC");
        $stmt->execute([':campus_id' => $campus_id]);
        $facultades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $facultades]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}