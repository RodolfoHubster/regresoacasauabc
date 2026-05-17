<?php
require_once 'conexion.php';

header('Content-Type: application/json');

// Función auxiliar para extraer el public_id de Cloudinary a partir de su URL segura
function obtenerCloudinaryPublicId($url) {
    if (empty($url) || strpos($url, 'cloudinary.com') === false) {
        return null;
    }
    
    // Extrae la ruta después de /upload/
    $parts = explode('/upload/', $url);
    if (count($parts) < 2) return null;
    
    $afterUpload = $parts[1];
    
    // Remueve la versión de la imagen si existe (ej: v1570975660/)
    $afterUpload = preg_replace('/^v\d+\//', '', $afterUpload);
    
    // Remueve la extensión del archivo (.jpg, .png, etc.)
    $filename = pathinfo($afterUpload, PATHINFO_FILENAME);
    
    // Si la imagen estaba guardada en subcarpetas de Cloudinary, las conserva
    $dirname = pathinfo($afterUpload, PATHINFO_DIRNAME);
    if ($dirname && $dirname !== '.') {
        return $dirname . '/' . $filename;
    }
    
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Leer la entrada JSON enviada por JavaScript
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (empty($id)) {
            echo json_encode(['status' => 'error', 'message' => 'Falta el ID del evento.']);
            exit;
        }

        // 1. Consultar la URL de la imagen actual antes de borrar el evento
        $sql_select = "SELECT imagen FROM evento WHERE id = :id";
        $stmt_select = $conexion->prepare($sql_select);
        $stmt_select->execute([':id' => $id]);
        $evento = $stmt_select->fetch(PDO::FETCH_ASSOC);

        if ($evento && !empty($evento['imagen'])) {
            $public_id = obtenerCloudinaryPublicId($evento['imagen']);
            
            // 2. Si es una imagen válida de Cloudinary, procedemos a destruirla con una petición firmada
            if ($public_id) {
                $cloud_name = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? '';
                $api_key = $_ENV['CLOUDINARY_API_KEY'] ?? '';
                $api_secret = $_ENV['CLOUDINARY_API_SECRET'] ?? '';
                
                if (!empty($cloud_name) && !empty($api_key) && !empty($api_secret)) {
                    $timestamp = time();
                    
                    // Generar la firma criptográfica SHA-1 requerida por Cloudinary
                    $string_to_sign = "public_id=$public_id&timestamp=$timestamp$api_secret";
                    $signature = sha1($string_to_sign);
                    
                    // Petición nativa vía cURL
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/destroy");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                        'public_id' => $public_id,
                        'timestamp' => $timestamp,
                        'api_key'   => $api_key,
                        'signature' => $signature
                    ]));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    
                    $response = curl_exec($ch);
                    curl_close($ch);
                    
                    // Opcional: puedes guardar un registro si la eliminación en la nube falló
                    // $res_data = json_decode($response, true);
                }
            }
        }

        // 3. Eliminar el registro del evento de la base de datos
        $sql_delete = "DELETE FROM evento WHERE id = :id";
        $stmt_delete = $conexion->prepare($sql_delete);
        $stmt_delete->execute([':id' => $id]);

        echo json_encode([
            'status' => 'success',
            'message' => 'El evento y su imagen asociada en Cloudinary fueron eliminados con éxito.'
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>