<?php
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

try {
    $campus = isset($_GET['campus']) ? $_GET['campus'] : '';
    $whereRegistro = "";
    $whereEvento = "";
    $paramsRegistro = [];
    $paramsEvento = [];

    if ($campus !== '') {
        $whereRegistro = " JOIN campus c ON r.campus_id = c.id WHERE c.nombre = :campus ";
        $whereEvento = " JOIN campus c ON e.campus_id = c.id WHERE c.nombre = :campus ";
        $paramsRegistro = [':campus' => $campus];
        $paramsEvento = [':campus' => $campus];
    }

    // 1. Total de registros
    $stmt1 = $conexion->prepare("SELECT COUNT(*) as total FROM registro_asistente r" . $whereRegistro);
    $stmt1->execute($paramsRegistro);
    $totalRegistros = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. QR Confirmados
    $stmt2 = $conexion->prepare("SELECT COUNT(*) as confirmados FROM registro_asistente r " . ($campus !== '' ? $whereRegistro . " AND r.asistencia = 1" : " WHERE r.asistencia = 1"));
    $stmt2->execute($paramsRegistro);
    $qrConfirmados = $stmt2->fetch(PDO::FETCH_ASSOC)['confirmados'];

    // 3. Eventos Activos
    $stmt3 = $conexion->prepare("SELECT COUNT(*) as total_eventos FROM evento e" . $whereEvento);
    $stmt3->execute($paramsEvento);
    $eventosActivos = $stmt3->fetch(PDO::FETCH_ASSOC)['total_eventos'];

    // 4. Correos enviados
    $stmt4 = $conexion->prepare("SELECT COUNT(*) as enviados FROM registro_asistente r " . ($campus !== '' ? $whereRegistro . " AND r.correo_enviado = 1" : " WHERE r.correo_enviado = 1"));
    $stmt4->execute($paramsRegistro);
    $correosEnviados = $stmt4->fetch(PDO::FETCH_ASSOC)['enviados'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_registros'  => (int)$totalRegistros,
            'eventos_activos'  => (int)$eventosActivos,
            'qr_confirmados'   => (int)$qrConfirmados,
            'correos_enviados' => (int)$correosEnviados
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>