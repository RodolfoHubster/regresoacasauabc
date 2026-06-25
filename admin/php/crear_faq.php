<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pregunta = trim($_POST['pregunta'] ?? '');
    $respuesta = trim($_POST['respuesta'] ?? '');
    $evento_id = !empty($_POST['evento_id']) ? $_POST['evento_id'] : null;

    if (empty($pregunta) || empty($respuesta)) {
        echo json_encode(['status' => 'error', 'message' => 'Pregunta y respuesta son obligatorias.']);
        exit;
    }

    try {
        // Obtener el orden máximo actual para este evento
        $evento_cond = $evento_id ? "evento_id = " . intval($evento_id) : "evento_id IS NULL";
        $stmtMax = $conexion->prepare("SELECT MAX(orden) as max_o FROM faq WHERE $evento_cond");
        $stmtMax->execute();
        $resMax = $stmtMax->fetch(PDO::FETCH_ASSOC);
        $nuevoOrden = ($resMax && $resMax['max_o'] !== null) ? $resMax['max_o'] + 1 : 1;

        $sql = "INSERT INTO faq (evento_id, pregunta, respuesta, orden) VALUES (:evento_id, :pregunta, :respuesta, :orden)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':evento_id' => $evento_id,
            ':pregunta' => $pregunta,
            ':respuesta' => $respuesta,
            ':orden' => $nuevoOrden
        ]);

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>