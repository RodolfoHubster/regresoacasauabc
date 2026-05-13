<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Juancarlos\Regresoacasauabc\Http\RequestValidator;
use Juancarlos\Regresoacasauabc\Event\EventoStatusValidator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id          = $_POST['id']           ?? null;
        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $fecha       = trim($_POST['fecha']       ?? '');
        $hora        = trim($_POST['hora']        ?? '');
        $ubicacion   = trim($_POST['ubicacion']   ?? '');
        $imagen      = trim($_POST['imagen']      ?? '');
        $estadoNuevo = trim($_POST['estado']      ?? '');

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID de evento no proporcionado.']);
            exit;
        }

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

        // --- Fecha válida ---
        if (!RequestValidator::isFutureOrTodayDate($fecha)) {
            echo json_encode(['status' => 'error', 'message' => 'La fecha del evento debe ser hoy o en el futuro.']);
            exit;
        }

        // --- Validar transición de estado ---
        $stmtActual = $conexion->prepare("SELECT estado FROM evento WHERE id = :id LIMIT 1");
        $stmtActual->execute([':id' => $id]);
        $eventoActual = $stmtActual->fetch(PDO::FETCH_ASSOC);

        if (!$eventoActual) {
            echo json_encode(['status' => 'error', 'message' => 'El evento no existe.']);
            exit;
        }

        $estadoActual = $eventoActual['estado'];

        // Solo validar transición si el estado cambió
        if ($estadoNuevo !== $estadoActual) {
            if (!EventoStatusValidator::isValidTransition($estadoActual, $estadoNuevo)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => "Transición de estado no permitida: '$estadoActual' → '$estadoNuevo'. Valores válidos: activo, proximo, cerrado.",
                ]);
                exit;
            }
        }

        $sql  = "UPDATE evento
                 SET nombre = :nombre, descripcion = :descripcion, fecha = :fecha,
                     hora = :hora, ubicacion = :ubicacion, imagen = :imagen, estado = :estado
                 WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id'          => $id,
            ':nombre'      => $nombre,
            ':descripcion' => $descripcion,
            ':fecha'       => $fecha,
            ':hora'        => $hora,
            ':ubicacion'   => $ubicacion,
            ':imagen'      => $imagen,
            ':estado'      => $estadoNuevo,
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Evento actualizado correctamente']);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
