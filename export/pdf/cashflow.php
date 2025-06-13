<?php
include __DIR__ . '/../../connection.php';
require __DIR__ . '/../../vendor/autoload.php';
use Dompdf\Dompdf;


$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$query = $conn->prepare("SELECT * FROM cashflow WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? ORDER BY tanggal ASC");
$query->bind_param("ii", $bulan, $tahun);
$query->execute();
$result = $query->get_result();

// Data & perhitungan
$no = 1;
$total_debet = $total_kredit = 0;
$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $total_debet += $row['debet'];
    $total_kredit += $row['kredit'];
}
$keuntungan = $total_debet - $total_kredit;
$status = $keuntungan >= 0 ? 'Laba / Surplus' : 'Rugi / Defisit';

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

<h2>Laporan Cashflow - {$nama_bulan} {$tahun}</h2>
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Tanggal</th>
      <th>Tipe</th>
      <th>Keterangan</th>
      <th>Debet</th>
      <th>Kredit</th>
    </tr>
  </thead>
  <tbody>";

foreach ($rows as $row) {
    $html .= "<tr>
        <td>{$no}</td>
        <td>" . date('d-m-Y', strtotime($row['tanggal'])) . "</td>
        <td>{$row['tipe']}</td>
        <td>{$row['keterangan']}</td>
        <td>" . number_format($row['debet'], 2, ',', '.') . "</td>
        <td>" . number_format($row['kredit'], 2, ',', '.') . "</td>
    </tr>";
    $no++;
}

$html .= "
    <tr>
        <td colspan='4'><strong>Total</strong></td>
        <td><strong>" . number_format($total_debet, 2, ',', '.') . "</strong></td>
        <td><strong>" . number_format($total_kredit, 2, ',', '.') . "</strong></td>
    </tr>
    <tr>
        <td colspan='4'><strong>{$status}</strong></td>
        <td colspan='2'><strong style='color: " . ($keuntungan >= 0 ? 'green' : 'red') . "'>" . number_format(abs($keuntungan), 2, ',', '.') . "</strong></td>
    </tr>
  </tbody>
</table>";

// Output PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("cashflow-{$bulan}-{$tahun}.pdf");
exit;