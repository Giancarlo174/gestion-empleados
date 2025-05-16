<?php
include('../config.php');
$codigo = $_GET['codigo'];

$query = "DELETE FROM cargos WHERE codigo='$codigo'";
$result = mysqli_query($conn, $query);

if ($result) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => mysqli_error($conn)]);
}
?>
