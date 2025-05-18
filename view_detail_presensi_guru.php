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

// Cek apakah parameter ID Presensi ada
if (!isset($_GET['id_presensi']) || empty($_GET['id_presensi'])) {
    die("ID Presensi tidak ditemukan.");
}

$id_presensi = $_GET['id_presensi'];

$query_detail = "SELECT DISTINCT
    p.id_presensi,
    p.id_jadwal,
    p.id_guru,
    g.nama_guru,
    p.tanggal_presensi,
    p.jam_masuk,
    p.jam_keluar,
    p.id_paket,
    k.paket AS nama_paket,
    m.nama AS nama_murid,
    kt.status_presensi
FROM final_presensi p
LEFT JOIN presensi d ON p.id_presensi = d.id_presensi
LEFT JOIN master_murid m ON p.id_murid = m.id_murid
LEFT JOIN guru g ON p.id_guru = g.id_guru
LEFT JOIN paket_bimbel k ON p.id_paket = k.id_paket
LEFT JOIN keterangan_presensi kt ON p.id_status_murid = kt.id_status_murid
WHERE p.id_presensi = ?
ORDER BY m.nama;  -- Optional: to ensure student names are ordered
";

$stmt = $conn->prepare($query_detail);
$stmt->bind_param("i", $id_presensi);
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah ada hasil
if ($result->num_rows == 0) {
    die("Data presensi tidak ditemukan.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>View Detail Presensi Guru</title>
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
    <div class="container mt-4">
        <h1 class="mb-4 text-center">Detail Presensi</h1>

        <div class="card shadow-lg">
            <div class="card-body">
                <table class="table table-striped table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>ID Presensi</th>
                            <th>ID Jadwal</th>
                            <th>ID Guru</th>
                            <th>Nama Guru</th>
                            <th>Tanggal Presensi</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>ID Paket</th>
                            <th>Nama Paket</th>
                            <th>Nama Murid</th>
                            <th>Keterangan Presensi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                      while ($data = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($data['id_presensi']) ?></td>
                      <td><?= htmlspecialchars($data['id_jadwal']) ?></td>
                      <td><?= htmlspecialchars($data['id_guru']) ?></td>
                      <td><?= htmlspecialchars($data['nama_guru']) ?></td>
                      <td><?= htmlspecialchars($data['tanggal_presensi']) ?></td>
                      <td><?= htmlspecialchars($data['jam_masuk']) ?></td>
                      <td><?= htmlspecialchars($data['jam_keluar']) ?></td>
                      <td><?= htmlspecialchars($data['id_paket']) ?></td>
                      <td><?= htmlspecialchars($data['nama_paket']) ?></td>
                      <td><?= htmlspecialchars($data['nama_murid']) ?></td>
                      <td><?= htmlspecialchars($data['status_presensi']) ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
        <div class="text-center mt-3">
            <a href="hasil_presensi_guru.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</main>
<?= require('layouts/footer.php');?>
</body>
</html>