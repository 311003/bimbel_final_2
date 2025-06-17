<?php
session_start();
include '../connection.php';
$id_user = $_SESSION['id_user'] ?? 0;

$q = $conn->prepare("SELECT id, judul, keterangan, tanggal, url, status FROM pusat_notifikasi WHERE  id_penerima = ? ORDER BY tanggal DESC LIMIT 5");
$q->bind_param("i", $id_user);
$q->execute();
$result = $q->get_result();
$data = [];

while ($row = $result->fetch_assoc()) {
  $row['tanggal'] = date('d M Y H:i', strtotime($row['tanggal']));
  $data[] = $row;
}
echo json_encode($data);