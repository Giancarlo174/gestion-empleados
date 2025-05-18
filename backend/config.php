<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  
define('DB_PASS', '');      
define('DB_NAME', 'ds6');   

    $conn = getConnection();

function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection error: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");

    return $conn;
}

function ensureCleanOutput() {
    header('Content-Type: application/json; charset=UTF-8');
    
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    
    if (ob_get_length()) ob_clean();
}
?>
