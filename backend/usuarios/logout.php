<?php
header('Content-Type: application/json');

require_once '../session_utils.php';

// Clear the session cookie
clearSessionCookie();

echo json_encode([
    'success' => true,
    'message' => 'Successfully logged out'
]);
?>
