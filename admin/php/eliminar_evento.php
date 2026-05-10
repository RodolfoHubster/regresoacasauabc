<?php
require_once 'conexion.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id'])) {
    try {
        $id = $data['id'];
        $conexion->beginTransaction();

        // 1. Borrar asistentes vinculados (Asegúrate de que la tabla sea registro_asistente)
        $sql1 = "DELETE FROM registro_asistente WHERE evento_id = :id";
        $stmt1 = $conexion->prepare($sql1);
        $stmt1->execute([':id' => $id]);

        // 2. Borrar el evento (CAMBIA 'eventos' POR EL NOMBRE REAL, ej: 'evento')
        $sql2 = "DELETE FROM evento WHERE id = :id"; // <-- Aquí estaba el error
        $stmt2 = $conexion->prepare($sql2);
        $stmt2->execute([':id' => $id]);

        $conexion->commit();
        echo json_encode(['status' => 'success', 'message' => 'Evento eliminado con éxito']);
    } catch (Exception $e) {
        $conexion->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID de evento no proporcionado.']);
}