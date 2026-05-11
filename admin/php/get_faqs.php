<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    // Traemos las preguntas y hacemos JOIN para saber de qué evento/campus son
    $sql = "SELECT f.*, e.nombre as evento_nombre, c.nombre as campus_nombre 
            FROM faq f 
            LEFT JOIN evento e ON f.evento_id = e.id
            LEFT JOIN campus c ON e.campus_id = c.id
            ORDER BY f.id DESC";
            
    $stmt = $conexion->query($sql);
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $faqs]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>