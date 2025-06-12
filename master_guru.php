<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
// echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Master Gurur</title>
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
            <h1>Tabel Guru</h1>
        </div><!-- End Page Title -->

        <!-- Begin Page Content -->
        <div class="container-fluid">
            <!-- Tambah Data Guru -->
            <div class="mb-3">
                <a href="tambah_guru.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Data Guru
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID Guru</th>
                                <th>Nama Guru</th>
                                <th>Tanggal Lahir</th>
                                <th>Alamat</th>
                                <th>Nomor Telepon</th>
                                <th>Pendidikan</th>
                                <th>Gaji</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            include 'connection.php';

                            // Ambil daftar status guru
                            $statusQuery = "SELECT * FROM status_guru";
                            $statusResult = $conn->query($statusQuery);
                            $statusOptions = [];

                            while ($statusRow = $statusResult->fetch_assoc()) {
                                $statusOptions[$statusRow['id_status_guru']] = $statusRow['status_guru'];
                            }

                            // Ambil data guru
                            $query = "SELECT g.id_guru, g.nama_guru, g.tanggal_lahir, g.alamat, g.no_telp, g.pendidikan, g.gaji,
                          g.id_status_guru, s.status_guru 
                    FROM guru g
                    LEFT JOIN status_guru s ON g.id_status_guru = s.id_status_guru";

                            $result = $conn->query($query);

                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['id_guru']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_guru']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_lahir']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['no_telp']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['pendidikan']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['gaji']) . "</td>";
                                    echo "<td>
                            <select class='form-select form-select-sm' 
                                onchange='updateStatus(" . htmlspecialchars($row['id_guru']) . ", this.value)'>";

                                    foreach ($statusOptions as $id => $status) {
                                        $selected = ($row['id_status_guru'] == $id) ? "selected" : "";
                                        echo "<option value='$id' $selected>$status</option>";
                                    }

                                    echo "  </select>
                        </td>";
                                    echo "<td> <a href='edit_guru.php?id_guru=" . $row['id_guru'] . "' class='btn btn-sm btn-warning'>Edit</a> </td> ";
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
    <?= require('layouts/footer.php') ?>
    <script>
        let table = new DataTable('#dataTable', {
            // options
            // lengthMenu: [
            //     [20, 30, 40, -1],
            //     [20, 30, 40, 'All']
            // ]
        });
        function updateStatus(id_guru, id_status_guru) {
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "update_status_guru.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    console.log("Response: " + xhr.responseText.trim()); // Debugging: Lihat response dari server

                    if (xhr.status === 200 && xhr.responseText.trim() === "success") {
                        alert("Status berhasil diperbarui!");
                    } else {
                        alert("Gagal memperbarui status: " + xhr.responseText);
                    }
                }
            };

            xhr.send("id_guru=" + encodeURIComponent(id_guru) + "&id_status_guru=" + encodeURIComponent(id_status_guru));
        }
    </script>
</body>

</html>