<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'ID no proporcionado.']);
        exit;
    }

    try {
        $stmt = $conexion->prepare("DELETE FROM faq WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>