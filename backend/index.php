<?php
// Cambiar el tipo de contenido a HTML
header('Content-Type: text/html; charset=utf-8');

// Incluir archivo de configuración
require_once 'config.php';

// Contenido HTML básico
echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de la Conexión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f5f5f5;
        }
        .container {
            text-align: center;
            padding: 40px;
            border-radius: 8px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .success {
            color: #2ecc71;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .error {
            color: #e74c3c;
            font-size: 24px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">';

try {
    // Intentar crear conexión
    $conn = getConnection();
    
    // Si llega aquí, la conexión fue exitosa
    echo '<h1 class="success">Conexión exitosa</h1>';
    echo '<p>La conexión a la base de datos se ha establecido correctamente.</p>';
    echo '<p>Para iniciar sesión, use la ruta <code>/backend/login/login.php</code></p>';
    
    // Información adicional sobre la autenticación
    echo '<div style="text-align: left; margin-top: 20px; padding: 15px; background-color: #f0f8ff; border-radius: 5px; border-left: 4px solid #1e90ff;">';
    echo '<h3>Información de autenticación:</h3>';
    echo '<ul>';
    echo '<li>Login: <code>/backend/login/login.php</code></li>';
    echo '<li>Logout: <code>/backend/login/logout.php</code></li>';
    echo '<li>Verificar sesión: <code>/backend/login/verify_session.php</code></li>';
    echo '<li>Cambiar contraseña: <code>/backend/login/change_password.php</code></li>';
    echo '<li>Restablecer contraseña: <code>/backend/login/reset_password.php</code></li>';
    echo '</ul>';
    echo '</div>';
    
    // Cerrar la conexión
    $conn->close();
} catch (Exception $e) {
    // Si hay un error, mostrar mensaje de error
    echo '<h1 class="error">Error de conexión</h1>';
    echo '<p>' . $e->getMessage() . '</p>';
}

echo '</div>
</body>
</html>';
?>
