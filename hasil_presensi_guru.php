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

// ✅ Query untuk menampilkan data presensi dari tabel presensi dan detail_presensi
$query_presensi = "SELECT 
            p.id_presensi, 
            p.id_jadwal, 
            g.nama_guru,  
            p.tanggal_presensi, 
            p.jam_masuk, 
            p.jam_keluar,
            k.paket AS paket
          FROM presensi p
          LEFT JOIN detail_presensi d ON p.id_presensi = d.id_presensi
          LEFT JOIN jadwal j ON p.id_jadwal = j.id_jadwal
          LEFT JOIN guru g ON j.id_guru = g.id_guru
          LEFT JOIN paket_bimbel k ON p.id_paket = k.id_paket
          WHERE p.id_presensi
          GROUP BY p.id_presensi, p.id_jadwal, p.id_guru, p.tanggal_presensi, p.jam_masuk, p.jam_keluar, p.id_paket";

$result = $conn->query($query_presensi);

// Cek koneksi dan eksekusi query
if (!$result) {
    die("Error mengambil data: " . $conn->error);
    echo "<td>" . htmlspecialchars($row['edit_presensi']) . "</td>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - Dashboard Guru</title>
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
    <link href="assets/vendor/DataTables/datatables.min.css" rel="stylesheet">

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
    <?= require('layouts/header.php'); ?>
    <?= require('layouts/sidemenu_guru.php'); ?>
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Hasil Data Presensi</h1>
        </div><!-- End Page Title -->

        <!-- Begin Page Content -->
        <!-- ✅ Tampilan Tabel -->
        <div class="container-fluid">
            <div class="card shadow mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID Presensi</th>
                                <th>Pilih Jadwal</th>
                                <th>Nama Guru</th>
                                <th>Tanggal Presensi</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Nama Paket</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id_presensi']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['id_jadwal']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_guru']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_presensi']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jam_masuk']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jam_keluar']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['paket']) . "</td>";
                                    echo "<td>
                                        <a href='absensi_guru.php?id_presensi=" . $row['id_presensi'] . "' class='btn btn-sm btn-warning'>Absensi</a>
                                        <a href='view_detail_presensi_guru.php?id_presensi=" . $row['id_presensi'] . "' class='btn btn-sm btn-warning'>Detail</a>
                                    </td>";
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
    <?= require('layouts/footer.php'); ?>
    <script>
        let table = new DataTable('#dataTable', {
            // options
            // lengthMenu: [
            //     [20, 30, 40, -1],
            //     [20, 30, 40, 'All']
            // ]
        });
        </script>
</body>

</html>