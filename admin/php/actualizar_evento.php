<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? null;
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora = $_POST['hora'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $imagen = $_POST['imagen'] ?? '';
        $estado = $_POST['estado'] ?? 'activo';

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID de evento no proporcionado.']);
            exit;
        }

        $sql = "UPDATE evento SET 
                nombre = :nombre, descripcion = :descripcion, fecha = :fecha, 
                hora = :hora, ubicacion = :ubicacion, imagen = :imagen, estado = :estado 
                WHERE id = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $nombre,
            ':descripcion' => $descripcion,
            ':fecha' => $fecha,
            ':hora' => $hora,
            ':ubicacion' => $ubicacion,
            ':imagen' => $imagen,
            ':estado' => $estado
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Evento actualizado correctamente']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>