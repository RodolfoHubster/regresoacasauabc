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

    // Obtener todas las FAQs del mismo evento (o generales) ordenadas actualmente
    $evento_cond = $actual['evento_id'] ? "evento_id = " . intval($actual['evento_id']) : "evento_id IS NULL";
    
    $stmtAll = $conexion->prepare("SELECT id FROM faq WHERE $evento_cond ORDER BY orden ASC, id ASC");
    $stmtAll->execute();
    $faqs = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
    
    $currentIndex = -1;
    for ($i = 0; $i < count($faqs); $i++) {
        if ($faqs[$i]['id'] == $id) {
            $currentIndex = $i;
            break;
        }
    }
    
    if ($currentIndex !== -1) {
        $targetIndex = $currentIndex;
        if ($direccion === 'subir' && $currentIndex > 0) {
            $targetIndex = $currentIndex - 1;
        } else if ($direccion === 'bajar' && $currentIndex < count($faqs) - 1) {
            $targetIndex = $currentIndex + 1;
        }
        
        // Intercambiar posiciones en el array
        if ($targetIndex !== $currentIndex) {
            $temp = $faqs[$currentIndex];
            $faqs[$currentIndex] = $faqs[$targetIndex];
            $faqs[$targetIndex] = $temp;
        }
    }
    
    // Normalizar todos los órdenes para que siempre sean 1, 2, 3... (sin negativos ni saltos)
    $conexion->beginTransaction();
    foreach ($faqs as $index => $f) {
        $nuevoOrden = $index + 1;
        $conexion->prepare("UPDATE faq SET orden = :o WHERE id = :i")->execute([':o' => $nuevoOrden, ':i' => $f['id']]);
    }
    $conexion->commit();

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
