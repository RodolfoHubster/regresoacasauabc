<?php
// Dar tiempo extra a PHP porque enviar múltiples correos puede tardar varios segundos/minutos
set_time_limit(300); 

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/conexion.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? 'todos';
    $mensajePersonalizado = trim($_POST['mensaje'] ?? '');
    
    try {
        // 1. Decidir a quién enviarle el correo
        if ($tipo === 'sin-qr') {
            // Solo a los que no se les pudo enviar el correo la primera vez
            $sql = "SELECT * FROM registro_asistente WHERE correo_enviado = 0";
        } else {
            // A TODOS los que NO han asistido aún
            $sql = "SELECT * FROM registro_asistente WHERE asistencia = 0";
        }

        $stmt = $conexion->query($sql);
        $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($asistentes) === 0) {
            echo json_encode(['status' => 'success', 'enviados' => 0, 'message' => 'No hay asistentes que cumplan el criterio.']);
            exit;
        }

        // 2. Preparar el servidor de correos (Se conecta una sola vez para ser más rápido)
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
        
        // Mantener la conexión viva (SMTP KeepAlive) para envíos masivos
        $mail->SMTPKeepAlive = true; 

        $correosEnviadosExitosamente = 0;
        $writer = new PngWriter();

        // 3. Recorrer a cada asistente y mandarle su correo
        foreach ($asistentes as $persona) {
            try {
                // Generar el QR de esta persona
                $qr_codigo = $persona['qr_codigo'];
                if (empty($qr_codigo)) {
                    // Si por algún error antiguo no tiene código, se lo creamos
                    $qr_codigo = 'UABC-' . strtoupper(uniqid());
                    $conexion->query("UPDATE registro_asistente SET qr_codigo = '$qr_codigo' WHERE id = " . $persona['id']);
                }

                $qr = new QrCode($qr_codigo);
                $result = $writer->write($qr);
                $qr_path = sys_get_temp_dir() . '/' . $qr_codigo . '.png';
                $result->saveToFile($qr_path);

                // Configurar el correo para esta persona
                $mail->clearAddresses(); // Limpiar el destinatario anterior
                $mail->clearAttachments(); // Limpiar el QR anterior
                
                $mail->addAddress($persona['correo'], $persona['nombre']);
                $mail->addAttachment($qr_path, 'Tu_Acceso_QR.png');

                $mail->isHTML(true);
                $mail->Subject = 'Recordatorio: Regresa a Casa UABC';
                
                // Construir el cuerpo del correo
                $bodyHtml = "<div style='font-family: Arial, sans-serif; color: #333; max-width: 600px;'>";
                $bodyHtml .= "<h2 style='color: #00713d;'>¡Hola " . $persona['nombre'] . "!</h2>";
                
                if (!empty($mensajePersonalizado)) {
                    $bodyHtml .= "<p style='padding: 10px; background-color: #f1f8f5; border-left: 4px solid #00713d;'>" . nl2br(htmlspecialchars($mensajePersonalizado)) . "</p>";
                }
                
                $bodyHtml .= "<p>Este es un recordatorio de que tu registro sigue activo.</p>";
                $bodyHtml .= "<p>Adjunto a este correo encontrarás tu <strong>Código QR de acceso</strong>. Por favor, llévalo en tu celular para agilizar tu entrada.</p>";
                $bodyHtml .= "<p>Tu código manual es: <strong>$qr_codigo</strong></p><br><p>¡Te esperamos!</p></div>";

                $mail->Body = $bodyHtml;

                // Enviar
                $mail->send();
                
                // Marcar como enviado en la BD por si antes estaba en 0
                $conexion->query("UPDATE registro_asistente SET correo_enviado = 1 WHERE id = " . $persona['id']);
                $correosEnviadosExitosamente++;

                // Borrar imagen temporal
                unlink($qr_path);

            } catch (Exception $e) {
                error_log("Error enviando recordatorio a " . $persona['correo'] . ": " . $mail->ErrorInfo);
                continue; // Si falla uno, que siga con el siguiente
            }
        }
        
        $mail->smtpClose(); // Cerrar conexión SMTP

        echo json_encode(['status' => 'success', 'enviados' => $correosEnviadosExitosamente]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error en base de datos.']);
    }
}
?>