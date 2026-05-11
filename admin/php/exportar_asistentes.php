<?php
require_once __DIR__ . '/conexion.php';

// Leemos los filtros que nos mandará Javascript por la URL
$filtroEvento = $_GET['evento'] ?? '';
$filtroCampus = $_GET['campus'] ?? '';

// Nombramos el archivo dinámicamente según los filtros
$nombreArchivo = "Lista_Asistentes";
if (!empty($filtroEvento)) $nombreArchivo .= "_" . str_replace(' ', '', $filtroEvento);
if (!empty($filtroCampus)) $nombreArchivo .= "_" . $filtroCampus;
$nombreArchivo .= ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF"); // BOM para acentos

// Encabezados
fputcsv($output, ['Nombre', 'Apellidos', 'Correo', 'Campus', 'Carrera', 'Generacion', 'Tipo', 'Evento', 'QR Correo', 'Asistencia']);

try {
    // Consulta base
    $sql = "SELECT r.*, c.nombre as campus_nombre, ca.nombre as carrera_nombre, e.nombre as evento_nombre
            FROM registro_asistente r
            LEFT JOIN campus c ON r.campus_id = c.id
            LEFT JOIN carrera ca ON r.carrera_id = ca.id
            LEFT JOIN evento e ON r.campus_id = e.campus_id
            WHERE 1=1";
    
    $params = [];

    // Aplicar filtros a la consulta SQL
    if (!empty($filtroEvento)) {
        $sql .= " AND e.nombre = :evento";
        $params[':evento'] = $filtroEvento;
    }
    if (!empty($filtroCampus)) {
        $sql .= " AND c.nombre = :campus";
        $params[':campus'] = $filtroCampus;
    }
    
    $sql .= " ORDER BY r.id DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $qrEstado = ($row['correo_enviado'] == 1) ? 'Enviado' : 'Pendiente';
        $asistenciaEstado = ($row['asistencia'] == 1) ? 'Asistio' : 'Registrado';

        fputcsv($output, [
            $row['nombre'],
            $row['apellidos'],
            $row['correo'],
            $row['campus_nombre'] ?? 'N/A',
            $row['carrera_nombre'] ?? 'N/A',
            $row['generacion'] ?? 'N/A',
            $row['tipo_asistente'] ?? 'N/A',
            $row['evento_nombre'] ?? 'N/A',
            $qrEstado,
            $asistenciaEstado
        ]);
    }
} catch (Exception $e) {
    fputcsv($output, ['Error al generar el reporte', $e->getMessage()]);
}

fclose($output);
exit;
?>