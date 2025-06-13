<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();

// Debugging: Menampilkan error jika ada
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah ID paket ada di URL
if (isset($_GET['id_paket'])) {
    $id_paket = $_GET['id_paket'];

    // Ambil data paket berdasarkan ID
    $query_select = "SELECT * FROM paket_bimbel WHERE id_paket = ?";
    $stmt = $conn->prepare($query_select);
    $stmt->bind_param("s", $id_paket);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Jika data tidak ditemukan
    if (!$row) {
        echo "<script>alert('Data tidak ditemukan!'); window.location.href='master_paket.php';</script>";
        exit();
    }

    // Jika tombol update ditekan
    if (isset($_POST['update'])) {
        $paket = $_POST['paket'];

        // Query UPDATE data ke database
        $query_update = "UPDATE paket_bimbel SET paket=? WHERE id_paket=?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ss", $paket, $id_paket);

        if ($stmt_update->execute()) {
            echo "<script>alert('Data berhasil diperbarui!'); window.location.href='master_paket.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui data!'); window.history.back();</script>";
        }

        $stmt_update->close();
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('ID tidak ditemukan!'); window.location.href='master_paket.php';</script>";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Edit Paket</title>
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
    <?= require('layouts/header.php'); ?>
    <?= require('layouts/sidemenu_owner.php'); ?>
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Edit Paket</h1>
        </div><!-- End Page Title -->

        <!-- Form -->

        <form method="POST" action="" enctype="multipart/form-data">

            <div class="card p-5 mb-5">
                <form method="POST" action="" enctype="multipart/form-data">

                    <!-- ID Paket (Read-Only) -->
                    <div class="mb-3">
                        <label for="id_paket" class="form-label">ID Paket</label>
                        <input type="text" class="form-control" id="id_paket" name="id_paket" value="<?= $row['id_paket'] ?>" readonly>
                    </div>

                    <!-- Nama Paket -->
                    <div class="mb-3">
                        <label for="paket" class="form-label">Nama Paket</label>
                        <input type="text" class="form-control" id="paket" name="paket" value="<?= $row['paket'] ?>" required>
                    </div>

                    <!-- Tombol Submit -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary" name="update">Perbarui</button>
                        <a href="master_paket.php" class="btn btn-secondary">Batal</a>
                    </div>

                </form>
            </div>
            </div>

            <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </main>
    <?= require('layouts/footer.php'); ?>
</body>

</html>