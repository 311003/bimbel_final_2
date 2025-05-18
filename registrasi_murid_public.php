<?php
include 'connection.php';

// Generate ID Murid Baru
$query_id = "SELECT LPAD(COALESCE(MAX(CAST(id_murid AS UNSIGNED)) + 1, 1), 2, '0') AS id_murid FROM master_murid";
$result = $conn->query($query_id);
$row = $result->fetch_assoc();
$new_id_murid = $row['id_murid'] ?? '01';

// Ambil paket bimbel
$query_paket = "SELECT id_paket, paket FROM paket_bimbel";
$result_paket = $conn->query($query_paket);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Pages / Login - NiceAdmin Bootstrap Template</title>
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

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

            </div>
              <header id="header" class="header fixed-top d-flex align-items-center">
                <img src="assets/img/logo_bimbel_rsdc.png" alt="Logo Bimbel RSDC"
                    style="height: 60px; width: auto; display: block;">
                <span class="d-none d-lg-block ms-3 fs-4">Bimbel RSDC</span>
              </div>
            </div><!-- End Logo -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registrasi Murid Baru</title>
  <link href="assets/css/style.css" rel="stylesheet"> <!-- sesuaikan jika pakai template -->
</head>
<body>
  <div class="container mt-5">
    <h2 class="mb-4">Form Registrasi Murid Baru</h2>

    <form action="proses_registrasi_murid.php" method="POST">
      <input type="hidden" name="id_murid" value="<?= htmlspecialchars($new_id_murid) ?>">

      <div class="mb-3">
        <label>Nama Murid</label>
        <input type="text" name="nama" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Tanggal Lahir</label>
        <input type="date" name="tanggal_lahir" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Alamat</label>
        <input type="text" name="alamat" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Kelas</label>
        <input type="text" name="kelas" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Asal Sekolah</label>
        <input type="text" name="asal_sekolah" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Jenis Kelamin</label><br>
        <input type="radio" name="jenis_kelamin" value="L" required> Laki-laki
        <input type="radio" name="jenis_kelamin" value="P" required> Perempuan
      </div>

      <div class="mb-3">
        <label>Nomor Telepon</label>
        <input type="text" name="no_telp" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Pilih Paket Bimbel</label>
        <select name="id_paket" class="form-control" required>
          <option value="">-- Pilih Paket --</option>
          <?php while ($paket = $result_paket->fetch_assoc()) : ?>
            <option value="<?= $paket['id_paket'] ?>"><?= htmlspecialchars($paket['paket']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Kirim Pendaftaran</button>
    </form>
  </div>
</body>
</html>

<div class="credits">
                <!-- All the links in the footer should remain intact. -->
                <!-- You can delete the links only if you purchased the pro version. -->
                <!-- Licensing information: https://bootstrapmade.com/license/ -->
                <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
              </div>

            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <?= require('layouts/footer.php');?>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>
</html>