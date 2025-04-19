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

// Verificar si se recibió el parámetro de búsqueda
if (isset($_GET['term']) && !empty($_GET['term'])) {
    $term = $_GET['term'];
    
    // Consultar nacionalidades que coincidan con el término
    $sql = "SELECT codigo, pais FROM nacionalidad WHERE pais LIKE ? ORDER BY pais LIMIT 20";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Añadir comodines para la búsqueda
        $term_param = "%$term%";
        
        // Vincular parámetros
        mysqli_stmt_bind_param($stmt, "s", $term_param);
        
        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            $nacionalidades = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $nacionalidades[] = [
                    'id' => $row['codigo'],
                    'text' => $row['pais']
                ];
            }
            
            // Devolver resultados en formato JSON
            header('Content-Type: application/json');
            echo json_encode(['results' => $nacionalidades]);
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
    // No se recibió término de búsqueda, devolver algunas opciones por defecto
    $sql = "SELECT codigo, pais FROM nacionalidad ORDER BY pais LIMIT 20";
    $result = mysqli_query($conn, $sql);
    
    $nacionalidades = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $nacionalidades[] = [
            'id' => $row['codigo'],
            'text' => $row['pais']
        ];
    }
    
    // Devolver resultados en formato JSON
    header('Content-Type: application/json');
    echo json_encode(['results' => $nacionalidades]);
}

// Cerrar conexión
mysqli_close($conn);
?>
