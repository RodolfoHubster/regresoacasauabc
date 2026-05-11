<?php
require_once __DIR__ . '/conexion.php';

// Configuramos las cabeceras para forzar la descarga como archivo CSV (Excel)
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="Registros_Por_Evento.csv"');

// Abrimos la salida de datos
$output = fopen('php://output', 'w');

// Agregamos el BOM de UTF-8 para que Excel lea perfectamente los acentos y las ñ
fputs($output, "\xEF\xBB\xBF");

// Escribimos los encabezados de las columnas
fputcsv($output, ['Evento', 'Campus', 'Fecha', 'Total de Registros', 'Estado']);

try {
    $sql = "SELECT 
                e.nombre, 
                c.nombre as campus, 
                e.fecha, 
                e.estado,
                (SELECT COUNT(*) FROM registro_asistente r WHERE r.campus_id = e.campus_id) as total_registros
            FROM evento e
            JOIN campus c ON e.campus_id = c.id
            ORDER BY e.fecha ASC";
            
    $stmt = $conexion->query($sql);

    // Recorremos los datos y escribimos fila por fila
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ponemos la primera letra del estado en mayúscula
        $estado = ucfirst($row['estado']); 
        
        fputcsv($output, [
            $row['nombre'], 
            $row['campus'], 
            $row['fecha'], 
            $row['total_registros'], 
            $estado
        ]);
    }
} catch (Exception $e) {
    fputcsv($output, ['Error al generar el reporte', $e->getMessage()]);
}

fclose($output);
exit;
?>