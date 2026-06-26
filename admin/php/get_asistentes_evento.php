<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if (isset($_GET['evento_id'])) {
    try {
        $id = $_GET['evento_id'];

        // 1. OBTENER EL NOMBRE DEL EVENTO (NUEVO)
        $stmtEvento = $conexion->prepare("SELECT nombre FROM evento WHERE id = :id");
        $stmtEvento->execute([':id' => $id]);
        $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);
        $nombreEvento = $evento ? $evento['nombre'] : 'Participantes del Evento';
        
        // 2. OBTENER LOS ASISTENTES (Tu consulta original intacta)
        $sql = "SELECT r.id, r.nombre, r.apellidos, r.correo,
                       cp.nombre AS campus_nombre,
                       f.nombre  AS facultad_nombre,
                       cr.nombre AS carrera_nombre,
                       r.facultad_otra,
                       r.carrera_otra,
                       r.generacion,
                       r.tipo_asistente,
                       r.correo_enviado,
                       r.asistencia,
                       r.necesidad_movilidad,
                       r.necesidad_especificacion
                FROM registro_asistente r
                LEFT JOIN campus cp ON r.campus_id = cp.id
                LEFT JOIN facultad f ON r.facultad_id = f.id
                LEFT JOIN carrera cr ON r.carrera_id = cr.id
                WHERE r.evento_id = :id 
                ORDER BY r.apellidos ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. ENVIAR TODO AL JAVASCRIPT
        echo json_encode([
            'status' => 'success', 
            'evento_nombre' => $nombreEvento, // Esto actualizará el título en participantes.php
            'data' => $asistentes
        ]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Falta el ID del evento']);
}
?>