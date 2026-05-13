<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Juancarlos\Regresoacasauabc\QrCode\QrCodeValidator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['codigo'])) {
    $codigo = trim($_POST['codigo']);

    // Rechazar de inmediato si el formato no es UABC-XX-YYYY-NNNNN
    if (!QrCodeValidator::isValidCode($codigo)) {
        echo json_encode(['status' => 'error', 'message' => 'Formato de código QR no válido.']);
        exit;
    }

    try {
        $sql = "SELECT r.*, c.nombre AS campus_nombre, ca.nombre AS carrera_nombre
                FROM registro_asistente r
                INNER JOIN campus c  ON r.campus_id  = c.id
                INNER JOIN carrera ca ON r.carrera_id = ca.id
                WHERE r.qr_codigo = :codigo LIMIT 1";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([':codigo' => $codigo]);
        $asistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($asistente) {
            if ($asistente['asistencia'] == 1) {
                echo json_encode(['status' => 'already_scanned', 'data' => $asistente]);
            } else {
                $update = $conexion->prepare(
                    "UPDATE registro_asistente SET asistencia = 1 WHERE id = :id"
                );
                $update->execute([':id' => $asistente['id']]);

                echo json_encode(['status' => 'success', 'data' => [
                    'nombre'    => $asistente['nombre'],
                    'apellidos' => $asistente['apellidos'],
                    'campus'    => $asistente['campus_nombre'],
                    'carrera'   => $asistente['carrera_nombre'],
                ]]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Código no encontrado.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en base de datos.']);
    }
}
?>
