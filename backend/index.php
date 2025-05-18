<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $conn->close();
    
    if ($isBrowser) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Estado de Conexión</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
                .success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; padding: 15px; border-radius: 5px; text-align: center; }
                h1 { color: #333; text-align: center; }
                .details { background: #f5f5f5; padding: 15px; border-radius: 5px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <h1>Estado de Conexión a la Base de Datos</h1>
            <div class="success">
                <h2>✅ Conexión Exitosa</h2>
            </div>
            </div>
        </body>
        </html>';
    } else {
        ensureCleanOutput();
        echo json_encode($response);
    }
} catch (Exception $e) {
    if ($isBrowser) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Error de Conexión</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
                .error { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: 15px; border-radius: 5px; text-align: center; }
                h1 { color: #333; text-align: center; }
            </style>
        </head>
        <body>
            <h1>Estado de Conexión a la Base de Datos</h1>
            <div class="error">
                <h2>❌ Error de Conexión</h2>
            </div>
        </body>
        </html>';
    }
}
?>
