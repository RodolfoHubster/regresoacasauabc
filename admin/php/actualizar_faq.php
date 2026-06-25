<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $pregunta = trim($_POST['pregunta'] ?? '');
    $respuesta = trim($_POST['respuesta'] ?? '');
    $evento_id = !empty($_POST['evento_id']) ? $_POST['evento_id'] : null;

    if (!$id || empty($pregunta) || empty($respuesta)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios.']);
        return;
    }

    try {
        $sql = "UPDATE faq SET evento_id = :evento_id, pregunta = :pregunta, respuesta = :respuesta WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':evento_id' => $evento_id,
            ':pregunta' => $pregunta,
            ':respuesta' => $respuesta,
            ':id' => $id
        ]);

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>