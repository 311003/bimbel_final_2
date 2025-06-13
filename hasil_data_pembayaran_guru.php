<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();

// Handling form submission for adding new payment data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $id_pembayaran = $_POST['id_pembayaran'];
    $jumlah_bayar = $_POST['jumlah_bayar'];

    // Query untuk mengambil data pembayaran yang sudah ada (biaya, jumlah_bayar lama, sisa_biaya, status_pembayaran)
    $query = "SELECT biaya, jumlah_bayar, sisa_biaya, status_pembayaran FROM pembayaran WHERE id_pembayaran = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id_pembayaran);
    $stmt->execute();
    $stmt->bind_result($biaya, $jumlah_bayar_lama, $sisa_biaya, $status_pembayaran);
    $stmt->fetch();
    $stmt->close();

    // Menghitung jumlah_bayar baru dan sisa_biaya
    $jumlah_bayar_total = $jumlah_bayar_lama + $jumlah_bayar;
    $sisa_biaya = $biaya - $jumlah_bayar_total;

    // Tentukan status pembayaran
    if ($sisa_biaya <= 0) {
        $status_pembayaran = 'Lunas';
    } else {
        $status_pembayaran = 'Belum Lunas';
    }

    // Update data pembayaran
    $query_update = "UPDATE pembayaran SET jumlah_bayar = ?, sisa_biaya = ?, status_pembayaran = ? WHERE id_pembayaran = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("dsss", $jumlah_bayar_total, $sisa_biaya, $status_pembayaran, $id_pembayaran);

    if ($stmt_update->execute()) {
        echo "<script>alert('Data pembayaran berhasil diperbarui!');</script>";
    } else {
        echo "<script>alert('Error memperbarui data pembayaran: " . $conn->error . "');</script>";
    }
    $stmt_update->close();

    if (!empty($data_pembayaran['bukti_pembayaran'])): ?>
        <div class="mt-2">
            <label>Bukti Pembayaran Saat Ini:</label><br>
            <?php if (in_array(pathinfo($data_pembayaran['bukti_pembayaran'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])): ?>
                <img src="<?= htmlspecialchars($data_pembayaran['bukti_pembayaran']) ?>" alt="Bukti Pembayaran" width="200px">
            <?php else: ?>
                <a href="<?= htmlspecialchars($data_pembayaran['bukti_pembayaran']) ?>" target="_blank">Lihat Bukti Pembayaran (PDF)</a>
            <?php endif; ?>
        </div>
<?php endif;
}

// Sinkronisasi data pembayaran sebelum ditampilkan
$update_status_sql = "
UPDATE pembayaran_guru r
LEFT JOIN (
    SELECT 
        id_pembayaran, 
        COALESCE(SUM(jumlah_bayar), 0) AS total_bayar
    FROM bukti_pembayaran_guru
    GROUP BY id_pembayaran
) b ON r.id_pembayaran = b.id_pembayaran
SET 
    r.jumlah_bayar = b.total_bayar,
    r.sisa_bayar = r.gaji - b.total_bayar,
    r.status_pembayaran = CASE
        WHEN b.total_bayar >= r.gaji THEN 'Lunas'
        ELSE 'Belum Lunas'
    END
";

$conn->query($update_status_sql);

// Fetch the validated students' payment data
// SELECT pembayaran.*, sum(bukti_pembayaran.jumlah_bayar) as total_bayar FROM `pembayaran` left join bukti_pembayaran on pembayaran.id_pembayaran = bukti_pembayaran.id_pembayaran group by pembayaran.id_pembayaran;
$query_pembayaran = "
SELECT 
    r.id_pembayaran,
    r.id_paket, 
    p.paket AS nama_paket, 
    r.gaji, 
    r.id_guru, 
    g.nama_guru AS nama,
    -- Hitung jumlah bayar meskipun belum ada data
    COALESCE(SUM(b.jumlah_bayar), 0) AS jumlah_bayar,
    -- Hitung sisa bayar
    (r.gaji - COALESCE(SUM(b.jumlah_bayar), 0)) AS sisa_bayar,
    -- Status otomatis tergantung sisa biaya
    CASE 
        WHEN COALESCE(SUM(b.jumlah_bayar), 0) >= r.gaji THEN 'Lunas'
        ELSE 'Belum Lunas'
    END AS status_pembayaran
