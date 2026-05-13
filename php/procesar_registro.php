<?php
// Cargar el autoload de Composer desde la raíz
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../admin/php/conexion.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Juancarlos\Regresoacasauabc\Http\RequestValidator;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // --- NUEVO: Recibir el ID del evento ---
        $evento_id = $_POST['evento_id'] ?? ''; 
        
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $correo = $_POST['email'] ?? ''; 
        $telefono = $_POST['telefono'] ?? null;
        $campus_id = $_POST['campus'] ?? '';
        $facultad_id = $_POST['facultad'] ?? '';
        $carrera_id = $_POST['carrera'] ?? '';
        $generacion = $_POST['generacion'] ?? '';
        $tipo_asistente = $_POST['tipo'] ?? '';

        // Validar que el evento_id no esté vacío
        if (!RequestValidator::hasRequiredRegistrationFields([
            'nombre' => $nombre,
            'email' => $correo,
            'campus' => $campus_id,
            'evento_id' => $evento_id
        ])) {
            throw new Exception("Faltan campos obligatorios (incluyendo el ID del evento).");
        }

        // 1. Generar un código único
        $qr_codigo = 'UABC-' . strtoupper(uniqid());

        // 2. Guardar en la base de datos (Se añadió evento_id)
        $sql = "INSERT INTO registro_asistente 
                (evento_id, qr_codigo, nombre, apellidos, correo, telefono, campus_id, facultad_id, carrera_id, generacion, tipo_asistente) 
                VALUES (:evento_id, :qr_codigo, :nombre, :apellidos, :correo, :telefono, :campus_id, :facultad_id, :carrera_id, :generacion, :tipo_asistente)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':evento_id' => $evento_id, // Vinculamos el asistente al evento
            ':qr_codigo' => $qr_codigo,
            ':nombre' => $nombre,
            ':apellidos' => $apellidos,
            ':correo' => $correo,
            ':telefono' => $telefono,
            ':campus_id' => $campus_id,
            ':facultad_id' => $facultad_id,
            ':carrera_id' => $carrera_id,
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
        $mail->addAttachment($qr_path, 'Tu_Codigo_QR.png');

        $mail->isHTML(true);
        $mail->Subject = '¡Registro Confirmado! - Regresa a Casa UABC';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #00713d;'>¡Hola $nombre!</h2>
                <p>Tu registro para el evento ha sido exitoso.</p>
                <p>Adjunto encontrarás tu <strong>Código QR de acceso</strong>.</p>
                <p>Tu código manual: <strong>$qr_codigo</strong></p>
                <br>
                <p>¡Te esperamos!</p>
            </div>
        ";

        $mail->send();

        // Actualizamos estado de envío
        $conexion->query("UPDATE registro_asistente SET correo_enviado = 1 WHERE qr_codigo = '$qr_codigo'");
        unlink($qr_path);

        echo json_encode(['status' => 'success', 'message' => '¡Registro guardado y correo enviado!']);

    } catch (Exception $e) {
        error_log("Error de registro/correo: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
