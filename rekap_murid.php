<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();

$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$murid = $_GET['murid'] ?? null;
$status = $_GET['status'] ?? null;
if ($role != 1) {
    $murid = $_SESSION['id_ref'];
    $status = null;
}
$status_bayar = $_GET['status_bayar'] ?? null;

$query_sql = "
    SELECT 
        pm.tanggal_bayar,
        pm.biaya,
        pm.jumlah_bayar AS total_biaya,
        pm.sisa_biaya,
        pm.status_pembayaran AS status,
        pb.paket,
        m.nama
    FROM pembayaran pm
    LEFT JOIN master_murid m ON pm.id_murid = m.id_murid
    LEFT JOIN paket_bimbel pb ON pm.id_paket = pb.id_paket
    WHERE MONTH(pm.tanggal_bayar) = ? AND YEAR(pm.tanggal_bayar) = ?
";

$params = [$bulan, $tahun];
$types = "ii";

// Tambah filter guru
if ($murid) {
    $query_sql .= " AND pm.id_murid = ?";
    $params[] = $murid;
    $types .= "i";
}

// Tambah filter status murid
if ($status) {
    $query_sql .= " AND m.status_murid = ?";
    $params[] = $status;
    $types .= "s";
}


// Tambah filter status
if ($status_bayar) {
    $query_sql .= " AND pm.status_pembayaran = ?";
    $params[] = $status_bayar;
    $types .= "s";
}

$query_sql .= " ORDER BY pm.tanggal_bayar ASC";
$query = $conn->prepare($query_sql);
$query->bind_param($types, ...$params);
$query->execute();
$result = $query->get_result();

$query_murid = "SELECT id_murid, nama FROM master_murid";
$result_murid = $conn->query($query_murid);

$query_status = "SELECT * FROM status_murid";
$status_murid = $conn->query($query_status);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Laporan Rekap Murid</title>
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
    <?php if ($role == 1) {
        require('layouts/sidemenu_owner.php');
    } else {
        require('layouts/sidemenu_murid.php');
    } ?>

</body>
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Data Rekap Murid Bulan <?= date('F', mktime(0, 0, 0, $bulan, 1)) . " $tahun" ?></h1>
    </div><!-- End Page Title -->

    <div class="card p-5 mb-5">
        <form method="get">
            <div class="d-flex gap-5">
                <div class="mb-3">
                    <label for="bulan" class="form-label">Bulan</label>
                    <select class="form-select" name="bulan">
                        <?php for ($b = 1; $b <= 12; $b++): ?>
                            <option value="<?= $b ?>" <?= $b == $bulan ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $b, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="tahun" class="form-label">Bulan</label>
                    <input class="form-control" type="number" name="tahun" value="<?= $tahun ?>" />
                </div>
                <?php
                if ($role == 1) {
                ?>
                    <div class="mb-3">
                        <label for="tahun" class="form-label">Murid</label>
                        <div class="murid-entry mb-2 d-flex align-items-center">
                            <select class="form-control me-2" name="murid">
                                <option value="">-- Pilih Murid --</option>
                                <?php while ($row = $result_murid->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row['id_murid']) ?>" <?= $row['id_murid'] == $murid ? 'selected' : '' ?> data-nama="<?= htmlspecialchars($row['nama']) ?>">
                                        <?= htmlspecialchars($row['id_murid']) ?> - <?= htmlspecialchars($row['nama']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="tahun" class="form-label">Status Murid</label>
                        <select class="form-control me-2" name="status">
                            <option value="">-- Pilih Status --</option>
                            <?php while ($row = $status_murid->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['id_status_murid']) ?>" <?= $row['id_status_murid'] == $status ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['status_murid']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php
                }
                ?>


                <div class="mb-3">
                    <label for="tahun" class="form-label">Status Pembayaran</label>
                    <select class="form-control me-2" name="status_bayar">
                        <option value>-- Pilih Status --</option>
                        <option value="Lunas" <?= $status_bayar == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                        <option value="Belum Lunas" <?= $status_bayar == 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                    </select>
                </div>

            </div>
            <br>
            <div class="d-flex mb-5 gap-5">
                <button type="submit" class="btn btn-primary">Filter</button>
                <button type="button" id="exportExcel" class="btn btn-primary">📥 Export ke Excel</button>
                <button type="button" id="exportPdf" class="btn btn-primary">📄 Export ke PDF</button>
            </div>
        </form>
        <table class="table" border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>No</th>
                <th>Nama Murid</th>
                <th>Tanggal</th>
                <th>Paket</th>
                <th>Total Biaya</th>
                <th>Total Bayar</th>
                <th>Sisa Bayar</th>
                <th>Status</th>
            </tr>
            <?php
            $no = 1;
            $total_biaya = $sisa_biaya = 0;
            while ($row = $result->fetch_assoc()):
                $total_biaya += $row['total_biaya'];
                $sisa_biaya += $row['sisa_biaya'];
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tanggal_bayar'])) ?></td>
                    <td><?= $row['paket'] ?></td>
                    <td align="right"><?= number_format($row['biaya']) ?></td>
                    <td align="right"><?= number_format($row['total_biaya'], 2) ?></td>
                    <td align="right"><?= number_format($row['sisa_biaya'], 2) ?></td>
                    <td align="right"><?= $row['status'] ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <th colspan="4" align="right">Total</th>
                <th align="right"><?= number_format($total_biaya, 2) ?></th>
                <th align="right"><?= number_format($sisa_biaya, 2) ?></th>
            </tr>
        </table>

    </div>
    <script>
        document.getElementById('exportExcel').addEventListener('click', function() {
            const bulan = document.querySelector('select[name="bulan"]').value;
            const tahun = document.querySelector('input[name="tahun"]').value;
            const murid = document.querySelector('select[name="murid"]')?.value || '';
            const status = document.querySelector('select[name="status"]')?.value || '';
            const status_bayar = document.querySelector('select[name="status_bayar"]').value;
            const url = `export/excel/rekap_murid.php?bulan=${bulan}&tahun=${tahun}&murid=${murid}&status=${status}&status_bayar=${status_bayar}`;
            window.location.href = url;
        });

        document.getElementById('exportPdf').addEventListener('click', function() {
            const bulan = document.querySelector('select[name="bulan"]').value;
            const tahun = document.querySelector('input[name="tahun"]').value;
            const murid = document.querySelector('select[name="murid"]')?.value || '';
            const status = document.querySelector('select[name="status"]')?.value || '';
            const status_bayar = document.querySelector('select[name="status_bayar"]').value;
            const url = `export/pdf/rekap_murid.php?bulan=${bulan}&tahun=${tahun}&murid=${murid}&status=${status}&status_bayar=${status_bayar}`;
            window.open(url, '_blank');
        });
    </script>
</main>
<?= require('layouts/footer.php'); ?>
</body>

</html>