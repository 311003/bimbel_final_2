<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

$id_murid = $_SESSION['id_ref'];
$query_presensi = "
SELECT 
    p.id_presensi,
    p.id_jadwal,
    p.id_murid,
    m.nama AS nama_murid,
    p.tanggal_presensi,
    p.jam_masuk,
    p.jam_keluar,
    p.id_paket,
    k.paket AS nama_paket
FROM final_presensi p
LEFT JOIN master_murid m ON p.id_murid = m.id_murid
LEFT JOIN paket_bimbel k ON p.id_paket = k.id_paket
WHERE m.id_murid = '" . $id_murid . "'
ORDER BY p.id_presensi ASC
";

$result = $conn->query($query_presensi);

// Cek apakah query berhasil dijalankan
if (!$result) {
    die("Error mengambil data: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>View Presensi Murid</title>
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
    <?= require('layouts/sidemenu_murid.php'); ?>

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
                                <th>Nama Murid</th>
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
                                    echo "<td>" . htmlspecialchars($row['nama_murid']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_presensi']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jam_masuk']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jam_keluar']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_paket']) . "</td>";
                                    echo "<td>";
                                    echo "<a href='view_detail_presensi_murid.php?id_murid=" . $row['id_murid'] . "' class='btn btn-sm btn-warning'>Detail</a> ";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>Data tidak ditemukan</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </main>
    <?= require('layouts/footer.php'); ?>
    <?php 
    if($result->num_rows > 0){
        ?>
        <script>
            let table = new DataTable('#dataTable', {
                // options
                // lengthMenu: [
                //     [20, 30, 40, -1],
                //     [20, 30, 40, 'All']
                // ]
            });
        </script>
        <?php
    }
    ?>
    
</body>
</html>