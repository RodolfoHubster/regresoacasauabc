<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

try {
    $stmt = $conexion->query("SELECT id, nombre, correo FROM usuario ORDER BY id DESC");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $usuarios]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>