FROM pembayaran_guru r
LEFT JOIN bukti_pembayaran_guru b ON b.id_pembayaran = r.id_pembayaran 
LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
LEFT JOIN guru g ON r.id_guru = g.id_guru
WHERE g.id_status_guru =1
GROUP BY 
    r.id_pembayaran,
    r.id_paket,
    p.paket,
    r.gaji,
    r.id_guru,
    g.nama_guru
";

$result_pembayaran = $conn->query($query_pembayaran);

if (!$result_pembayaran) {
    echo "<script>alert('Error fetching payment data: " . $conn->error . "');</script>";
}
?>


<?php
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Hasil Data Pembayaran Guru</title>
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
            <h2 class="text-center">Hasil Data Pembayaran Guru</h2>

            <h4 class="mt-4 text-success">✅ Gaji Guru</h4>
            <table id="viewTable" class="table table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>No Presensi</th>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Gaji</th>
                        <th>Jumlah Bayar</th>
                        <th>Sisa Bayar</th>
                        <th>Status Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_pembayaran->fetch_assoc()) {
                        $check = "
                         SELECT 
                             COUNT(*) as total
                         FROM bukti_pembayaran_guru
                         LEFT JOIN pembayaran_guru ON pembayaran_guru.id_pembayaran = bukti_pembayaran_guru.id_pembayaran 
                         WHERE bukti_pembayaran_guru.status = 0 AND  pembayaran_guru.id_pembayaran=" . $row['id_pembayaran'];

                        $check = $conn->query($check);
                        $total = 0;
                        if ($check) {
                            $total = $check->fetch_assoc()['total'];
                        }


                    ?>
                        <tr>
                            <td><?= isset($row['id_pembayaran']) ? htmlspecialchars($row['id_pembayaran']) : 'N/A' ?></td>
                            <td><?= isset($row['nama']) ? htmlspecialchars($row['nama']) : 'Unknown' ?></td>
                            <td><?= isset($row['nama_paket']) ? htmlspecialchars($row['nama_paket']) : 'Unknown' ?></td>
                            <td>Rp <?= isset($row['gaji']) ? number_format($row['gaji'], 2, ',', '.') : '0,00' ?></td>
                            <td>Rp <?= isset($row['jumlah_bayar']) ? number_format($row['jumlah_bayar'], 2, ',', '.') : '0,00' ?></td>
                            <td>Rp <?= isset($row['sisa_bayar']) ? number_format($row['sisa_bayar'], 2, ',', '.') : '0,00' ?></td>
                            <td>
                                <?php

                                if ($total > 0) {
                                ?>
                                    <span class="badge bg-info">Menunngu Konfirmasi</span>
                                <?php
                                } else {
                                ?>
                                    <?php if (isset($row['sisa_bayar']) && $row['sisa_bayar'] > 0): ?>
                                        <span class="badge bg-warning">Belum Lunas</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Lunas</span>
                                    <?php endif; ?>

                                <?php
                                }
                                ?>

                            </td>
                            <td>
                                <?php
                                if ((intval($row['jumlah_bayar']) < intval($row['gaji'])) || (intval($row['sisa_bayar']) > 0)) {
                                ?>
                                    <a href="verifikasi_pembayaran_guru.php?id_pembayaran=<?= isset($row['id_pembayaran']) ? $row['id_pembayaran'] : '' ?>" class="btn btn-sm btn-warning">Verifikasi
                                    <?php
                                }
                                    ?>
                                    <a href="bukti_pembayaran_guru.php?id_pembayaran=<?= isset($row['id_pembayaran']) ? $row['id_pembayaran'] : '' ?>" class="btn btn-sm btn-warning">Bukti Bayar
                            </td>
                        </tr>
                    <?php } ?>

                </tbody>
            </table>
        </div>
    </main>
    <?= require('layouts/footer.php') ?>
    <script>
        let table = new DataTable('#viewTable', {
            // options
            // lengthMenu: [
            //     [20, 30, 40, -1],
            //     [20, 30, 40, 'All']
            // ]
        });
    </script>
</body>

</html>