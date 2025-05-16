<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$conn = getConnection();

$result = $conn->query("SELECT codigo, pais FROM nacionalidad ORDER BY pais ASC");
$nacionalidades = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nacionalidades[] = [
            "codigo" => $row["codigo"],
            "pais" => $row["pais"]
        ];
    }
}
echo json_encode($nacionalidades);
$conn->close();
?>
