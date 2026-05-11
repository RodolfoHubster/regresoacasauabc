<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Usamos trim para eliminar espacios accidentales
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($correo) || empty($password)) {
        header("Location: ../login.html?error=empty");
        exit();
    }

    try {
        $query = "SELECT * FROM usuario WHERE correo = :correo";
        $stmt = $conexion->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificamos el hash
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logeado'] = true;  
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_nombre'] = $user['nombre'];
            $_SESSION['ultimo_acceso'] = time(); // Guardamos la hora actual en segundos

            // REDIRECCIÓN CORRECTA:
            // Sube un nivel (sale de php/) y busca admin.php
            header("Location: ../admin.php");
            exit();
        } else {
            // Si falla, regresa al login
            header("Location: ../login.html?error=1");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error de login: " . $e->getMessage());
        header("Location: ../login.html?error=system");
        exit();
    }
}
?>