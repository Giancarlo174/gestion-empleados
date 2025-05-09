<?php
// Check if we're in a subdirectory and adjust path accordingly
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    require_once __DIR__ . '/../config.php';
}

// Don't redefine constants that are already in config.php
// Just add additional constants if needed

/**
 * Creates a session token for the authenticated user
 */
function createSessionToken($cedula, $email, $role) {
    $tokenData = [
        'cedula' => $cedula,
        'email' => $email,
        'role' => $role,
        'issued_at' => time(),
        'expiration' => time() + SESSION_EXPIRATION
    ];
    
    // Simple JWT creation - for production, consider using a proper JWT library
    $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = base64_encode(json_encode($tokenData));
    $signature = base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    
    return "$header.$payload.$signature";
}

/**
 * Verifies if a session token is valid
 */
function verifySession($token) {
    if (!$token) return false;
    
    $parts = explode('.', $token);
    if (count($parts) != 3) return false;
    
    list($header, $payload, $signature) = $parts;
    
    // Verify signature
    $calculatedSignature = base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if ($calculatedSignature !== $signature) return false;
    
    // Decode payload
    $tokenData = json_decode(base64_decode($payload), true);
    
    // Check expiration
    if (!isset($tokenData['expiration']) || $tokenData['expiration'] < time()) {
        return false;
    }
    
    return $tokenData;
}

/**
 * Gets the session token from cookie or request headers
 */
function getSessionToken() {
    // Check cookie first
    if (isset($_COOKIE['ds6p1_session'])) {
        return $_COOKIE['ds6p1_session'];
    }
    
    // Check Authorization header
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
    }
    
    // Check POST/GET parameter as fallback
    if (isset($_REQUEST['session_token'])) {
        return $_REQUEST['session_token'];
    }
    
    return null;
}

/**
 * Gets user information from session token
 */
function getUserFromSession($token = null) {
    if (!$token) {
        $token = getSessionToken();
    }
    
    return verifySession($token);
}

/**
 * Clears the session cookie
 */
function clearSessionCookie() {
    setcookie('ds6p1_session', '', time() - 3600, '/', '', false, true);
}
?>
