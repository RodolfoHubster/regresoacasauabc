<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    // 1. Detectamos si JS nos mandó el ID del evento en la URL (?evento_id=X)
    $evento_id = $_GET['evento_id'] ?? null;

    // 2. Preparamos la consulta base (con tus JOINs originales)
    $sql = "SELECT f.*, e.nombre as evento_nombre, c.nombre as campus_nombre 
            FROM faq f 
            LEFT JOIN evento e ON f.evento_id = e.id
            LEFT JOIN campus c ON e.campus_id = c.id";

    if ($evento_id) {
        // 3a. Si HAY un evento_id, filtramos la consulta con WHERE
        $sql .= " WHERE f.evento_id = :evento_id ORDER BY f.orden ASC, f.id DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':evento_id' => $evento_id]);
    } else {
        // 3b. Si NO hay evento_id, traemos todas (tu comportamiento original)
        $sql .= " ORDER BY f.orden ASC, f.id DESC";
        $stmt = $conexion->query($sql);
    }
            
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $faqs]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>