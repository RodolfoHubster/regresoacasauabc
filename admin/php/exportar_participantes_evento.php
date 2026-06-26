<?php
require_once __DIR__ . '/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

$eventoId = isset($_GET['evento_id']) ? intval($_GET['evento_id']) : 0;

if (!$eventoId) {
    header('Content-Type: text/html; charset=utf-8');
    echo "Error: ID de evento no proporcionado.";
    return;
}

try {
    // Obtener nombre del evento
    $stmtEvento = $conexion->prepare("SELECT nombre FROM evento WHERE id = :id");
    $stmtEvento->execute([':id' => $eventoId]);
    $evento = $stmtEvento->fetch(PDO::FETCH_ASSOC);
    $nombreEvento = $evento ? $evento['nombre'] : 'Evento';

    // Consulta de participantes del evento
    $sql = "SELECT r.nombre, r.apellidos, r.correo,
                   cp.nombre AS campus_nombre,
                   f.nombre  AS facultad_nombre,
                   cr.nombre AS carrera_nombre,
                   r.facultad_otra,
                   r.carrera_otra,
                   r.generacion,
                   r.tipo_asistente,
                   r.correo_enviado,
                   r.asistencia,
                   r.necesidad_movilidad,
                   r.necesidad_especificacion
            FROM registro_asistente r
            LEFT JOIN campus    cp ON r.campus_id   = cp.id
            LEFT JOIN facultad   f ON r.facultad_id  = f.id
            LEFT JOIN carrera   cr ON r.carrera_id   = cr.id
            WHERE r.evento_id = :id
            ORDER BY r.apellidos ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $eventoId]);
    $participantes   = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPersonas   = count($participantes);
    $totalAsistieron = count(array_filter($participantes, fn($r) => $r['asistencia'] == 1));

    // Nombre del archivo
    $nombreArchivo = 'Participantes_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nombreEvento) . '.xlsx';

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Participantes');
    $sheet->setShowGridlines(true);

    // ── Fila 1: nombre del evento | Registrados: N | Asistieron: N ──
    $resumen = 'Registrados: ' . $totalPersonas . '   |   Asistieron: ' . $totalAsistieron;

    $tituloStyle = [
        'font'      => ['bold' => true, 'size' => 12, 'name' => 'Arial', 'color' => ['argb' => 'FF1A6B2A']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->setCellValue('A1', 'Evento: ' . $nombreEvento);
    $sheet->getStyle('A1')->applyFromArray($tituloStyle);

    $resumenStyle = [
        'font'      => ['bold' => true, 'size' => 11, 'name' => 'Arial', 'color' => ['argb' => 'FF1A6B2A']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->setCellValue('B1', $resumen);
    $sheet->getStyle('B1:E1')->applyFromArray($resumenStyle);
    $sheet->mergeCells('B1:E1');
    $sheet->getRowDimension(1)->setRowHeight(24);

    // ── Fila 2: Encabezados (NUEVA COLUMNA K) ──
    $headers = ['Nombre', 'Apellidos', 'Correo', 'Campus', 'Facultad', 'Carrera', 'Generación', 'Tipo', 'QR Correo', 'Asistencia', '¿Necesidad Movilidad?'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '2', $header);
        $col++;
    }

    $headerStyle = [
        'font' => [
            'bold'  => true,
            'color' => ['argb' => Color::COLOR_WHITE],
            'size'  => 11,
            'name'  => 'Arial',
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical'   => Alignment::VERTICAL_CENTER,
            'wrapText'   => true,
        ],
        'fill' => [
            'fillType'   => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FF1A6B2A'],
        ],
    ];
    $sheet->getStyle('A2:K2')->applyFromArray($headerStyle);
    $sheet->getRowDimension(2)->setRowHeight(30);

    // ── Filas de datos ──
    $rowNum = 3;
    foreach ($participantes as $row) {
        $qrEstado        = ($row['correo_enviado'] == 1) ? 'Enviado'    : 'Pendiente';
        $asistenciaEstado = ($row['asistencia']    == 1) ? 'Asistió'    : 'Registrado';

        $textoNecesidad = 'No';
        if ($row['necesidad_movilidad'] == 1 || $row['necesidad_movilidad'] === 'Si' || $row['necesidad_movilidad'] === 'Sí') {
            $especificacion = !empty($row['necesidad_especificacion']) ? $row['necesidad_especificacion'] : 'Sí';
            $textoNecesidad = 'Sí. ' . $especificacion;
        }

        $facultad = !empty($row['facultad_nombre']) ? $row['facultad_nombre'] : (!empty($row['facultad_otra']) ? $row['facultad_otra'] : 'N/A');
        $carrera = !empty($row['carrera_nombre']) ? $row['carrera_nombre'] : (!empty($row['carrera_otra']) ? $row['carrera_otra'] : 'N/A');

        $sheet->setCellValue('A' . $rowNum, $row['nombre']);
        $sheet->setCellValue('B' . $rowNum, $row['apellidos']);
        $sheet->setCellValue('C' . $rowNum, $row['correo']);
        $sheet->setCellValue('D' . $rowNum, $row['campus_nombre']   ?? 'N/A');
        $sheet->setCellValue('E' . $rowNum, $facultad);
        $sheet->setCellValue('F' . $rowNum, $carrera);
        $sheet->setCellValue('G' . $rowNum, $row['generacion']      ?? 'N/A');
        $sheet->setCellValue('H' . $rowNum, $row['tipo_asistente']  ?? 'N/A');
        $sheet->setCellValue('I' . $rowNum, $qrEstado);
        $sheet->setCellValue('J' . $rowNum, $asistenciaEstado);
        $sheet->setCellValue('K' . $rowNum, $textoNecesidad);

        $rowNum++;
    }

    $lastRow = $rowNum - 1;

    if ($lastRow >= 2) {
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFE0E0E0'],
                ],
            ],
        ];
        $sheet->getStyle('A2:K' . $lastRow)->applyFromArray($borderStyle);

        for ($i = 3; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22);
            $sheet->getStyle('D' . $i . ':H' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $i . ':K' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if ($i % 2 === 0) {
                $sheet->getStyle('A' . $i . ':K' . $i)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF4F9F5');
            }
        }

        foreach (range('A', 'K') as $columnId) {
            $sheet->getColumnDimension($columnId)->setAutoSize(true);
        }

        $sheet->setAutoFilter('A2:K' . $lastRow);
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    return;

} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo "Error al generar el reporte Excel: " . htmlspecialchars($e->getMessage());
    return;
}
?>