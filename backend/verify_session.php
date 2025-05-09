<?php
// Desactivar reportes de errores para la salida
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Iniciar buffer de salida inmediatamente
ob_start();

// Establecer control de errores personalizado
set_error_handler(function($severity, $message, $file, $line) {
    // Limpiar buffer de salida
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => "PHP Error: $message in $file on line $line",
        'authenticated' => false
    ]);
    exit;
});

// Establecer manejador de excepciones no capturadas
set_exception_handler(function($exception) {
    // Limpiar buffer de salida
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => "Uncaught exception: " . $exception->getMessage(),
        'authenticated' => false
    ]);
    exit;
});

// Establecer handlers CORS y Content-Type
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With, Authorization');

// Para solicitudes OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Incluir el archivo de utilidades de sesión con ruta adaptable
    if (file_exists(__DIR__ . '/session_utils.php')) {
        require_once __DIR__ . '/session_utils.php';
    } else if (file_exists(__DIR__ . '/login/session_utils.php')) {
        require_once __DIR__ . '/login/session_utils.php';
    } else {
        throw new Exception('Session utils file not found. Searched in ' . __DIR__ . '/session_utils.php and ' . __DIR__ . '/login/session_utils.php');
    }
    
    // Get session token from cookie or request body
    $sessionToken = getSessionToken();
    
    if (!$sessionToken) {
        echo json_encode([
            'success' => false,
            'message' => 'No session token provided',
            'authenticated' => false
        ]);
        exit;
    }
    
    // Verify if the session token is valid
    $sessionData = verifySession($sessionToken);
    if ($sessionData) {
        // Get user info from session
        echo json_encode([
            'success' => true,
            'message' => 'Session is valid',
            'authenticated' => true,
            'user' => $sessionData
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or expired session',
            'authenticated' => false
        ]);
    }
} catch (Exception $e) {
    // Limpiar completamente cualquier salida previa
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    // Asegurar que siempre se devuelve JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'authenticated' => false
    ]);
}

// Asegurar que no haya salida adicional después de la respuesta JSON
exit();
?>
