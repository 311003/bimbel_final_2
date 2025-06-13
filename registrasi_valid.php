<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
// echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara

if (isset($_GET['no_reg'])) {
    $no_reg = $_GET['no_reg'];

    // Ambil data murid yang perlu divalidasi
    $query_select = "SELECT r.id_murid, r.nama, r.id_paket, p.biaya, p.paket 
                     FROM registrasi_murid r
                     LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
                     WHERE r.no_reg = ?";
    $stmt_select = $conn->prepare($query_select);
    $stmt_select->bind_param("s", $no_reg);
    $stmt_select->execute();
    $stmt_select->bind_result($id_murid, $nama, $id_paket, $biaya, $paket);
    $stmt_select->fetch();
    $stmt_select->close();

    // Pastikan data valid
    if (empty($id_murid) || empty($id_paket) || empty($biaya)) {
        echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
        exit();
    }

    // Update konfirmasi_registrasi menjadi "Divalidasi"
    $query_update = "UPDATE registrasi_murid SET konfirmasi_registrasi = 'Divalidasi' WHERE no_reg = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("s", $no_reg);

    if (!$stmt_update->execute()) {
        echo "<script>alert('Terjadi kesalahan saat memvalidasi!'); window.history.back();</script>";
        exit();
    }

    $stmt_update->close();

    // Insert ke tabel pembayaran
    $query_insert = "INSERT INTO pembayaran (no_reg, id_murid, nama, id_paket, paket, biaya, jumlah_bayar, sisa_biaya, status_pembayaran, input_pembayaran, tanggal_bayar)
                 VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'Belum Lunas', NOW(), NOW())";
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param("ssssssd", $no_reg, $id_murid, $nama, $id_paket, $paket, $biaya, $biaya);

    if (!$stmt_insert->execute()) {
        echo "<script>alert('Error menambahkan pembayaran: " . $stmt_insert->error . "'); window.history.back();</script>";
        exit();
    }

    $stmt_insert->close();

    // Pastikan data yang diterima aman
    if (isset($_POST['id_murid']) && isset($_POST['id_paket'])) {
        $id_murid = $_POST['id_murid'];
        $id_paket = $_POST['id_paket'];

        // Debugging: Output the received values
        echo "ID Murid: " . $id_murid . "<br>";
        echo "ID Paket: " . $id_paket . "<br>";

        // Mulai transaksi
        $conn->begin_transaction();

        try {

            // Update paket bimbel di registrasi_valid
            $query_update_paket = "UPDATE registrasi_valid SET id_paket = ? WHERE id_murid = ?";
            $stmt_update_paket = $conn->prepare($query_update_paket);
            $stmt_update_paket->bind_param("ii", $id_paket, $id_murid);

            if ($stmt_update_paket->execute()) {
                echo "Paket berhasil diperbarui di registrasi_valid.<br>";
            } else {
                echo "Gagal memperbarui paket di registrasi_valid.<br>";
            }
            $stmt_update_paket->close();

            // Juga update data paket di master_murid jika perlu
            $query_update_master = "UPDATE master_murid SET id_paket = ? WHERE id_murid = ?";
            $stmt_update_master = $conn->prepare($query_update_master);
            $stmt_update_master->bind_param("ii", $id_paket, $id_murid);

            if ($stmt_update_master->execute()) {
                echo "Paket berhasil diperbarui di master_murid.<br>";
            } else {
                echo "Gagal memperbarui paket di master_murid.<br>";
            }
            $stmt_update_master->close();

            // Commit transaksi jika sukses
            $conn->commit();
            echo "Update sukses!<br>";
        } catch (Exception $e) {
            // Rollback jika ada error
            $conn->rollback();
            echo "Error: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "Invalid parameters!<br>";
    }

    // Redirect setelah berhasil
    echo "<script>alert('Murid berhasil divalidasi dan pembayaran berhasil ditambahkan!'); window.location.href='view_konfirmasi_registrasi.php';</script>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Registrasi Valid</title>
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
            <h2 class="text-center">Konfirmasi Data Registrasi Murid</h2>

            <!-- ✅ Tabel Murid yang Jadi Ikut Bimbel -->
            <h4 class="mt-4 text-success">✅ Murid yang Jadi Ikut Bimbel</h4>
            <table class="table table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>No Registrasi</th>
                        <th>Nama</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Kelas</th>
                        <th>Asal Sekolah</th>
                        <th>Jenis Kelamin</th>
                        <th>No Telepon</th>
                        <th>Paket</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_valid->fetch_assoc()) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['no_reg']) ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['tanggal_lahir']) ?></td>
                            <td><?= htmlspecialchars($row['alamat']) ?></td>
                            <td><?= htmlspecialchars($row['kelas']) ?></td>
                            <td><?= htmlspecialchars($row['asal_sekolah']) ?></td>
                            <td><?= ($row['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan' ?></td>
                            <td><?= htmlspecialchars($row['no_telp']) ?></td>
                            <td><?= !empty($row['paket']) ? htmlspecialchars($row['paket']) : '<i>Tidak Ditemukan</i>' ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
    <?= require('layouts/footer.php'); ?>
</body>

</html>