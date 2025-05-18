<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara

// Jika tombol validasi ditekan
if (isset($_POST['validasi'])) {
    $no_reg = $_POST['no_reg'];

    // Ambil id_paket dan biaya dari registrasi_murid dan paket_bimbel
    $query_select = "SELECT r.id_murid, r.nama, r.id_paket, p.biaya 
                     FROM registrasi_murid r
                     LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
                     WHERE r.no_reg = ?";
    $stmt_select = $conn->prepare($query_select);
    $stmt_select->bind_param("s", $no_reg);
    $stmt_select->execute();
    $stmt_select->bind_result($id_murid, $nama, $id_paket, $biaya);
    $stmt_select->fetch();
    $stmt_select->close();

    // Debugging: Cek apakah id_murid, id_paket, dan biaya valid
    if (empty($id_murid) || empty($id_paket) || empty($biaya)) {
        echo "<script>alert('Data tidak lengkap: ID Murid, ID Paket, atau Biaya tidak ditemukan!'); window.history.back();</script>";
        exit();
    }

    // Update status konfirmasi registrasi
    $query_update = "UPDATE registrasi_murid SET konfirmasi_registrasi = 'Divalidasi' WHERE no_reg = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("s", $no_reg);
    if (!$stmt_update->execute()) {
        echo "<script>alert('Error memperbarui status validasi: " . $conn->error . "'); window.history.back();</script>";
        exit();
    }

    // Debugging: Menampilkan nilai parameter
    echo "no_reg: $no_reg, id_murid: $id_murid, nama: $nama, id_paket: $id_paket, paket: $paket, biaya: $biaya";

    // Insert data pembayaran
    $query_insert = "INSERT INTO pembayaran 
                 (no_reg, id_murid, nama, id_paket, paket, biaya, jumlah_bayar, sisa_biaya, status_pembayaran, input_pembayaran) 
                 VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'Belum Lunas', NOW())";

    // Persiapkan dan bind parameter
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param("sssssds", $no_reg, $id_murid, $nama, $id_paket, $paket, $biaya, $biaya);

    // Eksekusi query
    if (!$stmt_insert->execute()) {
        echo "<script>alert('Error menambahkan pembayaran: " . $stmt_insert->error . "'); window.history.back();</script>";
        exit();
    } else {
        echo "<script>alert('Murid berhasil divalidasi dan data pembayaran berhasil ditambahkan!'); window.location.href='view_konfirmasi_registrasi.php';</script>";
        echo "<script>alert('Murid berhasil divalidasi dan data pembayaran berhasil ditambahkan!'); window.location.href='hasil_data_pembayaran.php';</script>";
    }

    $stmt_insert->close();
}

// Jika tombol batalkan ditekan
if (isset($_POST['batalkan'])) {
    $no_reg = $_POST['no_reg'];
    $query_delete = "DELETE FROM registrasi_murid WHERE no_reg = ?";
    $stmt = $conn->prepare($query_delete);
    $stmt->bind_param("s", $no_reg);
    $stmt->execute();
    echo "<script>alert('Murid berhasil dibatalkan!'); window.location.href='view_konfirmasi_registrasi.php';</script>";
    exit();
}

// Ambil semua data registrasi murid dari database dengan nama paket
$query = "SELECT r.*, p.paket 
          FROM registrasi_murid r
          LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket";
$result = $conn->query($query);

// Ambil data murid yang ikut bimbel (konfirmasi_registrasi = 'Divalidasi')
$query_valid = "SELECT * FROM registrasi_murid WHERE konfirmasi_registrasi = 'Divalidasi'";
$result_valid = $conn->query($query_valid);

// Ambil data murid yang sudah dibatalkan
$query_batal = "SELECT r.*, p.paket 
                FROM registrasi_murid r
                LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
                WHERE r.konfirmasi_registrasi = 'Dibatalkan'";
$result_batal = $conn->query($query_batal);

// Tutup koneksi setelah semua query selesai
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Konfirmasi Registrasi</title>
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
    <link href="assets/vendor/DataTables/datatables.min.css" rel="stylesheet">

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
            <h4 class="mt-4">📌 Konfirmasi Data Registrasi Murid</h4>
            <table id="viewTable" class=" table table-bordered">
                <thead class="table-primary">
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
                        <th>Konfirmasi Registrasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
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
                            <td>
                                <?php
                                if ($row['konfirmasi_registrasi'] == 'Divalidasi') {
                                    echo "<span class='badge bg-success'>Divalidasi</span>";
                                } elseif ($row['konfirmasi_registrasi'] == 'Dibatalkan') {
                                    echo "<span class='badge bg-danger'>Dibatalkan</span>";
                                } else {
                                    echo "<span class='badge bg-secondary'>Belum Diproses</span>";
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($row['konfirmasi_registrasi'] != 'Divalidasi') { ?>
                                    <a href="#" onclick="konfirmasiValidasi('<?= urlencode($row['no_reg']) ?>')" class="btn btn-success btn-sm">
                                        Validasi
                                    </a>
                                <?php } ?>
                                <?php if ($row['konfirmasi_registrasi'] != 'Dibatalkan') { ?>
                                    <a href="#" onclick="konfirmasiBatalkan('<?= urlencode($row['no_reg']) ?>')" class="btn btn-danger btn-sm">
                                        Batalkan
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <?= require('layouts/footer.php'); ?>
    <script>
        let table = new DataTable('#viewTable', {
            // options
            // lengthMenu: [
            //     [20, 30, 40, -1],
            //     [20, 30, 40, 'All']
            // ]
        });

        function konfirmasiValidasi(no_reg) {
            if (confirm("Apakah Anda yakin ingin memvalidasi murid ini?")) {
                window.location.href = "registrasi_valid.php?no_reg=" + encodeURIComponent(no_reg);
            }
        }

        function konfirmasiBatalkan(no_reg) {
            if (confirm("Apakah Anda yakin ingin membatalkan pendaftaran murid ini?")) {
                window.location.href = "delete_registrasi.php?no_reg=" + encodeURIComponent(no_reg);
            }
        }

        function ubahStatus(no_reg, status) {
            if (confirm("Apakah Anda yakin ingin mengubah status murid ini menjadi " + status + "?")) {
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "update_status_murid.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert("Status berhasil diperbarui!");
                        location.reload();
                    }
                };

                xhr.send("no_reg=" + encodeURIComponent(no_reg) + "&konfirmasi_registrasi=" + encodeURIComponent(status));
            }
        }
    </script>
</body>

</html>