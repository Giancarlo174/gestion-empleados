<?php
// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Default XAMPP user
define('DB_PASS', '');      // Default XAMPP password (empty)
define('DB_NAME', 'ds6');   // Database name

/**
 * Gets a new database connection
 * @return mysqli A new database connection
 * @throws Exception if connection fails
 */
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check for connection errors
    if ($conn->connect_error) {
        throw new Exception("Connection error: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset("utf8");
    
    return $conn;
}

/**
 * Ensures clean output for JSON responses
 * Clears any output buffers to prevent contamination of JSON data
 */
function ensureCleanOutput() {
    // Set appropriate content type for JSON
    header('Content-Type: application/json; charset=UTF-8');
    
    // Set headers for cross-origin requests (CORS) to allow Android app access
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    // Clear any previous output that might contaminate the JSON response
    if (ob_get_length()) ob_clean();
}
?>
