<?php
// ===== proses_registrasi_guru.php =====
include 'connection.php';
session_start();
$id_guru = $_POST['id_guru'];
$nama = $_POST['nama_guru'];
$tanggal = $_POST['tanggal_lahir'];
$alamat = $_POST['alamat'];
$no_telp = $_POST['no_telp'];
$pendidikan = $_POST['pendidikan'];

$query = $conn->prepare("INSERT INTO guru (nama_guru, tanggal_lahir, alamat, no_telp, pendidikan) VALUES ( ?, ?, ?, ?, ?)");
$query->bind_param("sssss",  $nama, $tanggal, $alamat, $no_telp, $pendidikan);
if ($query->execute()) {
    
    // Update ke tm_user
    $last_id = $conn->insert_id;
    $username = isset($_SESSION['user_register'])?$_SESSION['user_register']['username']:$_SESSION['username'];
    $stmt = $conn->prepare("UPDATE tm_user SET id_ref = ?, role = 2 WHERE username = ?");
    $stmt->bind_param("ss", $last_id, $username);
    $stmt->execute();
    if(isset($_SESSION['user_register'])){
        unset($_SESSION['user_register']);
    }
    echo "<script>alert('Data guru berhasil disimpan.'); window.location='terima_kasih.php';</script>";
} else {
    echo "<script>alert('Gagal menyimpan data.'); window.history.back();</script>";
}
?>