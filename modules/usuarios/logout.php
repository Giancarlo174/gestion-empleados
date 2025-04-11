<?php
// Inicializar la sesión
session_start();
 
// Eliminar todas las variables de sesión
$_SESSION = array();
 
// Destruir la sesión
session_destroy();
 
// Redirigir al usuario a la página de inicio (login)
header("location: /ds6/index.php");
exit;
?>
