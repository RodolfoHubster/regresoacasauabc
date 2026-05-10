<?php
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo'])) {
    $codigo = $_POST['codigo'];

    try {
        // Buscar a la persona y hacer JOIN para traer nombres bonitos
        $sql = "SELECT r.*, c.nombre as campus_nombre, ca.nombre as carrera_nombre 
                FROM registro_asistente r
                INNER JOIN campus c ON r.campus_id = c.id
                INNER JOIN carrera ca ON r.carrera_id = ca.id
                WHERE r.qr_codigo = :codigo LIMIT 1";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':codigo' => $codigo]);
        $asistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($asistente) {
            if ($asistente['asistencia'] == 1) {
                // Si ya había asistido antes
                echo json_encode(['status' => 'already_scanned', 'data' => $asistente]);
            } else {
                // Es la primera vez que se escanea, marcar asistencia = 1
                $update = $conexion->prepare("UPDATE registro_asistente SET asistencia = 1 WHERE id = :id");
                $update->execute([':id' => $asistente['id']]);

                echo json_encode(['status' => 'success', 'data' => [
                    'nombre' => $asistente['nombre'],
                    'apellidos' => $asistente['apellidos'],
                    'campus' => $asistente['campus_nombre'],
                    'carrera' => $asistente['carrera_nombre']
                ]]);
            }
        } else {
            // No existe ese código
            echo json_encode(['status' => 'error', 'message' => 'Código no encontrado']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error BD']);
    }
}
?>