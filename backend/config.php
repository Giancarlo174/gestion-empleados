<?php
// Control de errores global - captura todos los errores y los convierte en excepciones
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // Este error no se debe manejar según la configuración de error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Evitar mostrar errores directamente - en su lugar los convertiremos en JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Asegurar que no haya salida antes de los headers
ob_start();

// Database configuration parameters
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ds6');

// Session and cookies configuration
define('COOKIE_EXPIRATION', time() + 86400); // 24 hours
define('SESSION_EXPIRATION', 86400); // 24 hours
define('JWT_SECRET', 'ds6p12_secure_jwt_key'); // Clave para tokens JWT

// Función para crear conexión a la base de datos
function getConnection() {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Establecer charset a utf8
    $conn->set_charset("utf8");
    
    return $conn;
}

// Función para manejar errores y devolverlos en formato JSON
function handleError($message) {
    ensureCleanOutput();
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}

// Función para limpiar el buffer de salida y asegurar respuestas JSON limpias
function ensureCleanOutput() {
    // Limpiar cualquier salida previa
    if (ob_get_length()) ob_clean();
    
    // Establecer cabeceras para JSON y CORS
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');
}

// Manejador de excepciones no capturadas
set_exception_handler(function($e) {
    ensureCleanOutput();
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
});
?>
