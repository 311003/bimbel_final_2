<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara

// ✅ Tangkap parameter ID jika ada (default: null)
$id_presensi = isset($_GET['id_presensi']) ? $_GET['id_presensi'] : null;

// ✅ Query untuk mengambil data presensi termasuk nama murid dan status murid
if ($id_presensi) {
    $query_presensi = "SELECT fp.id_presensi, fp.id_jadwal, fp.id_guru, pb.paket, 
                              mm.nama, fp.tanggal_presensi, fp.jam_masuk, fp.jam_keluar, 
                              fp.id_status_murid, kp.status_murid, g.nama_guru
                        FROM final_presensi fp
                        LEFT JOIN keterangan_presensi kp ON fp.id_status_murid = kp.id_status_murid
                        LEFT JOIN master_murid mm ON fp.id_murid = mm.id_murid
                        LEFT JOIN paket_bimbel pb ON fp.id_paket = pb.id_paket
                        LEFT JOIN guru g ON fp.id_guru = g.id_guru
                        WHERE fp.id_presensi = ?";

    // ✅ Gunakan prepared statement untuk keamanan
    $stmt = $conn->prepare($query_presensi);
    $stmt->bind_param("s", $id_presensi);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Jika tidak ada ID yang diberikan, tampilkan semua data
    $query_presensi = "SELECT fp.id_presensi, fp.id_jadwal, fp.id_guru, pb.paket, 
                              mm.nama, fp.tanggal_presensi, fp.jam_masuk, fp.jam_keluar, 
                              fp.id_status_murid, kp.status_murid, g.nama_guru
                        FROM final_presensi fp
                        LEFT JOIN keterangan_presensi kp ON fp.id_status_murid = kp.id_status_murid
                        LEFT JOIN guru g ON fp.id_guru = g.id_guru
                        LEFT JOIN master_murid mm ON fp.id_murid = mm.id_murid
                        LEFT JOIN paket_bimbel pb ON fp_id_paket = pb.paket";

    $result = $conn->query($query_presensi);
}

// ✅ Cek koneksi dan eksekusi query
if (!$result) {
    die("Error mengambil data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>View Detail Presensi</title>
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
  <h1>Hasil Data Presensi</h1>
</div><!-- End Page Title -->

<!-- Tampilan Data Presensi -->
<!-- Tampilan Data Presensi -->
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID Presensi</th>
                        <th>Pilih Jadwal</th>
                        <th>Guru</th>
                        <th>Paket</th>
                        <th>Nama Murid</th>
                        <th>Tanggal Presensi</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>ID Status Murid</th>
                        <th>Status Murid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id_presensi']) ?></td>
                            <td><?= htmlspecialchars($row['id_jadwal']) ?></td>
                            <td><?= htmlspecialchars($row['nama_guru']) ?></td>
                            <td><?= htmlspecialchars($row['paket']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['tanggal_presensi']) ?></td>
                            <td><?= htmlspecialchars($row['jam_masuk']) ?></td>
                            <td><?= htmlspecialchars($row['jam_keluar']) ?></td>
                            <td><?= htmlspecialchars($row['id_status_murid']) ?></td>
                            <td><?= htmlspecialchars($row['status_murid'] ?? 'Tidak Ada Data') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<main>
<?= require('layouts/footer.php');?>
</body>
<html>