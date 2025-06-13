<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$query = $conn->prepare("SELECT * FROM cashflow WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? ORDER BY tanggal ASC");
$query->bind_param("ii", $bulan, $tahun);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Laporan Cashflow</title>
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

</body>
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Data Cashflow Bulan <?= date('F', mktime(0, 0, 0, $bulan, 1)) . " $tahun" ?></h1>
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
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Keterangan</th>
                <th>Debet</th>
                <th>Kredit</th>
            </tr>
            <?php
            $no = 1;
            $total_debet = $total_kredit = 0;
            while ($row = $result->fetch_assoc()):
                $total_debet += $row['debet'];
                $total_kredit += $row['kredit'];
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                    <td><?= $row['tipe'] ?></td>
                    <td><?= $row['keterangan'] ?></td>
                    <td align="right"><?= number_format($row['debet'], 2) ?></td>
                    <td align="right"><?= number_format($row['kredit'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <th colspan="4" align="right">Total</th>
                <th align="right"><?= number_format($total_debet, 2) ?></th>
                <th align="right"><?= number_format($total_kredit, 2) ?></th>
            </tr>
        </table>

    </div>
    <script>
        document.getElementById('exportExcel').addEventListener('click', function () {
            const bulan = document.querySelector('select[name="bulan"]').value;
            const tahun = document.querySelector('input[name="tahun"]').value;
            const url = `export/excel/cashflow.php?bulan=${bulan}&tahun=${tahun}`;
            window.location.href = url;
        });

        document.getElementById('exportPdf').addEventListener('click', function () {
            const bulan = document.querySelector('select[name="bulan"]').value;
            const tahun = document.querySelector('input[name="tahun"]').value;
            const url = `export/pdf/cashflow.php?bulan=${bulan}&tahun=${tahun}`;
            window.open(url, '_blank');
        });
    </script>      
</main>
<?= require('layouts/footer.php'); ?>
</body>

</html>