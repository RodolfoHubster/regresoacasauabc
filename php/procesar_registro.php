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
        $nombre = $_POST['nombre'] ?? '';
        $apellidos = $_POST['apellidos'] ?? '';
        $correo = $_POST['email'] ?? ''; 
        $telefono = $_POST['telefono'] ?? null;
        $campus_id = $_POST['campus'] ?? '';
        $facultad_id = $_POST['facultad'] ?? '';
        $carrera_id = $_POST['carrera'] ?? '';
        $generacion = $_POST['generacion'] ?? '';
        $tipo_asistente = $_POST['tipo'] ?? '';

        if (empty($nombre) || empty($correo) || empty($campus_id)) {
            throw new Exception("Faltan campos obligatorios.");
        }

        // 1. Generar un código único para este asistente (Ej. UABC-64A3F...)
        $qr_codigo = 'UABC-' . strtoupper(uniqid());

        // 2. Guardar en la base de datos
        $sql = "INSERT INTO registro_asistente 
                (qr_codigo, nombre, apellidos, correo, telefono, campus_id, facultad_id, carrera_id, generacion, tipo_asistente) 
                VALUES (:qr_codigo, :nombre, :apellidos, :correo, :telefono, :campus_id, :facultad_id, :carrera_id, :generacion, :tipo_asistente)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
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
        
        // Guardamos el QR temporalmente para adjuntarlo al correo
        $qr_path = sys_get_temp_dir() . '/' . $qr_codigo . '.png';
        $result->saveToFile($qr_path);

        // 4. Enviar el correo con PHPMailer
        $mail = new PHPMailer(true);
        
        // Configuración del servidor SMTP (leyendo del .env)
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USER'];
        $mail->Password   = $_ENV['MAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $_ENV['MAIL_PORT'];
        $mail->CharSet    = 'UTF-8';

        // Remitente y Destinatario
        $mail->setFrom($_ENV['MAIL_USER'], 'Regreso a Casa UABC');
        $mail->addAddress($correo, $nombre . ' ' . $apellidos);

        // Adjuntar el código QR
        $mail->addAttachment($qr_path, 'Tu_Codigo_QR.png');

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = '¡Registro Confirmado! - Regresa a Casa UABC';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #00713d;'>¡Hola $nombre!</h2>
                <p>Tu registro para el evento <strong>Regresa a Casa UABC</strong> ha sido exitoso.</p>
                <p>Adjunto a este correo encontrarás tu <strong>Código QR de acceso</strong>. Por favor, llévalo en tu celular o impreso el día del evento para agilizar tu entrada.</p>
                <p>Tu código de confirmación manual es: <strong>$qr_codigo</strong></p>
                <br>
                <p>¡Te esperamos!</p>
                <p><small>Dirección de Egresados UABC</small></p>
            </div>
        ";

        $mail->send();

        // Si se envió el correo, actualizamos la columna `correo_enviado` a 1
        $conexion->query("UPDATE registro_asistente SET correo_enviado = 1 WHERE qr_codigo = '$qr_codigo'");

        // Borramos el QR temporal del servidor para no ocupar espacio
        unlink($qr_path);

        echo json_encode(['status' => 'success', 'message' => '¡Registro guardado y correo enviado!']);

    } catch (Exception $e) {
        // Aunque falle el correo, el usuario ya se guardó, pero le avisamos del error
        error_log("Error de registro/correo: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Registro completado, pero ocurrió un error al enviar el correo.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>