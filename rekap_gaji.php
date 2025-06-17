<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();

$role=isset($_SESSION['role'])??null;
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');
$guru = $_GET['guru'] ?? null;
if($_SESSION['role'] != 1){
    $guru = $_SESSION['id_ref'];
}
$status = $_GET['status'] ?? null;

$query_sql = "
    SELECT 
        pg.tanggal_bayar,
        pg.gaji,
        pg.jumlah_bayar AS total_bayar,
        pg.sisa_bayar,
        pg.status_pembayaran AS status,
        pb.paket,
        g.nama_guru
    FROM pembayaran_guru pg
    LEFT JOIN guru g ON pg.id_guru = g.id_guru
    LEFT JOIN paket_bimbel pb ON pg.id_paket = pb.id_paket
    WHERE MONTH(pg.tanggal_bayar) = ? AND YEAR(pg.tanggal_bayar) = ?
";

$params = [$bulan, $tahun];
$types = "ii";

// Tambah filter guru
if ($guru) {
    $query_sql .= " AND pg.id_guru = ?";
    $params[] = $guru;
    $types .= "i";
}

// Tambah filter status
if ($status) {
    $query_sql .= " AND pg.status_pembayaran = ?";
    $params[] = $status;
    $types .= "s";
}

$query_sql .= " ORDER BY pg.tanggal_bayar ASC";
$query = $conn->prepare($query_sql);
$query->bind_param($types, ...$params);
$query->execute();
$result = $query->get_result();

$query_guru = "SELECT id_guru, nama_guru FROM guru";
$result_guru = $conn->query($query_guru);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Laporan Rekap Gaji</title>
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
        require('layouts/sidemenu_guru.php');
    } ?>

</body>
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Data Gaji Bulan <?= date('F', mktime(0, 0, 0, $bulan, 1)) . " $tahun" ?></h1>
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
                if($role==1){
                    ?>
                        <div class="mb-3">
                            <label for="tahun" class="form-label">Guru</label>
                            <div class="guru-entry mb-2 d-flex align-items-center">
                                <select class="form-control me-2" name="guru" >
                                    <option value="">-- Pilih Guru --</option>
                                    <?php while ($row = $result_guru->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($row['id_guru']) ?>" <?= $row['id_guru'] == $guru ? 'selected' : '' ?> data-nama="<?= htmlspecialchars($row['nama_guru']) ?>">
                                            <?= htmlspecialchars($row['id_guru']) ?> - <?= htmlspecialchars($row['nama_guru']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    <?php
                }
                ?>
                

                <div class="mb-3">
                    <label for="tahun" class="form-label">Status Pembayaran</label>
                    <select class="form-control me-2" name="status" >
                        <option value>-- Pilih Status --</option>
                       <option value="Lunas" <?= $status == 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                       <option value="Belum Lunas" <?= $status == 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas</option>
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
                <th>Nama Guru</th>
                <th>Tanggal</th>
                <th>Paket</th>
                <th>Total gaji</th>
                <th>Total Bayar</th>
                <th>Sisa Bayar</th>
                <th>Status</th>
            </tr>
            <?php
            $no = 1;
            $total_bayar = $sisa_bayar = 0;
            while ($row = $result->fetch_assoc()):
                $total_bayar += $row['total_bayar'];
                $sisa_bayar += $row['sisa_bayar'];
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nama_guru']) ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tanggal_bayar'])) ?></td>
                    <td><?= $row['paket'] ?></td>
                    <td align="right"><?= number_format($row['gaji']) ?></td>
                    <td align="right"><?= number_format($row['total_bayar'], 2) ?></td>
                    <td align="right"><?= number_format($row['sisa_bayar'], 2) ?></td>
                    <td align="right"><?= $row['status'] ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <th colspan="4" align="right">Total</th>
                <th align="right"><?= number_format($total_bayar, 2) ?></th>
                <th align="right"><?= number_format($sisa_bayar, 2) ?></th>
            </tr>
        </table>

    </div>
    <script>
        document.getElementById('exportExcel').addEventListener('click', function () {
            const bulan = document.querySelector('select[name="bulan"]').value;
            const tahun = document.querySelector('input[name="tahun"]').value;
            const guru = document.querySelector('select[name="guru"]')?.value||'';
            const status = document.querySelector('select[name="status"]').value;
            const url = `export/excel/rekap_gaji.php?bulan=${bulan}&tahun=${tahun}&guru=${guru}&status=${status}`;
            window.location.href = url;
        });

        document.getElementById('exportPdf').addEventListener('click', function () {
            const bulan = document.querySelector('select[name="bulan"]').value;
            const tahun = document.querySelector('input[name="tahun"]').value;
             const guru = document.querySelector('select[name="guru"]')?.value||'';
            const status = document.querySelector('select[name="status"]').value;
            const url = `export/pdf/rekap_gaji.php?bulan=${bulan}&tahun=${tahun}&guru=${guru}&status=${status}`;
            window.open(url, '_blank');
        });
    </script>      
</main>
<?= require('layouts/footer.php'); ?>
</body>

</html>