<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? '';
        if (empty($id)) throw new Exception("ID no proporcionado.");
        
        // Seguro: Evitar que el admin activo se elimine a sí mismo
        if ($id == $_SESSION['admin_id']) {
            throw new Exception("Por seguridad, no puedes eliminar tu propia cuenta mientras estás conectado.");
        }

        $stmt = $conexion->prepare("DELETE FROM usuario WHERE id = :id");
        $stmt->execute([':id' => $id]);

        echo json_encode(['status' => 'success', 'message' => 'Usuario eliminado correctamente.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>