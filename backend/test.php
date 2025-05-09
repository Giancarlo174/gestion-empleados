<?php
// Iniciar buffer de salida
ob_start();

// Asegurar que la respuesta sea siempre JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');

// Para solicitudes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Información de conexión
    $server_info = [
        'php_version' => phpversion(),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'request_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] ?? time()),
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
        'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
        'http_host' => $_SERVER['HTTP_HOST'] ?? 'Unknown'
    ];
    
    // Respuesta de prueba simple
    echo json_encode([
        'success' => true,
        'message' => 'API test successful',
        'timestamp' => time(),
        'datetime' => date('Y-m-d H:i:s'),
        'server_info' => $server_info
    ]);
} catch (Exception $e) {
    // Limpiar cualquier output previo
    ob_clean();
    
    // Respuesta de error
    echo json_encode([
        'success' => false,
        'message' => 'Test error: ' . $e->getMessage()
    ]);
}
?>
