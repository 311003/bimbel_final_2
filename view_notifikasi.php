<?php
session_start();
include 'connection.php';

$id_user = $_SESSION['id_user'] ?? 0;
$role = $_SESSION['role'] ?? 0;

// Ambil semua notifikasi milik user atau umum
$query = $conn->prepare("SELECT * FROM pusat_notifikasi WHERE  id_penerima = ? ORDER BY tanggal DESC");
$query->bind_param("i", $id_user);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Semua Notifikasi</title>
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


  <?php
  switch ($role) {
    case 1:
      require('layouts/sidemenu_owner.php');
      break;
    case 2:
      require('layouts/sidemenu_guru.php');
      break;
    case 3:
      require('layouts/sidemenu_murid.php');
      break;
  }
  ?>


  <main id="main" class="main">
    <div class="pagetitle">
      <i class="bi bi-bell"></i> Semua Notifikasi
    </div>

    <div class="card p-5 mb-5">
      <table class="table table-bordered table-hover">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Judul</th>
            <th>Keterangan</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          while ($row = $result->fetch_assoc()):
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($row['judul']) ?></td>
              <td><?= htmlspecialchars($row['keterangan']) ?></td>
              <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
              <td>
                <?php if (!empty($row['url'])): ?>
                  <a href="<?= htmlspecialchars($row['url']) ?>" class="btn btn-sm btn-primary" target="_blank">
                    <i class="bi bi-link-45deg"></i> Lihat
                  </a>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
  <?= require('layouts/footer.php'); ?>
</body>

</html>