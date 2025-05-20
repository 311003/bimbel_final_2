<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara

// Generate ID Guru (Format: 01, 02, 03, dst.)
$query_id = "SELECT LPAD(COALESCE(MAX(CAST(id_guru AS UNSIGNED)) + 1, 1), 2, '0') AS id_guru FROM guru";
$result = $conn->query($query_id);
$row = $result->fetch_assoc();
$id_guru = $row['id_guru'];

if (isset($_POST['tambah'])) {
    // Ambil data dari form
    $id_guru = $_POST['id_guru'];
    $nama_guru = $_POST['nama_guru'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $pendidikan = $_POST['pendidikan'];

    // Query untuk insert data ke tabel_guru
    $query_insert_guru = "INSERT INTO guru (id_guru, nama_guru, tanggal_lahir, alamat, no_telp, pendidikan)
                            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_guru = $conn->prepare($query_insert_guru);
    $stmt_guru->bind_param("ssssss", $id_guru, $nama_guru, $tanggal_lahir, $alamat, $no_telp, $pendidikan);

        // Eksekusi query 
        if ($stmt_guru->execute()) {
            echo "<script>alert('Data guru berhasil ditambahkan!'); window.location.href='master_guru.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan saat menyimpan data ke tabel guru!'); window.history.back();</script>";
        }
    $stmt_guru->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Tambah Guru</title>
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
      <h1>Tambah Data Guru</h1>
    </div><!-- End Page Title -->

    <!-- Form -->

    <form method="POST" action="" enctype="multipart/form-data">

    <div class="card p-5 mb-5">
    <form method="POST" action="" enctype="multipart/form-data">
        

        <!-- ID Guru -->
        <div class="form-group mb-3">
            <label for="id_guru">ID Guru</label>
            <input type="text" class="form-control" id="id_guru" name="id_guru" value="<?php echo $id_guru; ?>" readonly>
        </div>

        <!-- Nama Guru -->
        <div class="form-group mb-3">
            <label for="nama_guru">Nama Guru</label>
            <input type="text" class="form-control" id="nama_guru" name="nama_guru">
        </div>

        <!-- Tanggal Lahir -->
        <div class="form-group mb-3">
            <label for="tanggal_lahir">Tanggal Lahir</label>
            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
        </div>

        <!-- Alamat -->
        <div class="form-group mb-3">
            <label for="alamat">Alamat</label>
            <input type="text" class="form-control" id="alamat" name="alamat" required>
        </div>

        <!-- Nomor Telepon -->
        <div class="form-group mb-3">
            <label for="no_telp">Nomor Telepon</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" required>
        </div>

        <!-- Pendidikan -->
        <div class="form-group mb-3">
            <label for="pendidikan">Pendidikan</label>
            <input type="text" class="form-control" id="pendidikan" name="pendidikan" required>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-primary" name="tambah">Simpan</button>
        </div>

    </form>
</div>

</main>
<?= require('layouts/footer.php');?>
</body>
</html>
