<?php
require_once __DIR__ . '/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

// Leemos los filtros que nos mandará Javascript por la URL
$filtroEvento   = $_GET['evento']   ?? '';
$filtroCampus   = $_GET['campus']   ?? '';
$filtroFacultad = $_GET['facultad'] ?? '';

// Nombramos el archivo dinámicamente según los filtros
$nombreArchivo = "Lista_Asistentes";
if (!empty($filtroEvento))   $nombreArchivo .= "_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $filtroEvento);
if (!empty($filtroCampus))   $nombreArchivo .= "_" . $filtroCampus;
if (!empty($filtroFacultad)) $nombreArchivo .= "_" . preg_replace('/[^A-Za-z0-9_\-]/', '_', $filtroFacultad);
$nombreArchivo .= ".xlsx";

try {
    // Consulta base (con facultad incluida)
    $sql = "SELECT r.*, c.nombre as campus_nombre, f.nombre as facultad_nombre, ca.nombre as carrera_nombre, e.nombre as evento_nombre
            FROM registro_asistente r
            LEFT JOIN campus    c  ON r.campus_id   = c.id
            LEFT JOIN facultad  f  ON r.facultad_id = f.id
            LEFT JOIN carrera   ca ON r.carrera_id  = ca.id
            LEFT JOIN evento    e  ON r.evento_id   = e.id
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
    if (!empty($filtroFacultad)) {
        $sql .= " AND f.nombre = :facultad";
        $params[':facultad'] = $filtroFacultad;
    }

    $sql .= " ORDER BY r.id DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPersonas   = count($datos);
    $totalAsistieron = count(array_filter($datos, fn($r) => $r['asistencia'] == 1));

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Asistentes');
    $sheet->setShowGridlines(true);

    // ── Fila 1: filtros aplicados | total registrados | total asistieron ──
    $descripcionFiltros = 'Todos los asistentes';
    $partesFiltro = [];
    if (!empty($filtroEvento))   $partesFiltro[] = 'Evento: ' . $filtroEvento;
    if (!empty($filtroCampus))   $partesFiltro[] = 'Campus: ' . $filtroCampus;
    if (!empty($filtroFacultad)) $partesFiltro[] = 'Facultad: ' . $filtroFacultad;
    if (!empty($partesFiltro))   $descripcionFiltros = implode(' | ', $partesFiltro);

    $resumen = 'Registrados: ' . $totalPersonas . '   |   Asistieron: ' . $totalAsistieron;

    $sheet->setCellValue('A1', $descripcionFiltros);
    $sheet->setCellValue('B1', $resumen);

    $tituloStyle = [
        'font'      => ['bold' => true, 'size' => 11, 'name' => 'Arial', 'color' => ['argb' => 'FF1A6B2A']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->getStyle('A1')->applyFromArray($tituloStyle);

    $resumenStyle = [
        'font'      => ['bold' => true, 'size' => 11, 'name' => 'Arial', 'color' => ['argb' => 'FF1A6B2A']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
    ];
    $sheet->getStyle('B1:E1')->applyFromArray($resumenStyle);
    $sheet->mergeCells('B1:E1');
    $sheet->getRowDimension(1)->setRowHeight(24);

    // ── Fila 2: Encabezados ──
    $headers = ['Nombre', 'Apellidos', 'Correo', 'Campus', 'Facultad', 'Carrera', 'Generación', 'Tipo', 'Evento', 'QR Correo', 'Asistencia'];
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
    foreach ($datos as $row) {
        $qrEstado        = ($row['correo_enviado'] == 1) ? 'Enviado'   : 'Pendiente';
        $asistenciaEstado = ($row['asistencia']   == 1) ? 'Asistió'   : 'Registrado';

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
        $sheet->setCellValue('I' . $rowNum, $row['evento_nombre']   ?? 'N/A');
        $sheet->setCellValue('J' . $rowNum, $qrEstado);
        $sheet->setCellValue('K' . $rowNum, $asistenciaEstado);

        $rowNum++;
    }

    $lastRow = $rowNum - 1;

    if ($lastRow >= 2) {
        // Bordes para todos los datos
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFE0E0E0'],
                ],
            ],
        ];
        $sheet->getStyle('A2:K' . $lastRow)->applyFromArray($borderStyle);

        // Formato de filas de datos
        for ($i = 3; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22);
            $sheet->getStyle('D' . $i . ':H' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $i . ':K' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Fila cebra
            if ($i % 2 === 0) {
                $sheet->getStyle('A' . $i . ':K' . $i)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF4F9F5');
            }
        }

        // Auto-ajuste de columnas
        foreach (range('A', 'K') as $columnId) {
            $sheet->getColumnDimension($columnId)->setAutoSize(true);
        }

        // Filtros automáticos
        $sheet->setAutoFilter('A2:K' . $lastRow);
    }

    // Cabeceras para forzar la descarga de .xlsx
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;

} catch (Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo "Error al generar el reporte Excel: " . htmlspecialchars($e->getMessage());
    exit;
}
?>