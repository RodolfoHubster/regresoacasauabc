<?php
// Dar 30 minutos a PHP, enviar correos masivos con adjuntos toma tiempo
set_time_limit(1800); 

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
    $evento_id = $_POST['evento_id'] ?? '';
    
    if (empty($evento_id)) {
        echo json_encode(['status' => 'error', 'message' => 'No se especificó el evento.']);
        exit;
    }
    
    try {
        // --- 1. OBTENER DATOS DEL EVENTO PARA LA PLANTILLA ---
        $sql_evento = "SELECT e.*, c.nombre as campus_nombre 
                       FROM evento e 
                       LEFT JOIN campus c ON e.campus_id = c.id 
                       WHERE e.id = :evento_id";
        $stmt_evt = $conexion->prepare($sql_evento);
        $stmt_evt->execute([':evento_id' => $evento_id]);
        $evento_data = $stmt_evt->fetch(PDO::FETCH_ASSOC);

        if (!$evento_data) {
            throw new Exception("El evento no existe.");
        }

        // Formatear fechas para el diseño elegante
        $meses = ['01'=>'enero', '02'=>'febrero', '03'=>'marzo', '04'=>'abril', '05'=>'mayo', '06'=>'junio', '07'=>'julio', '08'=>'agosto', '09'=>'septiembre', '10'=>'octubre', '11'=>'noviembre', '12'=>'diciembre'];
        $fecha_parts = explode('-', $evento_data['fecha']);
        $fecha_formateada = ltrim($fecha_parts[2], '0') . ' de ' . $meses[$fecha_parts[1]] . ' de ' . $fecha_parts[0];
        $hora_formateada = date("g:i A", strtotime($evento_data['hora']));

        // --- 2. FILTRAR ASISTENTES SOLO DE ESTE EVENTO ---
        if ($tipo === 'sin-qr') {
            $sql = "SELECT * FROM registro_asistente WHERE evento_id = :evento_id AND correo_enviado = 0";
        } else {
            $sql = "SELECT * FROM registro_asistente WHERE evento_id = :evento_id AND asistencia = 0";
        }

        $stmt = $conexion->prepare($sql);
        $stmt->execute([':evento_id' => $evento_id]);
        $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($asistentes) === 0) {
            echo json_encode(['status' => 'success', 'enviados' => 0, 'message' => 'No hay asistentes pendientes para este evento.']);
            exit;
        }

        // --- 3. PREPARAR GMAIL API ---
        $client = new Client();
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
        $correo_remitente = 'egresados@uabc.edu.mx';
        $asunto = 'Recordatorio: ' . $evento_data['nombre'] . ' - Campus ' . $evento_data['campus_nombre'];

        $correosEnviadosExitosamente = 0;
        $writer = new PngWriter();
        $logo_path = __DIR__ . '/../../assets/images/LogoUabc.png';
        $logoData = base64_encode(file_get_contents($logo_path));

        // --- 4. BUCLE DE ENVÍO DE CORREOS ---
        foreach ($asistentes as $persona) {
            try {
                // Validación de QR
                $qr_codigo = $persona['qr_codigo'];
                if (empty($qr_codigo)) {
                    $qr_codigo = 'UABC-' . strtoupper(uniqid());
                    $conexion->query("UPDATE registro_asistente SET qr_codigo = '$qr_codigo' WHERE id = " . $persona['id']);
                }

                $qr = new QrCode($qr_codigo);
                $result = $writer->write($qr);
                $qr_path = sys_get_temp_dir() . '/' . $qr_codigo . '.png';
                $result->saveToFile($qr_path);

                $nombre_completo = mb_strtoupper($persona['nombre'] . ' ' . $persona['apellidos'], 'UTF-8');

                // Si escribieron un mensaje personalizado en el modal, lo inyectamos aquí
                $mensajeInyectado = "";
                if (!empty($mensajePersonalizado)) {
                    $mensajeInyectado = '<tr>
                                            <td align="center" style="padding: 0 40px 20px 40px;">
                                                <p style="background-color: #f1f8f5; border-left: 4px solid #00723F; padding: 15px; margin: 0; font-size: 15px; color: #333; text-align: left;">
                                                    ' . nl2br(htmlspecialchars($mensajePersonalizado)) . '
                                                </p>
                                            </td>
                                         </tr>';
                }

                // --- PLANTILLA HTML ELEGANTE ---
                $cuerpo_html = '
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <title>Recordatorio UABC</title>
                </head>
                <body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif;">
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f4f4f4; padding: 20px;">
                        <tr>
                            <td align="center">
                                <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                    <tr>
                                        <td align="center" style="background-color: #00723F; padding: 30px 20px; color: #ffffff;">
                                            <img src="cid:logo_uabc" alt="Logo UABC" width="80" style="display: block; margin-bottom: 15px;">
                                            <h2 style="margin: 0; font-size: 20px; font-weight: normal;">Universidad Autónoma de Baja California</h2>
                                            <p style="margin: 5px 0 0 0; color: #F2A900; font-size: 16px; font-weight: bold;">Recordatorio de Evento</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" style="padding: 30px 40px;">
                                            <p style="margin: 0; font-size: 18px; color: #333333;">¡Hola,</p>
                                            <h1 style="margin: 5px 0 20px 0; font-size: 22px; color: #00723F;">' . $nombre_completo . '!</h1>
                                            <p style="margin: 0; font-size: 16px; color: #555555;">Te recordamos que tu registro para</p>
                                            <h2 style="margin: 5px 0 20px 0; font-size: 20px; color: #333333;">' . $evento_data['nombre'] . '</h2>
                                            <p style="margin: 0; font-size: 16px; color: #555555;">está confirmado. ¡El evento ya casi llega!</p>
                                        </td>
                                    </tr>
                                    ' . $mensajeInyectado . '
                                    <tr>
                                        <td align="center" style="padding: 0 40px 30px 40px;">
                                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9f9f9; border-radius: 8px; padding: 20px; text-align: left;">
                                                <tr>
                                                    <td style="padding-bottom: 15px;">
                                                        <p style="margin: 0; font-size: 13px; color: #888888; text-transform: uppercase;">📅 Fecha y hora</p>
                                                        <p style="margin: 5px 0 0 0; font-size: 16px; color: #333333; font-weight: bold;">' . $fecha_formateada . ' | ' . $hora_formateada . '</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-bottom: 15px;">
                                                        <p style="margin: 0; font-size: 13px; color: #888888; text-transform: uppercase;">📍 Ciudad</p>
                                                        <p style="margin: 5px 0 0 0; font-size: 16px; color: #333333; font-weight: bold;">' . $evento_data['campus_nombre'] . '</p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <p style="margin: 0; font-size: 13px; color: #888888; text-transform: uppercase;">📌 Lugar</p>
                                                        <p style="margin: 5px 0 0 0; font-size: 16px; color: #333333;">' . $evento_data['ubicacion'] . '</p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="center" style="padding: 0 40px 30px 40px;">
                                            <p style="margin: 0 0 15px 0; font-size: 14px; color: #555555;">Presenta este código QR desde tu celular al llegar al evento:</p>
                                            <img src="cid:qr_img" alt="QR" width="200" height="200" style="display: block; border: 4px solid #00723F; border-radius: 8px;">
                                            <p style="margin: 10px 0 0 0; font-size: 12px; color: #999999;">Folio: ' . $qr_codigo . '</p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </body>
                </html>
                ';

                // --- CONSTRUIR EL MENSAJE MIME (Multipart/Related) ---
                $boundary = uniqid('np');
                $qrData = base64_encode(file_get_contents($qr_path));
                $correo_destino = $persona['correo'];

                $rawMessage = "MIME-Version: 1.0\r\n";
                $rawMessage .= "From: Regreso a Casa UABC <$correo_remitente>\r\n";
                $rawMessage .= "To: $nombre_completo <$correo_destino>\r\n";
                $rawMessage .= "Subject: =?utf-8?B?" . base64_encode($asunto) . "?=\r\n";
                $rawMessage .= "Content-Type: multipart/related; boundary=\"$boundary\"\r\n\r\n";

                $rawMessage .= "--$boundary\r\n";
                $rawMessage .= "Content-Type: text/html; charset=UTF-8\r\n";
                $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $rawMessage .= base64_encode($cuerpo_html) . "\r\n\r\n";

                $rawMessage .= "--$boundary\r\n";
                $rawMessage .= "Content-Type: image/png; name=\"Tu_Codigo_QR.png\"\r\n";
                $rawMessage .= "Content-Disposition: inline; filename=\"Tu_Codigo_QR.png\"\r\n";
                $rawMessage .= "Content-Transfer-Encoding: base64\r\n";
                $rawMessage .= "Content-ID: <qr_img>\r\n\r\n";
                $rawMessage .= $qrData . "\r\n\r\n";

                $rawMessage .= "--$boundary\r\n";
                $rawMessage .= "Content-Type: image/png; name=\"LogoUabc.png\"\r\n";
                $rawMessage .= "Content-Disposition: inline; filename=\"LogoUabc.png\"\r\n";
                $rawMessage .= "Content-Transfer-Encoding: base64\r\n";
                $rawMessage .= "Content-ID: <logo_uabc>\r\n\r\n";
                $rawMessage .= $logoData . "\r\n\r\n";
                
                $rawMessage .= "--$boundary--";

                $encoded = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');
                $message = new Message();
                $message->setRaw($encoded);

                // Enviar
                $service->users_messages->send('me', $message);
                $conexion->query("UPDATE registro_asistente SET correo_enviado = 1 WHERE id = " . $persona['id']);
                $correosEnviadosExitosamente++;
                unlink($qr_path);

                // --- 🛡️ SISTEMA ANTI-SPAM (RATE LIMITING) 🛡️ ---
                // Pausa de 0.5 segundos (500,000 microsegundos) entre cada correo
                // Evita bloquear la API de Gmail por enviar demasiados correos en 1 segundo.
                usleep(500000); 

            } catch (Exception $e) {
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