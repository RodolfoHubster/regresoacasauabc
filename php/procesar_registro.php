<?php
// Cargar el autoload de Composer desde la raíz
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../admin/php/conexion.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Juancarlos\Regresoacasauabc\Http\RequestValidator;
use Juancarlos\Regresoacasauabc\Http\TelefonoValidator;
use Juancarlos\Regresoacasauabc\Event\EventoStatusValidator;
use Juancarlos\Regresoacasauabc\QrCode\QrCodeValidator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $evento_id      = trim($_POST['evento_id']      ?? '');
        $nombre         = trim($_POST['nombre']         ?? '');
        $apellidos      = trim($_POST['apellidos']      ?? '');
        $correo         = trim($_POST['email']          ?? '');
        $telefono       = trim($_POST['telefono']       ?? '');
        $campus_id      = trim($_POST['campus']         ?? '');
        $facultad_id    = trim($_POST['facultad']       ?? '');
        $carrera_id     = trim($_POST['carrera']        ?? '');
        $generacion     = trim($_POST['generacion']     ?? '');
        $tipo_asistente = trim($_POST['tipo']           ?? '');

        // --- 1. Campos obligatorios completos ---
        if (!RequestValidator::hasRequiredRegistrationFields([
            'nombre'         => $nombre,
            'apellidos'      => $apellidos,
            'email'          => $correo,
            'campus'         => $campus_id,
            'facultad'       => $facultad_id,
            'carrera'        => $carrera_id,
            'generacion'     => $generacion,
            'tipo_asistente' => $tipo_asistente,
            'evento_id'      => $evento_id,
        ])) {
            throw new Exception('Faltan datos obligatorios. Revisa que todos los campos estén llenos.');
        }

        // --- 2. Formato de correo ---
        if (!RequestValidator::isValidEmail($correo)) {
            throw new Exception('El correo electrónico no tiene un formato válido.');
        }

        // --- 3. Generación (año numérico entre 1960 y el año actual) ---
        if (!RequestValidator::isValidGeneracion($generacion)) {
            throw new Exception('La generación debe ser un año entre 1960 y ' . date('Y') . '.');
        }

        // --- 4. Tipo de asistente permitido ---
        if (!RequestValidator::isValidTipoAsistente($tipo_asistente)) {
            throw new Exception('El tipo de asistente no es válido. Valores permitidos: egresado, docente, administrativo.');
        }

        // --- 5. Teléfono opcional, pero si viene debe tener formato correcto ---
        if (!TelefonoValidator::isValidOrEmpty($telefono)) {
            throw new Exception('El número de teléfono no tiene un formato válido (7–15 dígitos).');
        }

        // --- 6. Verificar que el evento existe y está activo ---
        $stmtEvento = $conexion->prepare(
            "SELECT id, nombre, fecha, estado, campus_clave FROM evento WHERE id = :id LIMIT 1"
        );
        $stmtEvento->execute([':id' => $evento_id]);
        $eventoData = $stmtEvento->fetch(PDO::FETCH_ASSOC);

        if (!$eventoData) {
            throw new Exception('El evento seleccionado no existe o ya no está disponible.');
        }

        if (!EventoStatusValidator::allowsRegistration($eventoData['estado'])) {
            throw new Exception('Este evento no acepta nuevos registros (estado: ' . $eventoData['estado'] . ').');
        }

        // --- 7. Generar código QR con formato UABC-{CAMPUS}-{AÑO}-{SEQ 5 dígitos} ---
        $campusClave = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $eventoData['campus_clave'] ?? 'TJ'), 0, 3));
        if (strlen($campusClave) < 2) {
            $campusClave = 'TJ';
        }
        $anio      = date('Y');
        $secuencia = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $qr_codigo = 'UABC-' . $campusClave . '-' . $anio . '-' . $secuencia;

        // Garantizar unicidad en BD
        $checkStmt = $conexion->prepare("SELECT id FROM registro_asistente WHERE qr_codigo = :qr LIMIT 1");
        $checkStmt->execute([':qr' => $qr_codigo]);
        while ($checkStmt->fetch()) {
            $secuencia = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $qr_codigo = 'UABC-' . $campusClave . '-' . $anio . '-' . $secuencia;
            $checkStmt->execute([':qr' => $qr_codigo]);
        }

        // Validar formato final con QrCodeValidator
        if (!QrCodeValidator::isValidCode($qr_codigo)) {
            throw new Exception('Error interno al generar el código QR. Intenta de nuevo.');
        }

        // --- 8. Guardar en la base de datos ---
        $sql = "INSERT INTO registro_asistente 
                (evento_id, qr_codigo, nombre, apellidos, correo, telefono,
                 campus_id, facultad_id, carrera_id, generacion, tipo_asistente) 
                VALUES (:evento_id, :qr_codigo, :nombre, :apellidos, :correo, :telefono,
                        :campus_id, :facultad_id, :carrera_id, :generacion, :tipo_asistente)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':evento_id'      => $evento_id,
            ':qr_codigo'      => $qr_codigo,
            ':nombre'         => $nombre,
            ':apellidos'      => $apellidos,
            ':correo'         => $correo,
            ':telefono'       => $telefono !== '' ? $telefono : null,
            ':campus_id'      => $campus_id,
            ':facultad_id'    => $facultad_id !== '' ? $facultad_id : null,
            ':carrera_id'     => $carrera_id !== '' ? $carrera_id : null,
            ':generacion'     => $generacion,
            ':tipo_asistente' => $tipo_asistente,
        ]);

        // --- 9. Generar imagen QR y enviar correo ---
        $qr     = new QrCode($qr_codigo);
        $writer = new PngWriter();
        $result = $writer->write($qr);

        $qr_path = sys_get_temp_dir() . '/' . $qr_codigo . '.png';
        $result->saveToFile($qr_path);

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

        $stmtUpdate = $conexion->prepare(
            "UPDATE registro_asistente SET correo_enviado = 1 WHERE qr_codigo = :qr"
        );
        $stmtUpdate->execute([':qr' => $qr_codigo]);

        if (file_exists($qr_path)) {
            unlink($qr_path);
        }

        echo json_encode(['status' => 'success', 'message' => '¡Registro guardado y correo enviado!']);

    } catch (Exception $e) {
        error_log('[procesar_registro] ' . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
