<?php
require_once 'auth_check.php';
require_once 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = $_POST['id'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($nombre) || empty($correo)) {
            throw new Exception("El nombre y el correo son obligatorios.");
        }

        if (empty($id)) {
            // ES UN NUEVO USUARIO
            if (empty($password)) throw new Exception("La contraseña es obligatoria para nuevos usuarios.");
            
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuario (nombre, correo, password) VALUES (:nombre, :correo, :password)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([':nombre' => $nombre, ':correo' => $correo, ':password' => $hash]);
            $msg = "Usuario creado exitosamente.";
        } else {
            // SE ESTÁ EDITANDO UN USUARIO
            if (!empty($password)) {
                // Si escribió una contraseña, la actualizamos
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuario SET nombre = :nombre, correo = :correo, password = :password WHERE id = :id";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([':nombre' => $nombre, ':correo' => $correo, ':password' => $hash, ':id' => $id]);
            } else {
                // Si la dejó en blanco, solo actualizamos nombre y correo
                $sql = "UPDATE usuario SET nombre = :nombre, correo = :correo WHERE id = :id";
                $stmt = $conexion->prepare($sql);
                $stmt->execute([':nombre' => $nombre, ':correo' => $correo, ':id' => $id]);
            }
            $msg = "Usuario actualizado exitosamente.";
        }

        echo json_encode(['status' => 'success', 'message' => $msg]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>