<?php
session_start();
include '../connection.php';
$id_user = $_SESSION['id_user'] ?? 0;

$stmt = $conn->prepare("UPDATE pusat_notifikasi SET status = 2 WHERE (id_penerima IS NULL OR id_penerima = ?) AND status = 1");
$stmt->bind_param("i", $id_user);
$stmt->execute();