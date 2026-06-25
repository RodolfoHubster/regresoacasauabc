<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (empty($id)) {
        throw new Exception("ID de participante no proporcionado.");
    }

    $sql = "DELETE FROM registro_asistente WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Participante eliminado exitosamente.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
