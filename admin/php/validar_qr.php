<?php
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibimos ya sea el código escaneado o el ID seleccionado manualmente
    $codigo = $_POST['codigo'] ?? null;
    $id = $_POST['id'] ?? null;

    if (empty($codigo) && empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'No se recibió ningún dato para validar.']);
        exit;
    }

    try {
        // Usamos LEFT JOIN para evitar que la validación falle si alguien se registró con "Otra carrera"
        $sql = "SELECT r.*, c.nombre as campus_nombre, ca.nombre as carrera_nombre, e.nombre as evento_nombre 
                FROM registro_asistente r
                LEFT JOIN campus c ON r.campus_id = c.id
                LEFT JOIN carrera ca ON r.carrera_id = ca.id 
                LEFT JOIN evento e ON r.evento_id = e.id ";
        
        // Dependiendo de qué nos enviaron, adaptamos la búsqueda
        if (!empty($codigo)) {
            // Normalizamos el código: mayúsculas y reinsertar guion si falta
            $codigo = strtoupper(trim($codigo));
            // Si empieza con UABC seguido de alfanumérico sin guion, lo reinsertamos
            if (preg_match('/^UABC[A-Z0-9]/', $codigo) && substr($codigo, 4, 1) !== '-') {
                $codigo = 'UABC-' . substr($codigo, 4);
            }
            $sql .= "WHERE r.qr_codigo = :param LIMIT 1";
            $param = $codigo;
        } else {
            $sql .= "WHERE r.id = :param LIMIT 1";
            $param = $id;
        }
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':param' => $param]);
        $asistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($asistente) {
            if ($asistente['asistencia'] == 1) {
                // Si ya había asistido antes
                echo json_encode(['status' => 'already_scanned', 'data' => $asistente]);
            } else {
                // Es la primera vez que entra, marcar asistencia = 1
                $update = $conexion->prepare("UPDATE registro_asistente SET asistencia = 1 WHERE id = :id");
                $update->execute([':id' => $asistente['id']]);

                echo json_encode(['status' => 'success', 'data' => [
                    'nombre' => $asistente['nombre'],
                    'apellidos' => $asistente['apellidos'],
                    'campus' => $asistente['campus_nombre'],
                    'carrera' => $asistente['carrera_nombre'],
                    'evento_nombre' => $asistente['evento_nombre']
                ]]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontrar coincidencia']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error de conexión con la base de datos.']);
    }
}
?>