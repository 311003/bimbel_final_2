<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();

// If no_reg and konfirmasi are passed via GET (for registration confirmation)
if (isset($_GET['no_reg']) && isset($_GET['konfirmasi'])) {
    $no_reg = $_GET['no_reg'];
    $konfirmasi = ($_GET['konfirmasi'] == 'valid') ? 'Divalidasi' : 'Belum Divalidasi';

    // Update konfirmasi_registrasi based on the user's selection
    $query = "UPDATE registrasi_murid SET konfirmasi_registrasi = ? WHERE no_reg = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $konfirmasi, $no_reg);
    $stmt->execute();
    $stmt->close();
}

// Query to display payment data
$query_pembayaran = "SELECT 
                        r.id_pembayaran,
                        r.id_paket, 
                        p.paket AS nama_paket, 
                        p.biaya, 
                        r.id_murid, 
                        m.nama AS nama_murid,
                        r.status_pembayaran,
                        COALESCE(r.jumlah_bayar, 0) AS jumlah_bayar,  
                        (p.biaya - COALESCE(r.jumlah_bayar, 0)) AS sisa_biaya
                     FROM pembayaran r
                     LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
                     LEFT JOIN master_murid m ON r.id_murid = m.id_murid
                     LEFT JOIN registrasi_murid reg ON r.id_murid = reg.no_reg
                     WHERE reg.konfirmasi_registrasi IS NOT NULL";

$result_pembayaran = $conn->query($query_pembayaran);

// Process edit payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_pembayaran'])) {
    $id_pembayaran = $_POST['id_pembayaran'];
    $jumlah_bayar = $_POST['jumlah_bayar'];

    // Update payment amount and calculate new status
    $query_update = "UPDATE pembayaran SET jumlah_bayar = ?, status_pembayaran = CASE
                        WHEN (biaya - ?) <= 0 THEN 'Lunas'
                        ELSE 'Belum Lunas'
                     END WHERE id_pembayaran = ?";
    $stmt = $conn->prepare($query_update);
    $stmt->bind_param("dii", $jumlah_bayar, $jumlah_bayar, $id_pembayaran);
    $stmt->execute();
    $stmt->close();

    header("Location: hasil_data_pembayaran.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Edit Pembayaranr</title>
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
        <div class="container mt-4">
            <h2 class="text-center">Hasil Data Pembayaran</h2>

            <h4 class="mt-4 text-success">✅ Murid yang Divalidasi</h4>
            <table class="table table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>No Registrasi</th>
                        <th>Nama</th>
                        <th>Paket</th>
                        <th>Biaya</th>
                        <th>Sudah Dibayar</th>
                        <th>Sisa Biaya</th>
                        <th>Status</th>
                        <th>Edit Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_pembayaran->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id_pembayaran']) ?></td>
                            <td><?= htmlspecialchars($row['nama_murid']) ?></td>
                            <td><?= htmlspecialchars($row['nama_paket']) ?></td>
                            <td>Rp <?= number_format($row['biaya'], 2, ',', '.') ?></td>
                            <td>Rp <?= number_format($row['jumlah_bayar'], 2, ',', '.') ?></td>
                            <td>Rp <?= number_format($row['sisa_biaya'], 2, ',', '.') ?></td>
                            <td>
                                <?php if ($row['sisa_biaya'] > 0): ?>
                                    <span class="badge bg-warning">Belum Lunas</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="" method="POST">
                                    <input type="hidden" name="id_pembayaran" value="<?= $row['id_pembayaran'] ?>">
                                    <input type="number" name="jumlah_bayar" class="form-control" min="0" value="<?= $row['jumlah_bayar'] ?>" required>
                                    <button type="submit" name="edit_pembayaran" class="btn btn-primary btn-sm mt-1">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
    <?= require('layouts/footer.php'); ?>
</body>

</html>
<?php
$conn->close();
?>