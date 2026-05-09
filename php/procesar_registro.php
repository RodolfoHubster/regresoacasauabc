<?php
require_once __DIR__ . '/../admin/php/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtenemos los valores del formulario. El name="" del HTML es la llave aquí.
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $correo = $_POST['email'] ?? ''; 
        $telefono = $_POST['telefono'] ?? null;
        $campus_id = $_POST['campus'] ?? '';
        $facultad_id = $_POST['facultad'] ?? '';
        $carrera_id = $_POST['carrera'] ?? '';
        $generacion = $_POST['generacion'] ?? '';
        $tipo_asistente = $_POST['tipo'] ?? '';

        // Validación básica de seguridad en el backend
        if (empty($nombre) || empty($apellidos) || empty($correo) || empty($campus_id) || empty($facultad_id) || empty($carrera_id)) {
            throw new Exception("Faltan campos obligatorios por llenar.");
        }

        // Preparamos la consulta SQL
        $sql = "INSERT INTO registro_asistente 
                (nombre, apellidos, correo, telefono, campus_id, facultad_id, carrera_id, generacion, tipo_asistente) 
                VALUES (:nombre, :apellidos, :correo, :telefono, :campus_id, :facultad_id, :carrera_id, :generacion, :tipo_asistente)";
        
        $stmt = $conexion->prepare($sql);
        
        // Ejecutamos la consulta reemplazando las variables
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':correo' => $correo,
            ':telefono' => $telefono,
            ':campus_id' => $campus_id,
            ':facultad_id' => $facultad_id,
            ':carrera_id' => $carrera_id,
            ':generacion' => $generacion,
            ':tipo_asistente' => $tipo_asistente
        ]);

        // Si todo sale bien, respondemos con éxito
        echo json_encode(['status' => 'success', 'message' => '¡Registro guardado con éxito!']);

    } catch (PDOException $e) {
        // Error de base de datos (por ejemplo, si los IDs no coinciden)
        echo json_encode(['status' => 'error', 'message' => 'Error de Base de Datos: ' . $e->getMessage()]);
    } catch (Exception $e) {
        // Otros errores
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>