<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    $campus = isset($_GET['campus']) ? $_GET['campus'] : '';
    
    $sql = "SELECT 
                e.id, 
                e.nombre, 
                c.nombre as campus, 
                e.fecha, 
                e.estado,
                (SELECT COUNT(*) FROM registro_asistente r WHERE r.evento_id = e.id) as total_registros
            FROM evento e
            JOIN campus c ON e.campus_id = c.id";
            
    if ($campus !== '') {
        $sql .= " WHERE c.nombre = :campus";
    }
    
    $sql .= " ORDER BY e.fecha ASC";
            
    $stmt = $conexion->prepare($sql);
    if ($campus !== '') {
        $stmt->execute([':campus' => $campus]);
    } else {
        $stmt->execute();
    }
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $eventos]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}