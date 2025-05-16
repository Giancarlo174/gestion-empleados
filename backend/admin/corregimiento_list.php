<?php
require_once __DIR__ . '/../config.php';
$conn = getConnection();
$provincia = $_GET['provincia'] ?? '';
$distrito = $_GET['distrito'] ?? '';
$stmt = $conn->prepare("SELECT codigo_corregimiento AS codigo, nombre_corregimiento AS nombre, codigo_provincia, codigo_distrito FROM corregimiento WHERE codigo_provincia=? AND codigo_distrito=?");
$stmt->bind_param("ss", $provincia, $distrito);
$stmt->execute();
$res = $stmt->get_result();
$data = [];
while ($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode($data);
$conn->close();
?>
