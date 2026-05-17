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
        $evento_id = $_POST['evento_id'] ?? ''; 
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $correo = $_POST['email'] ?? ''; 
        $telefono = $_POST['telefono'] ?? null;
        $campus_id = $_POST['campus'] ?? '';
        $facultad_id = $_POST['facultad'] ?? '';
        $carrera_id = $_POST['carrera'] ?? '';
        $carrera_input = $_POST['carrera'] ?? '';
        $carrera_id = ($carrera_input === 'otra' || empty($carrera_input)) ? null : $carrera_input;
        $facultad_otra = null;
        $carrera_otra = $_POST['carrera_otra'] ?? null;
        $generacion = $_POST['generacion'] ?? '';
        $tipo_asistente = $_POST['tipo'] ?? 'egresado';

        // Validar que el evento_id no esté vacío
        if (empty($nombre) || empty($correo) || empty($campus_id) || empty($evento_id)) {
            throw new Exception("Faltan campos obligatorios (incluyendo el ID del evento).");
        }

        // --- NUEVO: OBTENER DATOS DEL EVENTO PARA EL CORREO ---
        $sql_evento = "SELECT e.*, c.nombre as campus_nombre 
                       FROM evento e 
                       LEFT JOIN campus c ON e.campus_id = c.id 
                       WHERE e.id = :evento_id";
        $stmt_evt = $conexion->prepare($sql_evento);
        $stmt_evt->execute([':evento_id' => $evento_id]);
        $evento_data = $stmt_evt->fetch(PDO::FETCH_ASSOC);

        if (!$evento_data) {
            throw new Exception("El evento seleccionado no existe.");
        }

        // Formatear Fecha (Ej. 16 de junio de 2026) y Hora (Ej. 6:00 PM)
        $meses = ['01'=>'enero', '02'=>'febrero', '03'=>'marzo', '04'=>'abril', '05'=>'mayo', '06'=>'junio', '07'=>'julio', '08'=>'agosto', '09'=>'septiembre', '10'=>'octubre', '11'=>'noviembre', '12'=>'diciembre'];
        $fecha_parts = explode('-', $evento_data['fecha']);
        $fecha_formateada = ltrim($fecha_parts[2], '0') . ' de ' . $meses[$fecha_parts[1]] . ' de ' . $fecha_parts[0];
        $hora_formateada = date("g:i A", strtotime($evento_data['hora']));
        
        $nombre_completo = mb_strtoupper($nombre . ' ' . $apellidos, 'UTF-8');
        
        // 1. Generar un código único
        $qr_codigo = 'UABC-' . strtoupper(uniqid());

        // 2. Guardar en la base de datos
        $sql = "INSERT INTO registro_asistente 
                (evento_id, qr_codigo, nombre, apellidos, correo, telefono, campus_id, facultad_id, facultad_otra, carrera_id, carrera_otra, generacion, tipo_asistente) 
                VALUES (:evento_id, :qr_codigo, :nombre, :apellidos, :correo, :telefono, :campus_id, :facultad_id, :facultad_otra, :carrera_id, :carrera_otra, :generacion, :tipo_asistente)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':evento_id' => $evento_id,
            ':qr_codigo' => $qr_codigo,
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':correo' => $correo,
            ':telefono' => $telefono,
            ':campus_id' => $campus_id,
            ':facultad_id' => $facultad_id,
            ':facultad_otra' => $facultad_otra, // NUEVA VARIABLE
            ':carrera_id' => $carrera_id,
            ':carrera_otra' => $carrera_otra, // NUEVA VARIABLE
            ':generacion' => $generacion,
            ':tipo_asistente' => $tipo_asistente
        ]);

        // 3. Generar la imagen del código QR
        $qr = new QrCode($qr_codigo);
        $writer = new PngWriter();
        $result = $writer->write($qr);
        
        $qr_path = sys_get_temp_dir() . '/' . $qr_codigo . '.png';
        $result->saveToFile($qr_path);

        // 4. Enviar el correo con PHPMailer
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
        
        // Incrustamos el QR generado en el cuerpo del correo (CID) y también lo dejamos como adjunto
        $mail->addEmbeddedImage($qr_path, 'qr_img', 'Tu_Codigo_QR.png');

        $mail->isHTML(true);
        
        // --- ASUNTO DINÁMICO ---
        $mail->Subject = 'Boleto para ' . $evento_data['nombre'] . ' - Reencuentro de egresadas y egresados de UABC, Campus ' . $evento_data['campus_nombre'];
        
        // --- CUERPO DEL CORREO HTML ---
        $mail->Body = '
        <!DOCTYPE html>
        <html lang="es" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="color-scheme" content="light only">
            <meta name="supported-color-schemes" content="light only">
            <title>Tu Boleto UABC</title>
            <style>
                :root { color-scheme: light only; supported-color-schemes: light only; }
                
                /* Forzar estilos en clientes que ignoran las metas */
                @media (prefers-color-scheme: dark) {
                    .body-bg { background-color: #f4f4f4 !important; }
                    .content-bg { background-color: #ffffff !important; }
                    .header-bg { background-color: #00723F !important; background: linear-gradient(#00723F, #00723F) !important; color: #ffffff !important; }
                    .text-green { color: #00723F !important; }
                    .text-gold { color: #F2A900 !important; }
                    .text-dark { color: #333333 !important; }
                    .text-muted { color: #555555 !important; }
                    .card-bg { background-color: #f9f9f9 !important; }
                }
            </style>
        </head>
        <body class="body-bg" style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" class="body-bg" style="background-color: #f4f4f4; padding: 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" border="0" class="content-bg" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                            
                            <tr>
                                <td align="center" class="header-bg" style="background-color: #00723F; background: linear-gradient(#00723F, #00723F); padding: 30px 20px; color: #ffffff;">
                                    <img src="https://cimahub-fcitec.tij.uabc.mx/regresoacasauabc/assets/images/LogoUabc.png" alt="Logo UABC" width="80" style="display: block; margin-bottom: 15px;">
                                    <h2 style="margin: 0; font-size: 20px; font-weight: normal; color: #ffffff;">Universidad Autónoma de Baja California</h2>
                                    <p class="text-gold" style="margin: 5px 0 0 0; color: #F2A900; font-size: 16px; font-weight: bold;">Reencuentro de egresadas y egresados</p>
                                </td>
                            </tr>

                            <tr>
                                <td align="center" style="padding: 30px 40px;">
                                    <p class="text-dark" style="margin: 0; font-size: 18px; color: #333333;">¡Hola,</p>
                                    <h1 class="text-green" style="margin: 5px 0 20px 0; font-size: 22px; color: #00723F; text-transform: uppercase;">' . $nombre_completo . '!</h1>
                                    
                                    <p class="text-muted" style="margin: 0; font-size: 16px; color: #555555;">Tu registro para</p>
                                    <h2 class="text-dark" style="margin: 5px 0 20px 0; font-size: 20px; color: #333333;">' . $evento_data['nombre'] . '</h2>
                                    <p class="text-muted" style="margin: 0; font-size: 16px; color: #555555;">ha sido confirmado.</p>
                                </td>
                            </tr>

                            <tr>
                                <td align="center" style="padding: 0 40px 30px 40px;">
                                    <table width="100%" cellpadding="0" cellspacing="0" border="0" class="card-bg" style="background-color: #f9f9f9; border-radius: 8px; padding: 20px; text-align: left;">
                                        <tr>
                                            <td style="padding-bottom: 15px;">
                                                <p style="margin: 0; font-size: 13px; color: #888888; text-transform: uppercase;">📅 Fecha y hora</p>
                                                <p class="text-dark" style="margin: 5px 0 0 0; font-size: 16px; color: #333333; font-weight: bold;">' . $fecha_formateada . ' | ' . $hora_formateada . '</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 15px;">
                                                <p style="margin: 0; font-size: 13px; color: #888888; text-transform: uppercase;">📍 Ciudad</p>
                                                <p class="text-dark" style="margin: 5px 0 0 0; font-size: 16px; color: #333333; font-weight: bold;">' . $evento_data['campus_nombre'] . '</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 15px;">
                                                <p style="margin: 0; font-size: 13px; color: #888888; text-transform: uppercase;">🎓 Lugar</p>
                                                <p class="text-dark" style="margin: 5px 0 0 0; font-size: 16px; color: #333333;">' . $evento_data['ubicacion'] . '</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <p style="margin: 0; font-size: 13px; color: #888888; text-transform: uppercase;">🚗 Notas Adicionales</p>
                                                <p class="text-dark" style="margin: 5px 0 0 0; font-size: 14px; color: #333333;">' . nl2br($evento_data['descripcion']) . '</p>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="padding: 0 40px 30px 40px;">
                                    <p class="text-muted" style="margin: 0 0 15px 0; font-size: 14px; color: #555555;">Presenta este código QR desde tu celular al llegar al evento:</p>
                                    <img src="cid:qr_img" alt="Código QR de Acceso" width="200" height="200" style="display: block; border: 4px solid #00723F; border-radius: 8px;">
                                    <p style="margin: 10px 0 0 0; font-size: 12px; color: #999999;">Folio: ' . $qr_codigo . '</p>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" style="padding: 20px 40px 30px 40px; border-top: 1px solid #eeeeee;">
                                    <p class="text-green" style="margin: 0 0 15px 0; font-size: 16px; color: #00723F; font-weight: bold; font-style: italic;">
                                        "Vive nuevamente la experiencia Cimarrona,<br>reencuéntrate con tu generación y celebra el orgullo de ser UABC."
                                    </p>
                                    <p class="text-muted" style="margin: 0; font-size: 14px; color: #555555;">
                                        Coordinación General de Vinculación y Cooperación Académica
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <p style="text-align: center; font-size: 12px; color: #999999; margin-top: 20px;">
                            © ' . date('Y') . ' Universidad Autónoma de Baja California.<br>Este es un correo generado automáticamente, por favor no respondas a este mensaje.
                        </p>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';

        $mail->send();

        // Actualizamos estado de envío
        $conexion->query("UPDATE registro_asistente SET correo_enviado = 1 WHERE qr_codigo = '$qr_codigo'");
        unlink($qr_path); // Borramos el QR temporal

        echo json_encode(['status' => 'success', 'message' => '¡Registro guardado y correo enviado!']);

    } catch (Exception $e) {
        error_log("Error de registro/correo: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>