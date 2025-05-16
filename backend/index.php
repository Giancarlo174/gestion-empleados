<?php
// Include configuration file
require_once 'config.php';

// Determine if request is from browser or Android app
// Check for common Android client identifiers or specific header
$isAndroidApp = (
    isset($_SERVER['HTTP_USER_AGENT']) && 
    (strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false || 
    strpos($_SERVER['HTTP_USER_AGENT'], 'okhttp') !== false)
) || isset($_SERVER['HTTP_X_REQUESTED_WITH']);

$isBrowser = !$isAndroidApp && isset($_SERVER['HTTP_USER_AGENT']);

// Test connection and prepare response
try {
    $conn = getConnection();
    
    // Connection successful
    $response = [
        'success' => true,
        'message' => 'Conexión Exitosa',
        'server_info' => $conn->server_info,
        'host_info' => $conn->host_info,
        'db_name' => DB_NAME,
        'client_type' => $isAndroidApp ? 'android' : 'browser'
    ];
    
    // Close connection
    $conn->close();
    
    // If it's a browser request, display a more user-friendly response
    if ($isBrowser) {
        // Set content type to HTML for browsers
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
                <p>Se ha establecido la conexión con la base de datos correctamente.</p>
            </div>
            <div class="details">
                <h3>Información de la Conexión:</h3>
                <p>Servidor: '.$response["server_info"].'</p>
                <p>Host: '.$response["host_info"].'</p>
                <p>Base de datos: '.$response["db_name"].'</p>
            </div>
        </body>
        </html>';
    } else {
        // Set JSON response for API clients (including Android)
        ensureCleanOutput();
        echo json_encode($response);
    }
} catch (Exception $e) {
    // Handle connection error
    if ($isBrowser) {
        // HTML error message for browsers
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
                <p>'.$e->getMessage().'</p>
            </div>
        </body>
        </html>';
    } else {
        // JSON error message for API clients (including Android)
        ensureCleanOutput();
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'client_type' => $isAndroidApp ? 'android' : 'api'
        ]);
    }
}
?>
