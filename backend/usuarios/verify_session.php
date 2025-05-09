<?php
header('Content-Type: application/json');

require_once '../session_utils.php';

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
if (verifySession($sessionToken)) {
    // Get user info from session
    $userInfo = getUserFromSession($sessionToken);
    
    echo json_encode([
        'success' => true,
        'message' => 'Session is valid',
        'authenticated' => true,
        'user' => $userInfo
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or expired session',
        'authenticated' => false
    ]);
}
?>
