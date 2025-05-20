<?php
include 'connection.php';
session_start();

// Cek role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit;
}

// Hitung jumlah pembayaran
$query_count_status = "
    SELECT status_pembayaran, COUNT(*) AS jumlah
    FROM pembayaran
    GROUP BY status_pembayaran
";
$result_count_status = $conn->query($query_count_status);

$jumlah_lunas = 0;
$jumlah_belum_lunas = 0;

if ($result_count_status) {
    while ($row = $result_count_status->fetch_assoc()) {
        if ($row['status_pembayaran'] == 'Lunas') {
            $jumlah_lunas = $row['jumlah'];
        } elseif ($row['status_pembayaran'] == 'Belum Lunas') {
            $jumlah_belum_lunas = $row['jumlah'];
        }
    }
}

// Ambil data jadwal
$query = "SELECT r.id_jadwal, p.paket AS nama_paket, r.tanggal_jadwal, r.jam_masuk, r.jam_keluar
          FROM jadwal r
          LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Dashboard - Owner</title>
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/custom.css" rel="stylesheet">
</head>

<body>
<?php require('layouts/header.php'); ?>
<?php require('layouts/sidemenu_owner.php'); ?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Beranda Owner</h1>
    <p>Selamat datang, Owner</p>
</main>
  </div>
  
<main id="main" class="main" style="margin-top: 20px;">
  <div class="container-fluid">
    <div class="row my-4">
      <div class="col-md-6 col-lg-4">
        <div class="card text-white bg-success mb-3">
          <div class="card-body">
            <h5 class="card-title">Pembayaran Lunas</h5>
            <p class="card-text display-6"><?php echo $jumlah_lunas; ?> data</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="card text-white bg-warning mb-3">
          <div class="card-body">
            <h5 class="card-title">Pembayaran Belum Lunas</h5>
            <p class="card-text display-6"><?php echo $jumlah_belum_lunas; ?> data</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow mb-4">
      <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary">Hasil Data Jadwal</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered" width="100%" cellspacing="0">
            <thead class="table-light">
              <tr>
                <th>ID Jadwal</th>
                <th>Nama Paket</th>
                <th>Tanggal Jadwal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['id_jadwal']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_paket']); ?></td>
                    <td><?php echo htmlspecialchars($row['tanggal_jadwal']); ?></td>
                    <td><?php echo htmlspecialchars($row['jam_masuk']); ?></td>
                    <td><?php echo htmlspecialchars($row['jam_keluar']); ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="5" class="text-center">Data tidak ditemukan.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</main>
<?= require('layouts/footer.php'); ?>
</body>
</html>
