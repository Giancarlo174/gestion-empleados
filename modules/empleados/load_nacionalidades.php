<?php
// Iniciar sesión solo si no hay una sesión activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /ds6/index.php");
    exit;
}

// Incluir archivo de configuración
require_once "../../config/db.php";

// Función para cargar todas las nacionalidades
function loadNacionalidades($conn, $selectedCode = '') {
    // Consulta para obtener todas las nacionalidades
    $query = "SELECT codigo, pais FROM nacionalidad ORDER BY pais";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return '<option value="">Error al cargar nacionalidades</option>';
    }
    
    $output = '<option value="">Seleccione un país...</option>';
    
    // Generar opciones
    while ($row = mysqli_fetch_assoc($result)) {
        $selected = ($row['codigo'] == $selectedCode) ? 'selected' : '';
        $output .= '<option value="' . $row['codigo'] . '" ' . $selected . '>' 
                . htmlspecialchars($row['pais']) . '</option>';
    }
    
    return $output;
}
?>
