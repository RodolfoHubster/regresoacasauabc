<?php
require_once __DIR__ . '/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

// Nombramos el archivo
$nombreArchivo = "Registros_Por_Evento.xlsx";

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

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Eventos');

    // Habilitar líneas de cuadrícula
    $sheet->setShowGridlines(true);

    // Escribimos los encabezados de las columnas
    $headers = ['Evento', 'Campus', 'Fecha', 'Total de Registros', 'Estado'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '1', $header);
        $col++;
    }

    $rowNum = 2;
    // Recorremos los datos y escribimos fila por fila
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Ponemos la primera letra del estado en mayúscula
        $estado = ucfirst($row['estado']); 
        
        $sheet->setCellValue('A' . $rowNum, $row['nombre']);
        $sheet->setCellValue('B' . $rowNum, $row['campus']);
        $sheet->setCellValue('C' . $rowNum, $row['fecha']);
        $sheet->setCellValue('D' . $rowNum, $row['total_registros']);
        $sheet->setCellValue('E' . $rowNum, $estado);

        $rowNum++;
    }

    $lastRow = $rowNum - 1;

    // Si hay datos, aplicar formato premium
    if ($lastRow >= 1) {
        // Estilo de los encabezados (verde UABC #1A6B2A con texto blanco y negrita)
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
                'size' => 11,
                'name' => 'Arial'
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1A6B2A']
            ]
        ];
        $sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Bordes finos y alineación para los datos
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE0E0E0'],
                ],
            ],
        ];
        $sheet->getStyle('A1:E' . $lastRow)->applyFromArray($borderStyle);

        // Formato para filas de datos
        for ($i = 2; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22);
            // Centrar columnas específicas (Campus, Fecha, Total Registros, Estado)
            $sheet->getStyle('B' . $i . ':C' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $i . ':E' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Fila cebra: alternar color de fondo sutil
            if ($i % 2 === 0) {
                $sheet->getStyle('A' . $i . ':E' . $i)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF4F9F5'); // Verde ultra claro
            }
        }

        // Auto-ajustar ancho de columnas
        foreach (range('A', 'E') as $columnId) {
            $sheet->getColumnDimension($columnId)->setAutoSize(true);
        }

        // Filtros automáticos
        $sheet->setAutoFilter('A1:E' . $lastRow);
    }

    // Cabeceras para descarga XLSX
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