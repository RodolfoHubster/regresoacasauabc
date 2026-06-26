<?php
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json');

try {
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $tipo_asistente = $_POST['tipo_asistente'] ?? 'egresado';
    $evento_id = $_POST['evento_id'] ?? null;
    $campus_id = !empty($_POST['campus_id']) ? $_POST['campus_id'] : null;
    $facultad_id = !empty($_POST['facultad_id']) ? $_POST['facultad_id'] : null;
    $carrera_id = !empty($_POST['carrera_id']) ? $_POST['carrera_id'] : null;
    $generacion = $_POST['generacion'] ?? '';
    $asistencia = isset($_POST['asistencia']) ? intval($_POST['asistencia']) : 0;

    if (empty($nombre) || empty($apellidos) || empty($correo) || empty($evento_id)) {
        throw new Exception("Faltan campos obligatorios.");
    }

    if (empty($id)) {
        // Insert
        $sql = "INSERT INTO registro_asistente 
                (nombre, apellidos, correo, tipo_asistente, evento_id, campus_id, facultad_id, carrera_id, generacion, asistencia, correo_enviado, necesidad_movilidad) 
                VALUES (:nombre, :apellidos, :correo, :tipo, :evento, :campus, :facultad, :carrera, :gen, :asis, 0, 'No')";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':correo' => $correo,
            ':tipo' => $tipo_asistente,
            ':evento' => $evento_id,
            ':campus' => $campus_id,
            ':facultad' => $facultad_id,
            ':carrera' => $carrera_id,
            ':gen' => $generacion,
            ':asis' => $asistencia
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Participante registrado exitosamente']);
    } else {
        // Update
        $sql = "UPDATE registro_asistente SET 
                nombre = :nombre, 
                apellidos = :apellidos, 
                correo = :correo, 
                tipo_asistente = :tipo, 
                evento_id = :evento, 
                campus_id = :campus, 
                facultad_id = :facultad, 
                carrera_id = :carrera, 
                generacion = :gen,
                asistencia = :asis
                WHERE id = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':correo' => $correo,
            ':tipo' => $tipo_asistente,
            ':evento' => $evento_id,
            ':campus' => $campus_id,
            ':facultad' => $facultad_id,
            ':carrera' => $carrera_id,
            ':gen' => $generacion,
            ':asis' => $asistencia,
            ':id' => $id
        ]);
        echo json_encode(['status' => 'success', 'message' => 'Participante actualizado exitosamente']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
