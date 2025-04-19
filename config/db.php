<?php
/**
 * Configuración de conexión a la base de datos
 * Este archivo establece la conexión con MySQL
 */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ds6');

// Intentar conectar a la base de datos MySQL
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Verificar la conexión
if ($conn === false) {
    die("ERROR: No se pudo conectar a la base de datos. " . mysqli_connect_error());
}

// Asegurarse de que la conexión use UTF-8
mysqli_set_charset($conn, "utf8");
?>
