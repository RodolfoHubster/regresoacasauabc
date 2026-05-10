<?php
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json');

try {
    // 1. Total de registros (Cuenta todas las filas de la tabla)
    $stmt1 = $conexion->query("SELECT COUNT(*) as total FROM registro_asistente");
    $totalRegistros = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Correos Enviados (Cuenta donde correo_enviado sea 1)
    $stmt2 = $conexion->query("SELECT COUNT(*) as enviados FROM registro_asistente WHERE correo_enviado = 1");
    $correosEnviados = $stmt2->fetch(PDO::FETCH_ASSOC)['enviados'];

    // 3. QR Confirmados / Asistencias (Cuenta donde asistencia sea 1)
    $stmt3 = $conexion->query("SELECT COUNT(*) as confirmados FROM registro_asistente WHERE asistencia = 1");
    $qrConfirmados = $stmt3->fetch(PDO::FETCH_ASSOC)['confirmados'];

    // 4. Eventos Activos (Si ya tienes una tabla `evento`, puedes descomentar la línea de abajo)
    // $stmt4 = $conexion->query("SELECT COUNT(*) as activos FROM evento WHERE estado = 'activo'");
    // $eventosActivos = $stmt4->fetch(PDO::FETCH_ASSOC)['activos'];
    
    // Por ahora lo dejaremos fijo en 3 basándonos en tu HTML (Tijuana, Mexicali, Ensenada)
    $eventosActivos = 3; 

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_registros'  => $totalRegistros,
            'eventos_activos'  => $eventosActivos,
            'qr_confirmados'   => $qrConfirmados,
            'correos_enviados' => $correosEnviados
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>