<?php
require_once __DIR__ . '/../admin/php/conexion.php';

header('Content-Type: application/json');

if (isset($_GET['facultad_id'])) {
    $facultad_id = (int)$_GET['facultad_id'];
    try {
        $stmt = $conexion->prepare("SELECT id, nombre FROM carrera WHERE facultad_id = :facultad_id ORDER BY nombre ASC");
        $stmt->execute([':facultad_id' => $facultad_id]);
        $carreras = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $carreras]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener carreras']);
    }
}