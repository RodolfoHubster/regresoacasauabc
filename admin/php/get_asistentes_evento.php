<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if (isset($_GET['evento_id'])) {
    try {
        $id = $_GET['evento_id'];
        
        // Unimos con las tablas campus, facultad y carrera para obtener los nombres
        $sql = "SELECT r.*, 
                       cp.nombre as campus_nombre, 
                       f.nombre as facultad_nombre, 
                       cr.nombre as carrera_nombre
                FROM registro_asistente r
                LEFT JOIN campus cp ON r.campus_id = cp.id
                LEFT JOIN facultad f ON r.facultad_id = f.id
                LEFT JOIN carrera cr ON r.carrera_id = cr.id
                WHERE r.evento_id = :id 
                ORDER BY r.apellidos ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $asistentes]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>