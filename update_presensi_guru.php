<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

switch($_SESSION['role']){
    case 2:
        require 'middlewares/validasi_guru.php';
    break;
    case 3:
        require 'middlewares/validasi_murid.php';
    break;
}

// Ambil Id Presensi dari URL
$id_presensi = isset($_GET['id_presensi']) ? $_GET['id_presensi'] : '';

if ($id_jadwal) {
    // Ambil data berdasarkan Jadwal
    $query_murid = "SELECT * FROM presensi WHERE id_jadwal = ?";
    $stmt = $conn->prepare($query_murid);
    $stmt->bind_param("s", $id_jadwal);
    $stmt->execute();
    $result_presensi = $stmt->get_result();
    $presensi = $result_presensi->fetch_assoc();
    $stmt->close();

    // Cek apakah data ditemukan
    if (!$presensi) {
        die("<script>alert('Data tidak ditemukan untuk Presensi: $id_presensi'); window.history.back();</script>");
    }

    // Ambil ID Murid untuk update master_murid
    $id_presensi = $presensi['id_presensi'];
} else {
    die("<script>alert('Id Presensi tidak valid!'); window.history.back();</script>");
}

// Ambil data paket bimbel
$query_murid = "SELECT id_murid, paket FROM paket_bimbel";
$result_murid = $conn->query($query_murid);

// Jika form disubmit untuk update data
if (isset($_POST['update'])) {
    // Ambil data dari form
    $id_jadwal = $_POST['id_jadwal'];
    $keterangan_presensi = $_POST['keterangan_presensi'];

    // Mulai transaksi untuk keamanan
    $conn->begin_transaction();

    try {
        // **1. Update data di tabel registrasi_murid**
        $query_update_presensi = "UPDATE presensi
        SET id_presensi = ?, id_jadwal = ?, keterangan_presensi = ?
        WHERE id_presensi = ?";

        $stmt_presensi = $conn->prepare($query_update_presensi);
        if (!$stmt_presensi) {
            throw new Exception("Error preparing statement (presensi): " . $conn->error);
        }

        $stmt_presensi->bind_param("ss", $id_jadwal, $keterangan_presensi);
        if (!$stmt_presensi->execute()) {
            throw new Exception("Error executing update (presensi): " . $stmt_presensi->error);
        }
        $stmt_presensi->close();

        // **2. Update data di tabel master_murid**
        $query_update_master_murid = "UPDATE master_murid
        SET id_presensi = ?, id_jadwal = ?, keterangan_presensi = ?
        WHERE id_jadwal = ?";

        $stmt_master_murid = $conn->prepare($query_update_murid);
        if (!$stmt_master_murid) {
            throw new Exception("Error preparing statement (master_murid): " . $conn->error);
        }

        $stmt_master_murid->bind_param("ss", $id_jadwal, $keterangan_presensi);
        if (!$stmt_master_murid->execute()) {
            throw new Exception("Error executing update (master_murid): " . $stmt_master_murid->error);
        }
        $stmt_master_murid->close();

        // Commit transaksi jika semua update berhasil
        $conn->commit();
        echo "<script>alert('Data berhasil diperbarui di kedua tabel!'); window.location.href='hasil_presensi_guru.php';</script>";

    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        $conn->rollback();
        echo "<script>alert('Gagal mengupdate data! Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Update Presensi Guru</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/custom.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>
<?= require('layouts/header.php');?>
<?= require('layouts/sidemenu_guru.php');?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit Data Jadwal</h1>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="card p-5 mb-5">
            <!-- ID Jadwal -->
            <div class="mb-3">
                <label>ID Jadwal</label>
                <input type="text" class="form-control" name="id_jadwal" value="<?= htmlspecialchars($paket['id_jadwal'] ?? '') ?>" readonly>
            </div>

            <!-- Nama Murid -->
            <div class="mb-3">
                <label>Nama Murid</label>
                <input type="text" class="form-control" name="nama_murid" value="<?= htmlspecialchars($paket['nama_murid'] ?? '') ?>" readonly>
            </div>

           <!-- Keterangan Presensi -->
        <div class="form-group mb-3">
            <label for="keterangan">Keterangan Presensi</label>
            <div class="btn-group w-100" role="group" aria-label="Keterangan Presensi">
                <button type="button" class="btn btn-outline-success" onclick="setKeterangan('Hadir')">Hadir</button>
                <button type="button" class="btn btn-outline-warning" onclick="setKeterangan('Sakit')">Sakit</button>
                <button type="button" class="btn btn-outline-info" onclick="setKeterangan('Izin')">Izin</button>
                <button type="button" class="btn btn-outline-danger" onclick="setKeterangan('Absen')">Absen</button>
            </div>
            <input type="hidden" id="keterangan" name="keterangan" required>
        </div>
</main>

<!-- Tombol Update -->
<div class="text-center">
                <button type="submit" class="btn btn-primary w-100" name="update">Update Presensi</button>
                <a href="hasil_presensi_guru.php" class="btn btn-secondary w-100 mt-2">Batal</a>
            </div>
        </div>
    </form>
</main>

<!-- JavaScript untuk mengatur tombol keterangan -->
<script>
function setKeterangan(value) {
    document.getElementById('keterangan').value = value;

    // Reset semua tombol ke warna default
    let buttons = document.querySelectorAll('.btn-group button');
    buttons.forEach(button => {
        button.classList.remove('btn-success', 'btn-warning', 'btn-info', 'btn-danger');
        button.classList.add('btn-outline-success', 'btn-outline-warning', 'btn-outline-info', 'btn-outline-danger');
    });

    // Set warna tombol aktif
    let selectedButton = [...buttons].find(btn => btn.innerText === value);
    if (selectedButton) {
        selectedButton.classList.remove('btn-outline-success', 'btn-outline-warning', 'btn-outline-info', 'btn-outline-danger');
        if (value === 'Hadir') selectedButton.classList.add('btn-success');
        if (value === 'Sakit') selectedButton.classList.add('btn-warning');
        if (value === 'Izin') selectedButton.classList.add('btn-info');
        if (value === 'Absen') selectedButton.classList.add('btn-danger');
    }
}
</script>

<!-- Bootstrap JS -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<main>
<?= require('layouts/footer.php');?>
</body>
</html>