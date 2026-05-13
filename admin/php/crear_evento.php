<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Juancarlos\Regresoacasauabc\Http\RequestValidator;
use Juancarlos\Regresoacasauabc\Event\EventoStatusValidator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha       = trim($_POST['fecha']       ?? '');
        $hora        = trim($_POST['hora']        ?? '');
        $ubicacion   = trim($_POST['ubicacion']   ?? '');
        $imagen      = trim($_POST['imagen']      ?? '');
        $estado      = trim($_POST['estado']      ?? 'proximo');

        // --- Campos obligatorios ---
        if (!RequestValidator::hasRequiredEventFields([
            'nombre'    => $nombre,
            'fecha'     => $fecha,
            'hora'      => $hora,
            'ubicacion' => $ubicacion,
        ])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios (nombre, fecha, hora, ubicación).']);
            exit;
        }

        // --- Fecha debe ser hoy o en el futuro ---
        if (!RequestValidator::isFutureOrTodayDate($fecha)) {
            echo json_encode(['status' => 'error', 'message' => 'La fecha del evento debe ser hoy o en el futuro (formato YYYY-MM-DD).']);
            exit;
        }

        // --- Estado válido ---
        if (!EventoStatusValidator::isValidStatus($estado)) {
            echo json_encode(['status' => 'error', 'message' => 'Estado no válido. Valores permitidos: activo, proximo, cerrado.']);
            exit;
        }

        $sql  = "INSERT INTO evento (nombre, descripcion, fecha, hora, ubicacion, imagen, estado)
                 VALUES (:nombre, :descripcion, :fecha, :hora, :ubicacion, :imagen, :estado)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre'      => $nombre,
            ':descripcion' => $descripcion,
            ':fecha'       => $fecha,
            ':hora'        => $hora,
            ':ubicacion'   => $ubicacion,
            ':imagen'      => $imagen,
            ':estado'      => $estado,
        ]);

        $eventId = $conexion->lastInsertId();

        echo json_encode([
            'status'  => 'success',
            'message' => 'Evento creado con éxito',
            'eventId' => $eventId,
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
