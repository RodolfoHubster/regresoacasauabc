<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    // Esta consulta cuenta cuántos registros hay para cada campus asociado a un evento
    $sql = "SELECT 
                e.id, 
                e.nombre, 
                c.nombre as campus, 
                e.fecha, 
                e.estado,
                (SELECT COUNT(*) FROM registro_asistente r WHERE r.campus_id = e.campus_id) as total_registros
            FROM evento e
            JOIN campus c ON e.campus_id = c.id
            ORDER BY e.fecha ASC";
            
    $stmt = $conexion->query($sql);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $eventos]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}