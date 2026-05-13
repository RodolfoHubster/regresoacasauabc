<?php
// Cargar el autoload de Composer desde la raíz
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../admin/php/conexion.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $evento_id = isset($_POST['evento_id']) ? trim($_POST['evento_id']) : '';
        $nombre    = isset($_POST['nombre'])    ? trim($_POST['nombre'])    : '';
        $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
        $correo    = isset($_POST['email'])     ? trim($_POST['email'])     : '';
        $telefono  = isset($_POST['telefono'])  ? trim($_POST['telefono'])  : null;
        $campus_id    = isset($_POST['campus'])   ? trim($_POST['campus'])   : '';
        $facultad_id  = isset($_POST['facultad']) ? trim($_POST['facultad']) : '';
        $carrera_id   = isset($_POST['carrera'])  ? trim($_POST['carrera'])  : '';
        $generacion   = isset($_POST['generacion']) ? trim($_POST['generacion']) : '';
        $tipo_asistente = isset($_POST['tipo'])   ? trim($_POST['tipo'])     : '';

        // Validaciones con mensajes específicos
        if (empty($evento_id)) {
            throw new Exception('No se recibió el ID del evento. Vuelve a abrir el formulario desde el botón "Registrarme" del evento.');
        }
        if (empty($nombre) || empty($apellidos)) {
            throw new Exception('El nombre y apellidos son obligatorios.');
        }
        if (empty($correo)) {
            throw new Exception('El correo electrónico es obligatorio.');
        }
        if (empty($campus_id)) {
            throw new Exception('Debes seleccionar tu campus de egreso.');
        }

        // Verificar que el evento existe en la BD
        $stmtEvento = $conexion->prepare("SELECT id, nombre FROM evento WHERE id = :id LIMIT 1");
        $stmtEvento->execute([':id' => $evento_id]);
        $eventoData = $stmtEvento->fetch(PDO::FETCH_ASSOC);
        if (!$eventoData) {
            throw new Exception('El evento seleccionado no existe o ya no está disponible.');
        }

        // Generar un código único de QR
        $qr_codigo = 'UABC-' . strtoupper(uniqid());

        // Guardar en la base de datos
        $sql = "INSERT INTO registro_asistente 
                (evento_id, qr_codigo, nombre, apellidos, correo, telefono, campus_id, facultad_id, carrera_id, generacion, tipo_asistente) 
                VALUES (:evento_id, :qr_codigo, :nombre, :apellidos, :correo, :telefono, :campus_id, :facultad_id, :carrera_id, :generacion, :tipo_asistente)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':evento_id'      => $evento_id,
            ':qr_codigo'      => $qr_codigo,
            ':nombre'         => $nombre,
            ':apellidos'      => $apellidos,
            ':correo'         => $correo,
            ':telefono'       => $telefono ?: null,
            ':campus_id'      => $campus_id,
            ':facultad_id'    => $facultad_id ?: null,
            ':carrera_id'     => $carrera_id ?: null,
            ':generacion'     => $generacion,
            ':tipo_asistente' => $tipo_asistente
        ]);

        // Generar imagen del QR
        $qr = new QrCode($qr_codigo);
        $writer = new PngWriter();
        $result = $writer->write($qr);
        
        $qr_path = sys_get_temp_dir() . '/' . $qr_codigo . '.png';
        $result->saveToFile($qr_path);

        // Enviar correo con PHPMailer
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USER'];
        $mail->Password   = $_ENV['MAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $_ENV['MAIL_PORT'];
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($_ENV['MAIL_USER'], 'Regreso a Casa UABC');
        $mail->addAddress($correo, $nombre . ' ' . $apellidos);
        $mail->addAttachment($qr_path, 'Tu_Codigo_QR.png');

        $nombreEvento = $eventoData['nombre'];

        $mail->isHTML(true);
        $mail->Subject = '¡Registro Confirmado! – ' . $nombreEvento;
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
                <div style='background:#002855; padding:20px; text-align:center;'>
                    <h1 style='color:#C8972B; margin:0;'>Regresa a Casa</h1>
                    <p style='color:#fff; margin:4px 0 0;'>Universidad Autónoma de Baja California</p>
                </div>
                <div style='padding:24px;'>
                    <h2 style='color:#002855;'>¡Hola, $nombre!</h2>
                    <p>Tu registro para el evento <strong>$nombreEvento</strong> fue exitoso.</p>
                    <p>Adjunto encontrarás tu <strong>Código QR de acceso</strong>. Preséntalo el día del evento desde tu celular, tablet o impreso.</p>
                    <p style='background:#f5f5f5; padding:12px; border-radius:6px; font-family:monospace;'>Código: <strong>$qr_codigo</strong></p>
                    <p>¡Te esperamos!</p>
                </div>
                <div style='background:#002855; padding:12px; text-align:center;'>
                    <p style='color:#C8972B; margin:0; font-size:12px;'>© UABC · Dirección de Egresados</p>
                </div>
            </div>
        ";

        $mail->send();

        // Marcar correo como enviado
        $stmtUpdate = $conexion->prepare("UPDATE registro_asistente SET correo_enviado = 1 WHERE qr_codigo = :qr");
        $stmtUpdate->execute([':qr' => $qr_codigo]);
        
        if (file_exists($qr_path)) unlink($qr_path);

        echo json_encode(['status' => 'success', 'message' => '¡Registro guardado y correo enviado!']);

    } catch (Exception $e) {
        error_log('[procesar_registro] ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
