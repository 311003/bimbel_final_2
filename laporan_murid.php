<?php
// laporan_gaji_guru.php dan laporan_pembayaran_murid.php
include 'connection.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

$tanggal_dari = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';
$nama_murid = $_GET['nama_murid'] ?? '';
$export = $_GET['export'] ?? '';

$sql = "SELECT 
  p.id_pembayaran,
  m.nama AS nama_murid,
  p.tanggal_bayar,
  pb.paket,
  p.biaya,
  p.jumlah_bayar,
  p.sisa_biaya,
  p.status_pembayaran
FROM pembayaran p
JOIN master_murid m ON p.id_murid = m.id_murid
JOIN paket_bimbel pb ON p.id_paket = pb.id_paket
WHERE 1";

$params = [];
if ($tanggal_dari && $tanggal_sampai) {
    $sql .= " AND p.tanggal_bayar BETWEEN ? AND ?";
    $params[] = $tanggal_dari;
    $params[] = $tanggal_sampai;
}
if ($nama_murid) {
    $sql .= " AND m.nama LIKE ?";
    $params[] = "%$nama_murid%";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($export === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray(['No', 'Nama Murid', 'Tanggal Bayar', 'Paket', 'Biaya', 'Dibayar', 'Sisa', 'Status'], NULL, 'A1');
    $rowNum = 2;
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $sheet->fromArray([
            $no++, $row['nama_murid'], $row['tanggal_bayar'],
            $row['paket'], $row['biaya'], $row['jumlah_bayar'], $row['sisa_biaya'], $row['status_pembayaran']
        ], NULL, "A$rowNum");
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="laporan_pembayaran_murid.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

if ($export === 'pdf') {
    $html = '<h2>Laporan Pembayaran Murid</h2><table border="1" cellpadding="5" cellspacing="0">
        <tr><th>No</th><th>Nama Murid</th><th>Tanggal Bayar</th><th>Paket</th><th>Biaya</th><th>Dibayar</th><th>Sisa</th><th>Status</th></tr>';
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $html .= "<tr><td>{$no}</td><td>{$row['nama_murid']}</td><td>{$row['tanggal_bayar']}</td>
            <td>{$row['paket']}</td><td>{$row['biaya']}</td><td>{$row['jumlah_bayar']}</td><td>{$row['sisa_biaya']}</td><td>{$row['status_pembayaran']}</td></tr>";
        $no++;
    }
    $html .= '</table>';
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('laporan_pembayaran_murid.pdf');
    exit;
}
?>

<h2>Laporan Pembayaran Murid</h2>
<form method="get">
    Dari: <input type="date" name="tanggal_dari" value="<?= $tanggal_dari ?>">
    Sampai: <input type="date" name="tanggal_sampai" value="<?= $tanggal_sampai ?>">
    Murid: <input type="text" name="nama_murid" value="<?= $nama_murid ?>">
    <button type="submit">Filter</button>
    <a href="?tanggal_dari=<?= $tanggal_dari ?>&tanggal_sampai=<?= $tanggal_sampai ?>&nama_murid=<?= $nama_murid ?>&export=excel" class="btn btn-success">Export Excel</a>
    <a href="?tanggal_dari=<?= $tanggal_dari ?>&tanggal_sampai=<?= $tanggal_sampai ?>&nama_murid=<?= $nama_murid ?>&export=pdf" class="btn btn-danger">Export PDF</a>
</form>
<table border="1">
    <tr>
        <th>No</th>
        <th>Nama Murid</th>
        <th>Tanggal Bayar</th>
        <th>Paket</th>
        <th>Biaya</th>
        <th>Dibayar</th>
        <th>Sisa</th>
        <th>Status</th>
    </tr>
    <?php $no = 1; mysqli_data_seek($result, 0); while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['nama_murid'] ?></td>
        <td><?= $row['tanggal_bayar'] ?></td>
        <td><?= $row['paket'] ?></td>
        <td><?= number_format($row['biaya'], 0, ',', '.') ?></td>
        <td><?= number_format($row['jumlah_bayar'], 0, ',', '.') ?></td>
        <td><?= number_format($row['sisa_biaya'], 0, ',', '.') ?></td>
        <td><?= $row['status_pembayaran'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
