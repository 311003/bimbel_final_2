<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

$id_pembayaran = $_GET['id_pembayaran'] ?? '';

$query = "SELECT 
  b.id_bukti,
  b.jumlah_bayar,
  b.bukti_pembayaran,
  b.tanggal_bayar,
  r.id_pembayaran,
  r.nama,
  r.status_pembayaran,
  r.paket,
  r.biaya,
  paket_bimbel.paket AS paket
FROM pembayaran r
LEFT JOIN bukti_pembayaran b ON r.id_pembayaran = b.id_pembayaran
LEFT JOIN paket_bimbel ON r.id_paket = paket_bimbel.id_paket
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

  <title>Dashboard - Murid</title>
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
          <?php if (isset($row['id_bukti']) && $row['id_bukti'] != ''): ?>
              <a href="delete_pembayaran_murid.php?id=<?= $row['id_bukti'] ?>&id_pembayaran=<?= $row['id_pembayaran'] ?>"
                 onclick="return confirm('Yakin ingin menghapus bukti pembayaran ini?')"
                 class="btn btn-danger btn-sm">Hapus</a>
            <?php else: ?>
              <span class="text-muted">Tidak bisa hapus</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
</main>

    </table>
  </div>
</main>
<?= require('layouts/footer.php');?>
</body>
</html>
