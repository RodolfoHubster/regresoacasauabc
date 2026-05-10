<?php
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

try {
    // Quitamos f.codigo porque no existe en tu tabla facultad
    $sql = "SELECT 
                r.nombre, 
                r.apellidos, 
                r.correo, 
                r.generacion,
                c.nombre as campus, 
                f.nombre as facultad_nombre, 
                ca.nombre as carrera
            FROM registro_asistente r
            INNER JOIN campus c ON r.campus_id = c.id
            INNER JOIN facultad f ON r.facultad_id = f.id
            INNER JOIN carrera ca ON r.carrera_id = ca.id
            WHERE r.asistencia = 1
            ORDER BY r.id DESC";
            
    $stmt = $conexion->query($sql);
    $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $asistentes]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}