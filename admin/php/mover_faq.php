<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $direccion = $data['direccion'] ?? null; // 'subir' or 'bajar'

    if (!$id || !$direccion) throw new Exception("Parámetros incompletos.");

    // Obtener el FAQ actual
    $stmt = $conexion->prepare("SELECT id, orden, evento_id FROM faq WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $actual = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$actual) throw new Exception("FAQ no encontrada.");

    // Ojo: En MySQL si evento_id es nulo se usa "IS NULL", pero en la app usan = o IS NULL
    $evento_cond = $actual['evento_id'] ? "evento_id = " . intval($actual['evento_id']) : "evento_id IS NULL";

    if ($direccion === 'subir') {
        // Encontrar el inmediatamente anterior
        $stmt2 = $conexion->prepare("SELECT id, orden FROM faq WHERE $evento_cond AND (orden < :orden OR (orden = :orden AND id > :id)) ORDER BY orden DESC, id ASC LIMIT 1");
        $stmt2->execute([':orden' => $actual['orden'], ':id' => $id]);
    } else {
        // Encontrar el inmediatamente siguiente
        $stmt2 = $conexion->prepare("SELECT id, orden FROM faq WHERE $evento_cond AND (orden > :orden OR (orden = :orden AND id < :id)) ORDER BY orden ASC, id DESC LIMIT 1");
        $stmt2->execute([':orden' => $actual['orden'], ':id' => $id]);
    }

    $adyacente = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($adyacente) {
        $orden_actual = $actual['orden'];
        $orden_adyacente = $adyacente['orden'];

        // Si por casualidad tienen el mismo orden (ej. por default 0), forzamos un desfase
        if ($orden_actual == $orden_adyacente) {
            if ($direccion === 'subir') {
                $orden_actual = $orden_adyacente - 1;
            } else {
                $orden_actual = $orden_adyacente + 1;
            }
        }

        $conexion->beginTransaction();
        $conexion->prepare("UPDATE faq SET orden = :o WHERE id = :i")->execute([':o' => $orden_adyacente, ':i' => $actual['id']]);
        $conexion->prepare("UPDATE faq SET orden = :o WHERE id = :i")->execute([':o' => $orden_actual, ':i' => $adyacente['id']]);
        $conexion->commit();
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
