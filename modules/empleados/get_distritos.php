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

// Verificar si se recibió el parámetro provincia
if (isset($_GET['provincia']) && !empty($_GET['provincia'])) {
    $provincia = $_GET['provincia'];
    
    // Consultar distritos
    $sql = "SELECT codigo_distrito, nombre_distrito FROM distrito WHERE codigo_provincia = ? ORDER BY nombre_distrito";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $provincia);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            $distritos = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $distritos[] = [
                    'codigo_distrito' => $row['codigo_distrito'],
                    'nombre_distrito' => $row['nombre_distrito']
                ];
            }
            
            // Devolver los distritos en formato JSON
            header('Content-Type: application/json');
            echo json_encode($distritos);
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
    // No se recibió el parámetro provincia
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Parámetro provincia faltante']);
}

// Cerrar conexión
mysqli_close($conn);
?>
