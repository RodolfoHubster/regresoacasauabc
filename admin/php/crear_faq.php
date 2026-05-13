<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Juancarlos\Regresoacasauabc\Http\FaqValidator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pregunta  = trim($_POST['pregunta']  ?? '');
    $respuesta = trim($_POST['respuesta'] ?? '');
    $evento_id = !empty($_POST['evento_id']) ? $_POST['evento_id'] : null;

    if (!FaqValidator::isValid(['pregunta' => $pregunta, 'respuesta' => $respuesta])) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Pregunta y respuesta son obligatorias y no pueden superar los límites de longitud (pregunta: 255 caracteres, respuesta: 1000 caracteres).'
        ]);
        exit;
    }

    try {
        $sql  = "INSERT INTO faq (evento_id, pregunta, respuesta) VALUES (:evento_id, :pregunta, :respuesta)";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':evento_id' => $evento_id,
            ':pregunta'  => $pregunta,
            ':respuesta' => $respuesta,
        ]);

        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
