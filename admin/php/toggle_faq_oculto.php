<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $oculto = $data['oculto'] ?? 0;

    if (!$id) {
        throw new Exception("Faltan parámetros.");
    }

    $sql = "UPDATE faq SET oculto = :oculto WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':oculto' => $oculto, ':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Visibilidad actualizada.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
