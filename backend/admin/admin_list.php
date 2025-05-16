<?php
require_once __DIR__ . '/../config.php';

$conn = getConnection();

$sql = "SELECT id, cedula, correo_institucional FROM usuarios ORDER BY id ASC";

$result = $conn->query($sql);

$admins = array();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admins[] = [
            "id" => $row["id"],
            "cedula" => $row["cedula"],
            "correo" => $row["correo_institucional"]
        ];
    }
}

echo json_encode($admins);

$conn->close();
?>
