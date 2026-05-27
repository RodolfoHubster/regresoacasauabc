<?php
require_once 'conexion.php';
header('Content-Type: application/json');

// Si viene campus_nombre, filtramos por ese campus
$campusNombre = isset($_GET['campus']) ? trim($_GET['campus']) : '';

try {
    if (!empty($campusNombre)) {
        $sql = "SELECT f.id, f.nombre, f.campus_id
                FROM facultad f
                JOIN campus c ON f.campus_id = c.id
                WHERE c.nombre = :campus
                ORDER BY f.nombre ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':campus' => $campusNombre]);
    } else {
        // Sin filtro: todas las facultades
        $sql = "SELECT id, nombre, campus_id FROM facultad ORDER BY nombre ASC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
    }

    $facultades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $facultades]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
