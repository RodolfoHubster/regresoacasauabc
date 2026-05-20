<?php
// Dar tiempo extra a PHP porque enviar múltiples correos puede tardar varios segundos/minutos
set_time_limit(300); 

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/conexion.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

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

        // 2. PREPARAR CLIENTE DE GOOGLE GMAIL API (Se conecta una sola vez)
        $client = new Client();
        // Las rutas tienen /../../ porque estamos dentro de la subcarpeta admin/php/
        $client->setAuthConfig(__DIR__ . '/../../credentials.json');
        $client->setScopes([Gmail::GMAIL_SEND]);
        $client->setAccessType('offline');

        $tokenData = json_decode(file_get_contents(__DIR__ . '/../../token.json'), true);
        $client->setAccessToken($tokenData);

        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents(__DIR__ . '/../../token.json', json_encode($client->getAccessToken()));
        }

        $service = new Gmail($client);
        $correo_remitente = 'egresados@uabc.edu.mx'; // Cambiar si es necesario
        $asunto = 'Recordatorio: Regresa a Casa UABC';

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

                // Construir el cuerpo del correo en HTML
                $bodyHtml = "<div style='font-family: Arial, sans-serif; color: #333; max-width: 600px;'>";
                $bodyHtml .= "<h2 style='color: #00713d;'>¡Hola " . $persona['nombre'] . "!</h2>";
                
                if (!empty($mensajePersonalizado)) {
                    $bodyHtml .= "<p style='padding: 10px; background-color: #f1f8f5; border-left: 4px solid #00713d;'>" . nl2br(htmlspecialchars($mensajePersonalizado)) . "</p>";
                }
                
                $bodyHtml .= "<p>Este es un recordatorio de que tu registro sigue activo.</p>";
                $bodyHtml .= "<p>Adjunto a este correo encontrarás tu <strong>Código QR de acceso</strong>. Por favor, llévalo en tu celular para agilizar tu entrada.</p>";
                $bodyHtml .= "<p>Tu código manual es: <strong>$qr_codigo</strong></p><br><p>¡Te esperamos!</p></div>";

                // --- CONSTRUIR EL MENSAJE MIME MULTIPART/MIXED (Para adjuntos normales) ---
                $boundary = uniqid('np');
                $qrData = base64_encode(file_get_contents($qr_path));
                $correo_destino = $persona['correo'];
                $nombre_destino = $persona['nombre'];

                $rawMessage = "MIME-Version: 1.0\r\n";
                $rawMessage .= "From: Regreso a Casa UABC <$correo_remitente>\r\n";
                $rawMessage .= "To: $nombre_destino <$correo_destino>\r\n";
                $rawMessage .= "Subject: =?utf-8?B?" . base64_encode($asunto) . "?=\r\n";
                $rawMessage .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n\r\n";

                // Parte HTML
                $rawMessage .= "--$boundary\r\n";
                $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n";
                $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $rawMessage .= base64_encode($bodyHtml) . "\r\n\r\n";

                // Parte Archivo Adjunto (QR)
                $rawMessage .= "--$boundary\r\n";
                $rawMessage .= "Content-Type: image/png; name=\"Tu_Acceso_QR.png\"\r\n";
                $rawMessage .= "Content-Disposition: attachment; filename=\"Tu_Acceso_QR.png\"\r\n";
                $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $rawMessage .= $qrData . "\r\n\r\n";
                
                $rawMessage .= "--$boundary--";

                // Codificar para la API de Google
                $encoded = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');

                $message = new Message();
                $message->setRaw($encoded);

                // Enviar mediante la API
                $service->users_messages->send('me', $message);
                
                // Marcar como enviado en la BD
                $conexion->query("UPDATE registro_asistente SET correo_enviado = 1 WHERE id = " . $persona['id']);
                $correosEnviadosExitosamente++;

                // Borrar imagen temporal
                unlink($qr_path);

            } catch (Exception $e) {
                // Si falla el envío de uno, guardamos el error en el log pero continuamos con el ciclo
                error_log("Error enviando recordatorio a " . $persona['correo'] . " (Google API): " . $e->getMessage());
                continue; 
            }
        }
        
        echo json_encode(['status' => 'success', 'enviados' => $correosEnviadosExitosamente]);

    } catch (Exception $e) {
        error_log("Error general en recordatorios: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Error al procesar la solicitud de recordatorios.']);
    }
}
?>