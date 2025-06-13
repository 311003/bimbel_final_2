<?php
// laporan_gaji_guru.php
include 'connection.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

$tanggal_dari = $_GET['tanggal_dari'] ?? '';
$tanggal_sampai = $_GET['tanggal_sampai'] ?? '';
$nama_guru = $_GET['nama_guru'] ?? '';
$export = $_GET['export'] ?? '';

$sql = "SELECT 
  pg.id_pembayaran,
  g.nama_guru,
  pg.tanggal_bayar,
  pg.gaji,
  pg.jumlah_bayar,
  pg.sisa_bayar,
  pg.status_pembayaran
FROM pembayaran_guru pg
JOIN guru g ON pg.id_guru = g.id_guru
WHERE 1";

$params = [];
if ($tanggal_dari && $tanggal_sampai) {
    $sql .= " AND pg.tanggal_bayar BETWEEN ? AND ?";
    $params[] = $tanggal_dari;
    $params[] = $tanggal_sampai;
}
if ($nama_guru) {
    $sql .= " AND g.nama_guru LIKE ?";
    $params[] = "%$nama_guru%";
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
    $sheet->fromArray(['No', 'Nama Guru', 'Tanggal Bayar', 'Gaji', 'Dibayar', 'Sisa', 'Status'], NULL, 'A1');
    $rowNum = 2;
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $sheet->fromArray([
            $no++, $row['nama_guru'], $row['tanggal_bayar'],
            $row['gaji'], $row['jumlah_bayar'], $row['sisa_bayar'], $row['status_pembayaran']
        ], NULL, "A$rowNum");
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="laporan_gaji_guru.xlsx"');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

if ($export === 'pdf') {
    $html = '<h2>Laporan Gaji Guru</h2><table border="1" cellpadding="5" cellspacing="0">
        <tr><th>No</th><th>Nama Guru</th><th>Tanggal Bayar</th><th>Gaji</th><th>Dibayar</th><th>Sisa</th><th>Status</th></tr>';
    $no = 1;
    while ($row = $result->fetch_assoc()) {
        $html .= "<tr><td>{$no}</td><td>{$row['nama_guru']}</td><td>{$row['tanggal_bayar']}</td>
            <td>{$row['gaji']}</td><td>{$row['jumlah_bayar']}</td><td>{$row['sisa_bayar']}</td><td>{$row['status_pembayaran']}</td></tr>";
        $no++;
    }
    $html .= '</table>';
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('laporan_gaji_guru.pdf');
    exit;
}
?>

<h2>Laporan Gaji Guru</h2>
<form method="get">
    Dari: <input type="date" name="tanggal_dari" value="<?= $tanggal_dari ?>">
    Sampai: <input type="date" name="tanggal_sampai" value="<?= $tanggal_sampai ?>">
    Guru: <input type="text" name="nama_guru" value="<?= $nama_guru ?>">
    <button type="submit">Filter</button>
    <a href="?tanggal_dari=<?= $tanggal_dari ?>&tanggal_sampai=<?= $tanggal_sampai ?>&nama_guru=<?= $nama_guru ?>&export=excel" class="btn btn-success">Export Excel</a>
    <a href="?tanggal_dari=<?= $tanggal_dari ?>&tanggal_sampai=<?= $tanggal_sampai ?>&nama_guru=<?= $nama_guru ?>&export=pdf" class="btn btn-danger">Export PDF</a>
</form>
<table border="1">
    <tr>
        <th>No</th>
        <th>Nama Guru</th>
        <th>Tanggal Bayar</th>
        <th>Gaji</th>
        <th>Dibayar</th>
        <th>Sisa</th>
        <th>Status</th>
    </tr>
    <?php $no = 1; mysqli_data_seek($result, 0); while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $no++ ?></td>
        <td><?= $row['nama_guru'] ?></td>
        <td><?= $row['tanggal_bayar'] ?></td>
        <td><?= number_format($row['gaji'], 0, ',', '.') ?></td>
        <td><?= number_format($row['jumlah_bayar'], 0, ',', '.') ?></td>
        <td><?= number_format($row['sisa_bayar'], 0, ',', '.') ?></td>
        <td><?= $row['status_pembayaran'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
