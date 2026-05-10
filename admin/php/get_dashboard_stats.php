<?php
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

try {
    // 1. Total de registros (Personas que se han registrado en el formulario)
    $stmt1 = $conexion->query("SELECT COUNT(*) as total FROM registro_asistente");
    $totalRegistros = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. QR Confirmados (Asistentes que ya tienen asistencia = 1)
    $stmt2 = $conexion->query("SELECT COUNT(*) as confirmados FROM registro_asistente WHERE asistencia = 1");
    $qrConfirmados = $stmt2->fetch(PDO::FETCH_ASSOC)['confirmados'];

    // 3. Eventos Activos (Cuenta real de la tabla 'evento')
    // Nota: Si en el futuro agregas una columna 'estado', puedes filtrar por WHERE estado = 'activo'
    $stmt3 = $conexion->query("SELECT COUNT(*) as total_eventos FROM evento");
    $eventosActivos = $stmt3->fetch(PDO::FETCH_ASSOC)['total_eventos'];

    // 4. Correos enviados (Basado en tu columna correo_enviado)
    $stmt4 = $conexion->query("SELECT COUNT(*) as enviados FROM registro_asistente WHERE correo_enviado = 1");
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