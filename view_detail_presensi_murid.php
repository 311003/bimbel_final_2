<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

// Ambil id_murid dari parameter GET
$id_murid = isset($_GET['id_murid']) ? intval($_GET['id_murid']) : 0;
$result = null;

if ($id_murid > 0) {
$query = "SELECT DISTINCT
    p.id_presensi,
    p.id_guru,
    g.nama_guru,
    p.tanggal_presensi,
    m.nama AS nama_murid,
    kt.status_presensi
FROM final_presensi p
LEFT JOIN presensi d ON p.id_presensi = d.id_presensi
LEFT JOIN master_murid m ON p.id_murid = m.id_murid
LEFT JOIN guru g ON p.id_guru = g.id_guru
LEFT JOIN keterangan_presensi kt ON p.id_status_murid = kt.id_status_murid
WHERE p.id_murid = ?
ORDER BY m.nama;  -- Optional: to ensure student names are ordered
";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_murid);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>View Detail Presensi Murid</title>
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

<body>
    <?= require('layouts/header.php');?>
    <?= require('layouts/sidemenu_murid.php');?>

<main id="main" class="main">
    <div class="pagetitle">
      <h1>Detail Presensi Murid</h1>
    </div>

    <section class="section">
      <div class="card">
        <div class="card-body pt-4">

          <?php if ($result && $result->num_rows > 0): ?>
            <div class="table-responsive">
              <table class="table table-bordered text-center">
                <thead>
                  <tr>
                    <th>Nama Murid</th>
                    <th>ID Presensi</th>
                    <th>Tanggal Presensi</th>
                    <th>Nama Guru</th>
                    <th>Status Presensi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                      <td><?= htmlspecialchars($row['nama_murid']) ?></td>
                      <td><?= htmlspecialchars($row['id_presensi']) ?></td>
                      <td><?= htmlspecialchars($row['tanggal_presensi']) ?></td>
                      <td><?= htmlspecialchars($row['nama_guru']) ?></td>
                      <td><?= htmlspecialchars($row['status_presensi']) ?></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <p class="text-center">Data presensi tidak ditemukan untuk murid ini.</p>
          <?php endif; ?>
          <div class="text-center mt-3">
            <a href="view_presensi_murid.php" class="btn btn-secondary">Kembali</a>
        </div>
        </div>
      </div>
    </section>

  </main>
  <?= require('layouts/footer.php');?>
</body>
</html>