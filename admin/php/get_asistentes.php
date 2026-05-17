<?php
require_once 'conexion.php';
header('Content-Type: application/json');

try {
    // Seleccionamos todo de registro_asistente y traemos los nombres reales con JOIN
    $sql = "SELECT r.*, 
                   c.nombre as campus, 
                   f.nombre as facultad_nombre, 
                   car.nombre as carrera_nombre, 
                   e.nombre as evento_nombre
            FROM registro_asistente r
            LEFT JOIN campus c ON r.campus_id = c.id
            LEFT JOIN facultad f ON r.facultad_id = f.id
            LEFT JOIN carrera car ON r.carrera_id = car.id
            LEFT JOIN evento e ON r.evento_id = e.id
            ORDER BY r.id DESC";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $asistentes]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>