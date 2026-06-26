<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    $id = $_GET['id'] ?? null;

    if (empty($id)) {
        throw new Exception("ID de participante no proporcionado.");
    }

    $sql = "SELECT * FROM registro_asistente WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id]);
    $participante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$participante) {
        throw new Exception("Participante no encontrado.");
    }

    echo json_encode(['status' => 'success', 'data' => $participante]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
