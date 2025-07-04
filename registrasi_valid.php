<?php
include 'connection.php';
session_start();

if (isset($_GET['no_reg'])) {
    $no_reg = $_GET['no_reg'];

    // Ambil data murid
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

    if (empty($id_murid) || empty($id_paket) || empty($biaya)) {
        echo "<script>alert('Data tidak lengkap!'); window.history.back();</script>";
        exit();
    }

    // ✅ Cek apakah sudah pernah divalidasi
    $cek = $conn->prepare("SELECT no_reg FROM pembayaran WHERE no_reg = ?");
    $cek->bind_param("s", $no_reg);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        // Jika sudah pernah divalidasi
        echo "<script>alert('Murid ini sudah pernah divalidasi sebelumnya!'); window.location.href='view_konfirmasi_registrasi.php';</script>";
        exit();
    }
    $cek->close();

    // Update status konfirmasi
    $query_update = "UPDATE registrasi_murid SET konfirmasi_registrasi = 'Divalidasi' WHERE no_reg = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("s", $no_reg);
    $stmt_update->execute();
    $stmt_update->close();

    // Simpan data ke tabel pembayaran
    $sisa_biaya = $biaya; // karena jumlah_bayar = 0
    $query_insert = "INSERT INTO pembayaran 
    (no_reg, id_murid, nama, id_paket, paket, biaya, jumlah_bayar, sisa_biaya, status_pembayaran, input_pembayaran, tanggal_bayar)
    VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'Belum Lunas', NOW(), NOW())";

    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param("ssssssd", $no_reg, $id_murid, $nama, $id_paket, $paket, $biaya, $sisa_biaya);

    if (!$stmt_insert->execute()) {
        echo "<script>alert('Error menambahkan pembayaran: " . $stmt_insert->error . "'); window.history.back();</script>";
        exit();
    }
    $stmt_insert->close();

    // Perbarui id_paket ke tabel lain jika ada POST (opsional)
    if (isset($_POST['id_murid']) && isset($_POST['id_paket'])) {
        $id_murid_post = $_POST['id_murid'];
        $id_paket_post = $_POST['id_paket'];

        $conn->begin_transaction();
        try {
            $stmt_update1 = $conn->prepare("UPDATE registrasi_valid SET id_paket = ? WHERE id_murid = ?");
            $stmt_update1->bind_param("ii", $id_paket_post, $id_murid_post);
            $stmt_update1->execute();
            $stmt_update1->close();

            $stmt_update2 = $conn->prepare("UPDATE master_murid SET id_paket = ? WHERE id_murid = ?");
            $stmt_update2->bind_param("ii", $id_paket_post, $id_murid_post);
            $stmt_update2->execute();
            $stmt_update2->close();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
        }
    }

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