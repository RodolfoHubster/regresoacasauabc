<?php
require_once 'conexion.php';
header('Content-Type: application/json');

if (isset($_GET['evento_id'])) {
    try {
        $id = $_GET['evento_id'];
        
        // CORRECCIÓN: Cambiamos 'asistente' por 'registro_asistente'
        $sql = "SELECT * FROM registro_asistente WHERE evento_id = :id ORDER BY apellidos ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $asistentes]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID de evento no proporcionado']);
}
?>