<?php
session_start();
include __DIR__ . '/../../connection.php';
require __DIR__ . '/../../vendor/autoload.php';
use Dompdf\Dompdf;

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
// Data & perhitungan

$no = 1;
$total_bayar = $sisa_bayar = 0;
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $total_bayar += $row['total_bayar'];
    $sisa_bayar += $row['sisa_bayar'];
}
$total = $total_bayar - $sisa_bayar;
$status = $total >= 0 ? 'Lunas' : 'Belum Lunas';

// Nama bulan
$nama_bulan = date('F', mktime(0, 0, 0, $bulan, 1));

// HTML content
$html = "
<style>
  body { font-family: sans-serif; font-size: 12px; }
  h2 { text-align: center; }
  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
  table, th, td { border: 1px solid #000; }
  th, td { padding: 6px; text-align: center; }
</style>

<h2>Laporan Gaji Guru - {$nama_bulan} {$tahun}</h2>
<table>
  <thead>
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
  </thead>
  <tbody>";

foreach ($rows as $row) {
    $html .= "<tr>
        <td>{$no}</td>
        <td>" . htmlspecialchars($row['nama_guru']) . "</td>
        <td>". date('d-m-Y', strtotime($row['tanggal_bayar']))."</td>
        <td>{$row['paket'] }</td>
        <td align='right'>". number_format($row['gaji']) ."</td>
        <td align='right'>". number_format($row['total_bayar'], 2)."</td>
        <td align='right'>".number_format($row['sisa_bayar'], 2) ."</td>
        <td align='right'>{$row['status'] }</td>
    </tr>";
    $no++;
}

$html .= "
    <tr>
        <td colspan='5'><strong>Total</strong></td>
        <td><strong>" . number_format($total_bayar, 2, ',', '.') . "</strong></td>
        <td><strong>" . number_format($sisa_bayar, 2, ',', '.') . "</strong></td>
        <td></td>
    </tr>
    <tr>
        <td colspan='5'><strong>{$status}</strong></td>
        <td colspan='3'><strong style='color: " . ($total >= 0 ? 'green' : 'red') . "'>" . number_format(abs($total), 2, ',', '.') . "</strong></td>
    </tr>
  </tbody>
</table>";

// Output PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Gaji Guru-{$bulan}-{$tahun}.pdf");
exit;
