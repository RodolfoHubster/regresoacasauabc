<?php
require_once 'conexion.php'; 
header('Content-Type: application/json');

try {
    // MODIFICACIÓN: Quitamos el WHERE estado != 'cerrado' para traer TODOS los eventos.
    // También hacemos un JOIN para traernos el nombre del campus de una vez.
    $sql = "SELECT e.*, c.nombre as campus_nombre 
            FROM evento e 
            LEFT JOIN campus c ON e.campus_id = c.id 
            ORDER BY e.fecha ASC";
            
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $eventos]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>