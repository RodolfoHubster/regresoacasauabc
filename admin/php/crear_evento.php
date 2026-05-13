<?php
require_once 'conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Juancarlos\Regresoacasauabc\Http\RequestValidator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $imagen = $_POST['imagen'] ?? '';
        $estado = $_POST['estado'] ?? 'activo';

        // Validar que los campos requeridos no vengan vacíos
        if (!RequestValidator::hasRequiredEventFields([
            'nombre' => $nombre,
            'fecha' => $fecha,
            'hora' => $hora,
            'ubicacion' => $ubicacion
        ])) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
            exit;
        }

        $sql = "INSERT INTO evento (nombre, descripcion, fecha, hora, ubicacion, imagen, estado) 
                VALUES (:nombre, :descripcion, :fecha, :hora, :ubicacion, :imagen, :estado)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':fecha' => $fecha,
            ':hora' => $hora,
            ':ubicacion' => $ubicacion,
            ':imagen' => $imagen,
            ':estado' => $estado
        ]);

        // Obtenemos el ID que la base de datos le acaba de asignar al evento
        $eventId = $conexion->lastInsertId();

        echo json_encode([
            'status' => 'success', 
            'message' => 'Evento creado con éxito',
            'eventId' => $eventId
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
