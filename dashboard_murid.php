<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

require 'middlewares/validasi_murid.php';

// ✅ Query dengan JOIN ke tabel guru dan paket_bimbel
$query = "SELECT 
            r.id_jadwal, 
            p.paket AS nama_paket, 
            r.tanggal_jadwal, 
            r.jam_masuk, 
            r.jam_keluar 
          FROM jadwal r
          LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket";

$result = $conn->query($query);

$id_ref = $_SESSION['id_ref'];

// dashboard_murid.php
if (!isset($_SESSION['role']) || $_SESSION['role'] != 3) {
  header('Location: login.php');
  exit;
}

$id_ref = $_SESSION['id_ref'];
$murid_query = $conn->query("SELECT * FROM master_murid WHERE id_murid = '$id_ref'");
$murid = $murid_query->fetch_assoc();

$jadwal_query = "SELECT j.id_jadwal, p.paket AS nama_paket, j.tanggal_jadwal, j.jam_masuk, j.jam_keluar 
               FROM jadwal j
               LEFT JOIN paket_bimbel p ON j.id_paket = p.id_paket";
$result = $conn->query($jadwal_query);
?>

<div class="pagetitle">
  <h1>Hasil Data Jadwal</h1>
</div><!-- End Page Title -->

<!-- Begin Page Content -->

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - Dashboard Murid</title>
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
<?= require('layouts/sidemenu_murid.php');?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Dashboard Murid</h1>
    <p>Selamat datang, <strong><?= htmlspecialchars($murid['nama']) ?></strong></p>
  </div>

  <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">Hasil Data Jadwal</h5>
      </div>
  <div class="card shadow mb-4">
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>ID Jadwal</th>
            <th>Nama Paket</th>
            <th>Tanggal Jadwal</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['id_jadwal']) ?></td>
              <td><?= htmlspecialchars($row['nama_paket']) ?></td>
              <td><?= htmlspecialchars($row['tanggal_jadwal']) ?></td>
              <td><?= htmlspecialchars($row['jam_masuk']) ?></td>
              <td><?= htmlspecialchars($row['jam_keluar']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<?= require('layouts/footer.php');?>
</body>
</html>