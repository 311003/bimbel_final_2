<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();

// ✅ Query dengan JOIN ke tabel guru dan paket_bimbel
$query = "SELECT 
            r.id_jadwal, 
            g.nama_guru,
            p.paket AS nama_paket, 
            r.tanggal_jadwal, 
            r.jam_masuk, 
            r.jam_keluar 
          FROM jadwal r
          LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
          LEFT JOIN guru g ON r.id_guru = g.id_guru";

$result = $conn->query($query);

// Cek apakah query berhasil dieksekusi
if (!$result) {
    die("Query error: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Hasil Data Jadwal</title>
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
    <?= require('layouts/sidemenu_owner.php'); ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Hasil Data Jadwal</h1>
        </div><!-- End Page Title -->

        <!-- Begin Page Content -->
        <!-- ✅ Tampilan Tabel -->
        <div class="container-fluid">
            <div class="card shadow mb-4">
                <div class="table-responsive">
                    <table id="viewTable" class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID Jadwal</th>
                                <th>Nama Paket</th>
                                <th>Nama Guru</th>
                                <th>Tanggal Jadwal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Keluar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id_jadwal']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_paket']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_guru']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_jadwal']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jam_masuk']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jam_keluar']) . "</td>";
                                    echo "<td>";
                                    echo "<a href='edit_jadwal.php?id_jadwal=" . $row['id_jadwal'] . "' class='btn btn-sm btn-warning'>Edit</a> ";
                                    echo "<a href='delete_jadwal.php?id_jadwal=" . $row['id_jadwal'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin menghapus data?\")'>Hapus</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Data tidak ditemukan</td></tr>";
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
        let table = new DataTable('#viewTable', {
            // options
            // lengthMenu: [
            //     [20, 30, 40, -1],
            //     [20, 30, 40, 'All']
            // ]
        });
    </script>
</body>

</html>