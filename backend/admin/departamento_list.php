<?php
require_once __DIR__ . '/../config.php';
$conn = getConnection();
$res = $conn->query("SELECT codigo, nombre FROM departamento");
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode($data);
$conn->close();
?>
