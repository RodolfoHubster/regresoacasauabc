<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    // Traemos a TODOS los registrados. 
    // Usamos LEFT JOIN con 'evento' conectándolo por el campus_id para saber a qué evento van.
    $sql = "SELECT 
                r.id,
                r.nombre, 
                r.apellidos, 
                r.correo, 
                r.generacion,
                r.tipo_asistente,
                r.correo_enviado,
                r.asistencia,
                c.nombre as campus, 
                ca.nombre as carrera,
                e.nombre as evento_nombre
            FROM registro_asistente r
            LEFT JOIN campus c ON r.campus_id = c.id
            LEFT JOIN carrera ca ON r.carrera_id = ca.id
            LEFT JOIN evento e ON r.campus_id = e.campus_id
            ORDER BY r.id DESC";
            
    $stmt = $conexion->query($sql);
    $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $asistentes]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>