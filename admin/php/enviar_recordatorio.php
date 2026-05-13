<?php
set_time_limit(300);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/conexion.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Juancarlos\Regresoacasauabc\QrCode\QrCodeValidator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo               = $_POST['tipo']    ?? 'todos';
    $mensajePersonalizado = trim($_POST['mensaje'] ?? '');

    try {
        if ($tipo === 'sin-qr') {
            $sql = "SELECT * FROM registro_asistente WHERE correo_enviado = 0";
        } else {
            $sql = "SELECT * FROM registro_asistente WHERE asistencia = 0";
        }

        $stmt       = $conexion->query($sql);
        $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($asistentes) === 0) {
            echo json_encode(['status' => 'success', 'enviados' => 0, 'message' => 'No hay asistentes que cumplan el criterio.']);
            exit;
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host          = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth      = true;
        $mail->Username      = $_ENV['MAIL_USER'];
        $mail->Password      = $_ENV['MAIL_PASS'];
        $mail->SMTPSecure    = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port          = $_ENV['MAIL_PORT'];
        $mail->CharSet       = 'UTF-8';
        $mail->SMTPKeepAlive = true;
        $mail->setFrom($_ENV['MAIL_USER'], 'Regreso a Casa UABC');

        $correosEnviadosExitosamente = 0;
        $writer = new PngWriter();

        foreach ($asistentes as $persona) {
            try {
                $qr_codigo = $persona['qr_codigo'];

                // Reparar QRs huérfanos con el formato correcto
                if (empty($qr_codigo) || !QrCodeValidator::isValidCode($qr_codigo)) {
                    // Obtener clave del campus para armar el código
                    $stmtCampus = $conexion->prepare(
                        "SELECT campus_clave FROM evento e
                         INNER JOIN registro_asistente r ON r.evento_id = e.id
                         WHERE r.id = :id LIMIT 1"
                    );
                    $stmtCampus->execute([':id' => $persona['id']]);
                    $campusRow   = $stmtCampus->fetch(PDO::FETCH_ASSOC);
                    $campusClave = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $campusRow['campus_clave'] ?? 'TJ'), 0, 3));
                    if (strlen($campusClave) < 2) {
                        $campusClave = 'TJ';
                    }

                    do {
                        $secuencia = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                        $qr_codigo = 'UABC-' . $campusClave . '-' . date('Y') . '-' . $secuencia;
                        $checkStmt = $conexion->prepare("SELECT id FROM registro_asistente WHERE qr_codigo = :qr LIMIT 1");
                        $checkStmt->execute([':qr' => $qr_codigo]);
                    } while ($checkStmt->fetch());

                    $conexion->prepare("UPDATE registro_asistente SET qr_codigo = :qr WHERE id = :id")
                             ->execute([':qr' => $qr_codigo, ':id' => $persona['id']]);
                }

                $qr      = new QrCode($qr_codigo);
                $result  = $writer->write($qr);
                $qr_path = sys_get_temp_dir() . '/' . $qr_codigo . '.png';
                $result->saveToFile($qr_path);

                $mail->clearAddresses();
                $mail->clearAttachments();
                $mail->addAddress($persona['correo'], $persona['nombre']);
                $mail->addAttachment($qr_path, 'Tu_Acceso_QR.png');

                $mail->isHTML(true);
                $mail->Subject = 'Recordatorio: Regresa a Casa UABC';

                $bodyHtml  = "<div style='font-family: Arial, sans-serif; color: #333; max-width: 600px;'>";
                $bodyHtml .= "<div style='background:#002855; padding:16px; text-align:center;'>";
                $bodyHtml .= "<h1 style='color:#C8972B; margin:0;'>Regresa a Casa</h1>";
                $bodyHtml .= "<p style='color:#fff; margin:4px 0 0;'>Universidad Autónoma de Baja California</p></div>";
                $bodyHtml .= "<div style='padding:24px;'>";
                $bodyHtml .= "<h2 style='color:#002855;'>¡Hola, " . htmlspecialchars($persona['nombre']) . "!</h2>";

                if ($mensajePersonalizado !== '') {
                    $bodyHtml .= "<p style='padding:10px; background:#eef2f8; border-left:4px solid #002855;'>" . nl2br(htmlspecialchars($mensajePersonalizado)) . "</p>";
                }

                $bodyHtml .= "<p>Este es un recordatorio: tu registro está confirmado.</p>";
                $bodyHtml .= "<p>Adjunto encontrarás tu <strong>Código QR de acceso</strong>. Llévalo en tu celular o impreso.</p>";
                $bodyHtml .= "<p style='background:#f5f5f5; padding:10px; border-radius:4px; font-family:monospace;'>Código: <strong>" . htmlspecialchars($qr_codigo) . "</strong></p>";
                $bodyHtml .= "<p>¡Te esperamos!</p></div>";
                $bodyHtml .= "<div style='background:#002855; padding:10px; text-align:center;'>";
                $bodyHtml .= "<p style='color:#C8972B; margin:0; font-size:12px;'>© UABC · Dirección de Egresados</p></div></div>";

                $mail->Body = $bodyHtml;
                $mail->send();

                $conexion->prepare("UPDATE registro_asistente SET correo_enviado = 1 WHERE id = :id")
                         ->execute([':id' => $persona['id']]);

                $correosEnviadosExitosamente++;

                if (file_exists($qr_path)) {
                    unlink($qr_path);
                }

            } catch (Exception $e) {
                error_log('[enviar_recordatorio] ' . $persona['correo'] . ': ' . $e->getMessage());
                continue;
            }
        }

        $mail->smtpClose();

        echo json_encode(['status' => 'success', 'enviados' => $correosEnviadosExitosamente]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en base de datos.']);
    }
}
?>
