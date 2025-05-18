<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

// Cek apakah user sudah login
if (!isset($_SESSION['role'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location.href='login.php';</script>";
    exit;
}

$role = $_SESSION['role'];
$id_murid = isset($_SESSION['id_murid']) ? $_SESSION['id_murid'] : '';

// Jika user adalah murid, hanya tampilkan datanya sendiri
if ($role === 'murid') {
    $query = "SELECT r.no_reg, r.id_murid, r.tgl_reg, r.nama, r.tanggal_lahir, r.alamat, 
                     r.jenis_kelamin, r.kelas, r.asal_sekolah, r.no_telp, p.paket AS nama_paket
              FROM registrasi_murid r
              LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
              WHERE r.id_murid = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id_murid);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Jika user adalah owner atau admin, tampilkan semua data
    $query = "SELECT r.no_reg, r.id_murid, r.tgl_reg, r.nama, r.tanggal_lahir, r.alamat, 
                     r.jenis_kelamin, r.kelas, r.asal_sekolah, r.no_telp, p.paket AS nama_paket
              FROM registrasi_murid r
              LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket";
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>View registrasi Murid</title>
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
<?= require('layouts/sidemenu_owner.php');?>

  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Hasil Data Registrasi</h1>
    </div><!-- End Page Title -->

   <!-- Begin Page Content -->
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No Registrasi</th>
                        <th>ID Murid</th>
                        <th>Tanggal Registrasi</th>
                        <th>Nama</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Jenis Kelamin</th>
                        <th>Kelas</th>
                        <th>Asal Sekolah</th>
                        <th>Paket</th>
                        <th>Nomor Telepon</th>                                   
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['no_reg'] . "</td>";
                            echo "<td>" . $row['id_murid'] . "</td>";
                            echo "<td>" . $row['tgl_reg'] . "</td>";
                            echo "<td>" . $row['nama'] . "</td>";
                            echo "<td>" . $row['tanggal_lahir'] . "</td>";
                            echo "<td>" . $row['alamat'] . "</td>";
                            echo "<td>" . $row['jenis_kelamin'] . "</td>";
                            echo "<td>" . $row['kelas'] . "</td>";
                            echo "<td>" . $row['asal_sekolah'] . "</td>";
                            echo "<td>" . $row['nama_paket'] . "</td>"; // Tampilkan nama paket
                            echo "<td>" . $row['no_telp'] . "</td>";
                            echo "<td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>Data tidak ditemukan</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
  </main>
<?= require('layouts/header.php');?>
</body>
</html>