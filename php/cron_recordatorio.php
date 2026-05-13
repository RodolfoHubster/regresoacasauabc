<?php
/**
 * cron_recordatorio.php — Script de cron para recordatorios automáticos
 *
 * Ejecutar desde el servidor 1 vez al día (ej. 9:00 AM):
 *   0 9 * * * php /ruta/del/proyecto/php/cron_recordatorio.php >> /var/log/cron_recordatorio.log 2>&1
 *
 * Lógica:
 *   - Obtiene todos los eventos cuya fecha sea exactamente 2 días después de hoy.
 *   - Para cada evento con registros pendientes, envía el correo recordatorio
 *     con el QR adjunto usando ReminderScheduler::debeEnviarHoy().
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../admin/php/conexion.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Juancarlos\Regresoacasauabc\Email\ReminderScheduler;
use Juancarlos\Regresoacasauabc\QrCode\QrCodeValidator;

// Cargar variables de entorno (.env en la raíz)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$hoy = date('Y-m-d');
echo "[{$hoy}] Iniciando cron_recordatorio...\n";

try {
    // Obtener todos los eventos activos o próximos
    $stmtEventos = $conexion->query(
        "SELECT id, nombre, fecha FROM evento WHERE estado IN ('activo', 'proximo')"
    );
    $eventos = $stmtEventos->fetchAll(PDO::FETCH_ASSOC);

    $totalEnviados = 0;

    foreach ($eventos as $evento) {
        // Verificar con ReminderScheduler si hoy toca enviar el recordatorio
        if (!ReminderScheduler::debeEnviarHoy($evento['fecha'], $hoy)) {
            continue;
        }

        echo "  Evento '" . $evento['nombre'] . "' (" . $evento['fecha'] . ") → enviando recordatorios...\n";

        // Obtener asistentes registrados que aún no han recibido recordatorio y no han asistido
        $stmtAsistentes = $conexion->prepare(
            "SELECT r.*, c.campus_clave
             FROM registro_asistente r
             INNER JOIN evento e ON r.evento_id = e.id
             LEFT JOIN campus c ON r.campus_id = c.id
             WHERE r.evento_id = :evento_id
               AND r.asistencia = 0"
        );
        $stmtAsistentes->execute([':evento_id' => $evento['id']]);
        $asistentes = $stmtAsistentes->fetchAll(PDO::FETCH_ASSOC);

        if (count($asistentes) === 0) {
            echo "    Sin asistentes pendientes.\n";
            continue;
        }

        // Preparar mailer (una sola conexión SMTP para todos)
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

        $writer = new PngWriter();

        foreach ($asistentes as $persona) {
            try {
                $qr_codigo = $persona['qr_codigo'] ?? '';

                // Reparar QRs con formato incorrecto
                if (!QrCodeValidator::isValidCode($qr_codigo)) {
                    $campusClave = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $persona['campus_clave'] ?? 'TJ'), 0, 3));
                    if (strlen($campusClave) < 2) {
                        $campusClave = 'TJ';
                    }
                    do {
                        $secuencia = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
                        $qr_codigo = 'UABC-' . $campusClave . '-' . date('Y') . '-' . $secuencia;
                        $chk = $conexion->prepare("SELECT id FROM registro_asistente WHERE qr_codigo = :qr LIMIT 1");
                        $chk->execute([':qr' => $qr_codigo]);
                    } while ($chk->fetch());

                    $conexion->prepare("UPDATE registro_asistente SET qr_codigo = :qr WHERE id = :id")
                             ->execute([':qr' => $qr_codigo, ':id' => $persona['id']]);
                }

                // Generar imagen QR temporal
                $qr      = new QrCode($qr_codigo);
                $result  = $writer->write($qr);
                $qr_path = sys_get_temp_dir() . '/' . $qr_codigo . '.png';
                $result->saveToFile($qr_path);

                // Armar y enviar correo
                $mail->clearAddresses();
                $mail->clearAttachments();
                $mail->addAddress($persona['correo'], $persona['nombre']);
                $mail->addAttachment($qr_path, 'Tu_Acceso_QR.png');

                $mail->isHTML(true);
                $mail->Subject = '¡Tu evento es pasado mañana! – ' . $evento['nombre'];

                $nombreEvento = htmlspecialchars($evento['nombre']);
                $fechaEvento  = date('d/m/Y', strtotime($evento['fecha']));
                $nombreP      = htmlspecialchars($persona['nombre']);
                $qrHtml       = htmlspecialchars($qr_codigo);

                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
                        <div style='background:#002855; padding:20px; text-align:center;'>
                            <h1 style='color:#C8972B; margin:0;'>Regresa a Casa</h1>
                            <p style='color:#fff; margin:4px 0 0;'>Universidad Autónoma de Baja California</p>
                        </div>
                        <div style='padding:24px;'>
                            <h2 style='color:#002855;'>¡Hola, {$nombreP}!</h2>
                            <p>Te recordamos que el evento <strong>{$nombreEvento}</strong>
                            es el próximo <strong>{$fechaEvento}</strong>.</p>
                            <p>Adjunto encontrarás tu <strong>Código QR de acceso</strong>.
                            Llévalo en tu celular, tablet o impreso.</p>
                            <p style='background:#f5f5f5; padding:12px; border-radius:6px; font-family:monospace;'>
                                Código: <strong>{$qrHtml}</strong>
                            </p>
                            <p>¡Te esperamos!</p>
                        </div>
                        <div style='background:#002855; padding:12px; text-align:center;'>
                            <p style='color:#C8972B; margin:0; font-size:12px;'>© UABC · Dirección de Egresados</p>
                        </div>
                    </div>
                ";

                $mail->send();
                $totalEnviados++;

                echo "    ✓ " . $persona['correo'] . "\n";

                if (file_exists($qr_path)) {
                    unlink($qr_path);
                }

            } catch (Exception $e) {
                error_log('[cron_recordatorio] ' . $persona['correo'] . ': ' . $e->getMessage());
                echo "    ✗ Error con " . $persona['correo'] . ": " . $e->getMessage() . "\n";
                continue;
            }
        }

        $mail->smtpClose();
    }

    echo "[{$hoy}] Cron finalizado. Total enviados: {$totalEnviados}\n";

} catch (Exception $e) {
    error_log('[cron_recordatorio] Error general: ' . $e->getMessage());
    echo "[{$hoy}] Error general: " . $e->getMessage() . "\n";
    exit(1);
}
?>
