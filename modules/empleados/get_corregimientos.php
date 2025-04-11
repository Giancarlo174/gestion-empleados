<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Incluir archivo de configuración
require_once "../../config/db.php";

// Verificar si se recibieron los parámetros provincia y distrito
if (isset($_GET['provincia']) && !empty($_GET['provincia']) && isset($_GET['distrito']) && !empty($_GET['distrito'])) {
    $provincia = $_GET['provincia'];
    $distrito = $_GET['distrito'];
    
    // Consultar corregimientos
    $sql = "SELECT codigo_corregimiento, nombre_corregimiento FROM corregimiento WHERE codigo_provincia = ? AND codigo_distrito = ? ORDER BY nombre_corregimiento";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $provincia, $distrito);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            $corregimientos = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $corregimientos[] = [
                    'codigo_corregimiento' => $row['codigo_corregimiento'],
                    'nombre_corregimiento' => $row['nombre_corregimiento']
                ];
            }
            
            // Devolver los corregimientos en formato JSON
            header('Content-Type: application/json');
            echo json_encode($corregimientos);
        } else {
            // Error al ejecutar la consulta
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error al ejecutar la consulta']);
        }
        
        mysqli_stmt_close($stmt);
    } else {
        // Error al preparar la consulta
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Error al preparar la consulta']);
    }
} else {
    // No se recibieron los parámetros necesarios
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Parámetros provincia o distrito faltantes']);
}

// Cerrar conexión
mysqli_close($conn);
?>
