<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara

// Debugging: Menampilkan error jika ada
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah ID guru ada di URL
if (isset($_GET['id_guru'])) {
    $id_guru = $_GET['id_guru'];

    // Ambil data guru berdasarkan ID
    $query_select = "SELECT * FROM guru WHERE id_guru = ?";
    $stmt = $conn->prepare($query_select);
    $stmt->bind_param("s", $id_guru);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Jika data tidak ditemukan
    if (!$row) {
        echo "<script>alert('Data tidak ditemukan!'); window.location.href='master_guru.php';</script>";
        exit();
    }

    // Jika tombol update ditekan
    if (isset($_POST['update'])) {
        $nama_guru = $_POST['nama_guru'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $alamat = $_POST['alamat'];
        $no_telp = $_POST['no_telp'];
        $pendidikan = $_POST['pendidikan'];

        // Query UPDATE data ke database
        $query_update = "UPDATE guru SET nama_guru=?, tanggal_lahir=?, alamat=?, no_telp=?, pendidikan=? WHERE id_guru=?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("ssssss", $nama_guru, $tanggal_lahir, $alamat, $no_telp, $pendidikan, $id_guru);

        if ($stmt_update->execute()) {
            echo "<script>alert('Data berhasil diperbarui!'); window.location.href='master_guru.php';</script>";
        } else {
            echo "<script>alert('Gagal memperbarui data!'); window.history.back();</script>";
        }

        $stmt_update->close();
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('ID tidak ditemukan!'); window.location.href='master_guru.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Edit Guru</title>
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

</body>
  <main id="main" class="main">

    <div class="pagetitle">
      <h1>Edit Data Guru</h1>
    </div><!-- End Page Title -->

    <!-- Form -->

    <form method="POST" action="" enctype="multipart/form-data">

    <div class="card p-5 mb-5">
    <form method="POST" action="" enctype="multipart/form-data">

            <!-- ID Guru (Read-Only) -->
            <div class="mb-3">
                <label for="id_guru" class="form-label">ID Guru</label>
                <input type="text" class="form-control" id="id_guru" name="id_guru" value="<?= $row['id_guru'] ?>" readonly>
            </div>

            <div class="row">
                <!-- Nama Guru -->
                <div class="mb-3">
                    <label for="nama_guru" class="form-label">Nama Guru</label>
                    <input type="text" class="form-control" id="nama_guru" name="nama_guru" value="<?= $row['nama_guru'] ?>" required>
                </div>

                <!-- Tanggal Lahir -->
                <div class="mb-3">
                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= $row['tanggal_lahir'] ?>" required>
                </div>
            </div>

            <div class="row">
                <!-- Alamat -->
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <input type="text" class="form-control" id="alamat" name="alamat" value="<?= $row['alamat'] ?>" required>
                </div>

                <!-- Nomor Telepon -->
                <div class="mb-3">
                    <label for="no_telp" class="form-label">Nomor Telepon</label>
                    <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?= $row['no_telp'] ?>" required>
                </div>
            </div>

            <!-- Pendidikan -->
            <div class="mb-3">
                <label for="pendidikan" class="form-label">Pendidikan</label>
                <input type="text" class="form-control" id="pendidikan" name="pendidikan" value="<?= $row['pendidikan'] ?>" required>
            </div>

            <!-- Tombol Submit -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary" name="update">Perbarui</button>
                <a href="master_guru.php" class="btn btn-secondary">Batal</a>
            </div>

        </form>
    </div>
</div>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
    // Membatasi input tanggal lahir agar tidak bisa memilih hari ini atau ke depan
    window.addEventListener("DOMContentLoaded", function () {
        const tanggalLahirInput = document.getElementById("tanggal_lahir");
        if (tanggalLahirInput) {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            const maxDate = yesterday.toISOString().split('T')[0];
            tanggalLahirInput.setAttribute("max", maxDate);
        }
    });
</script>
</main>
<?= require('layouts/footer.php');?>
</body>
</html>
