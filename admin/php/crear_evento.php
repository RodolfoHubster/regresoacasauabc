<?php
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $campus_id = $_POST['campus_id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $imagen = $_POST['imagen'] ?? '';
        $estado = $_POST['estado'] ?? 'activo';

        // Validar que los campos requeridos no vengan vacíos
        if (empty($nombre) || empty($fecha) || empty($hora) || empty($ubicacion)) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
            exit;
        }

        $sql = "INSERT INTO evento (campus_id, nombre, descripcion, fecha, hora, ubicacion, imagen, estado) 
                VALUES (:campus_id, :nombre, :descripcion, :fecha, :hora, :ubicacion, :imagen, :estado)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':campus_id' => $campus_id,
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