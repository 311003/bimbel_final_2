<?php

session_start();

include 'connection.php'; // Pastikan file koneksi database sudah di-include
require 'classes/Cashflow.php';

$id_pembayaran = $_GET['id_pembayaran'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $param = isset($_POST['params']) ? $_POST['params'] : null;
  $id_bukti = isset($_POST['id_bukti']) ? $_POST['id_bukti'] : null;
  switch ($param) {
    case 'konfrim':
      $stmt = $conn->prepare("UPDATE bukti_pembayaran_guru SET status= 1 where id_pembayaran = ? AND id_bukti = ? ");
      $stmt->bind_param("ss", $id_pembayaran, $id_bukti);
      $stmt->execute();
      break;
    case 'delete':
      $stmt = $conn->prepare("SELECT bukti_pembayaran, jumlah_bayar,tanggal_bayar FROM bukti_pembayaran_guru WHERE id_bukti = ?");
      $stmt->bind_param("i", $id_bukti);
      $stmt->execute();
      $stmt->bind_result($file_path, $jumlah_bayar, $tanggal_bayar);
      $stmt->fetch();
      $stmt->close();

      // Hapus file fisik
      if ($file_path && file_exists($file_path)) {
        unlink($file_path);
      }

      //update pembayaran

      $query = "SELECT gaji, jumlah_bayar, sisa_bayar, status_pembayaran FROM pembayaran_guru WHERE id_pembayaran = ?";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("s", $id_pembayaran);
      $stmt->execute();
      $stmt->bind_result($gaji, $jumlah_bayar_lama, $sisa_biaya, $status_pembayaran);
      $stmt->fetch();
      $stmt->close();

      // Menghitung jumlah_bayar baru dan sisa_biaya
      $jumlah_bayar_total = $jumlah_bayar_lama - $jumlah_bayar;
      $sisa_bayar = $gaji - $jumlah_bayar_total;

      // Tentukan status pembayaran
      if ($sisa_bayar <= 0) {
        $status_pembayaran = 'Lunas';
      } else {
        $status_pembayaran = 'Belum Lunas';
      }

      // Update data pembayaran
      $query_update = "UPDATE pembayaran_guru SET jumlah_bayar = ?, sisa_bayar = ?, status_pembayaran = ? WHERE id_pembayaran = ?";
      $stmt_update = $conn->prepare($query_update);
      $stmt_update->bind_param("dsss", $jumlah_bayar_total, $sisa_bayar, $status_pembayaran, $id_pembayaran);
      $stmt_update->execute();
      $stmt_update->close();
      $cashflow = new Cashflow($conn);
      $cashflow->remove('bukti_pembayaran_guru',$id_bukti);

      // Hapus data hanya berdasarkan ID BUKTI
      $stmt_del = $conn->prepare("DELETE FROM bukti_pembayaran_guru WHERE id_bukti = ?");
      $stmt_del->bind_param("i", $id_bukti);

      if ($stmt_del->execute()) {
        echo "<script>alert('Transaksi berhasil dihapus'); window.location.href='hasil_data_pembayaran_guru.php?id_pembayaran=$id_pembayaran';</script>";
      } else {
        echo "<script>alert('Gagal menghapus data'); history.back();</script>";
      }
      $stmt_del->close();
      break;
  }
}
$query = "SELECT 
  b.id_bukti,
  b.jumlah_bayar,
  b.bukti_pembayaran,
  b.status,
  b.tanggal_bayar,
  r.id_pembayaran,
  g.nama_guru,
  r.status_pembayaran,
  r.gaji,
  paket_bimbel.paket AS paket
FROM pembayaran_guru r
LEFT JOIN bukti_pembayaran_guru b ON r.id_pembayaran = b.id_pembayaran
LEFT JOIN paket_bimbel ON r.id_paket = paket_bimbel.id_paket
LEFT JOIN guru g ON r.id_guru = g.id_guru
WHERE r.id_pembayaran = ?
ORDER BY b.tanggal_bayar DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $id_pembayaran);
$stmt->execute();
$data_pembayaran = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Bukti Pembayaran Guru</title>
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
      <h1>Bukti Pembayaran</h1>
    </div>

    <div class="card p-5 mb-5">
      <table class="table">
        <thead>
          <tr>
            <th>No</th>
            <th>Tanggal Bayar</th>
            <th>Jumlah Bayar</th>
            <th>Bukti Transfer</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>

          <?php
          $i = 0;
          while ($row = $data_pembayaran->fetch_assoc()) {
            if ($row['id_bukti']) {
              $i++;
              $bukti = $row['bukti_pembayaran'] ?? '';
              $ext = pathinfo($bukti, PATHINFO_EXTENSION);
              $is_image = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
          ?>
              <tr>
                <td><?= $i ?></td>
                <td><?= date('d M Y H:i:s', strtotime($row['tanggal_bayar'])) ?></td>
                <td><?= number_format($row['jumlah_bayar'], 2, ',', '.') ?></td>
                <td>
                  <?php if ($is_image && !empty($bukti)): ?>
                    <a href="<?= $bukti ?>" target="_blank">
                      <img src="<?= $bukti ?>" class="img-thumbnail" style="max-width: 150px;" />
                    </a>
                  <?php elseif (!empty($bukti)): ?>
                    <a href="<?= $bukti ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                      Lihat Dokumen (<?= strtoupper($ext) ?>)
                    </a>
                  <?php else: ?>
                    <span class="text-muted">Tidak ada file</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                  if ($row['status'] == 0) {
                  ?>
                    <form action="bukti_pembayaran_guru.php?id_pembayaran=<?= $row['id_pembayaran'] ?>" method="POST">
                      <input type="hidden" name="id_bukti" value="<?= $row['id_bukti'] ?>" />
                      <input type="hidden" name="params" value="konfrim" />
                      <input type="hidden" name="id_pembayaran" value="<?= $row['id_pembayaran'] ?>" />
                      <button type="submit"
                        onclick="return confirm('Konfirm bukti pembayaran ini?')"
                        class="btn btn-success btn-sm">Konfirmasi</button>
                    </form>
                  <?php
                  } else {
                  ?>
                    <?php if (isset($row['id_bukti']) && $row['id_bukti'] != ''): ?>
                      <form action="bukti_pembayaran_guru.php?id_pembayaran=<?= $row['id_pembayaran'] ?>" method="POST">
                        <input type="hidden" name="id_bukti" value="<?= $row['id_bukti'] ?>" />
                        <input type="hidden" name="params" value="delete" />
                        <input type="hidden" name="id_pembayaran" value="<?= $row['id_pembayaran'] ?>" />
                        <button type="submit"
                          onclick="return confirm('Hapus bukti pembayaran ini?')"
                          class="btn btn-danger btn-sm">Hapus</button>
                      </form>

                    <?php else: ?>
                      <span class="text-muted">Tidak bisa hapus</span>
                    <?php endif; ?>
                  <?php
                  }

                  ?>


                </td>
              </tr>
          <?php
            }
          } ?>
        </tbody>
      </table>
    </div>
  </main>
  <?= require('layouts/footer.php'); ?>
</body>

</html>