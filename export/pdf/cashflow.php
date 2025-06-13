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

$html = "<h3>Data Cashflow Bulan " . date('F', mktime(0,0,0,$bulan,1)) . " $tahun</h3>";
$html .= "<table border='1' cellpadding='5' cellspacing='0'>
<tr>
    <th>No</th><th>Tanggal</th><th>Tipe</th><th>Keterangan</th><th>Debet</th><th>Kredit</th>
</tr>";

$no = 1; $total_debet = $total_kredit = 0;
while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
        <td>{$no}</td>
        <td>".date('d-m-Y', strtotime($row['tanggal']))."</td>
        <td>{$row['tipe']}</td>
        <td>{$row['keterangan']}</td>
        <td align='right'>".number_format($row['debet'],2)."</td>
        <td align='right'>".number_format($row['kredit'],2)."</td>
    </tr>";
    $total_debet += $row['debet'];
    $total_kredit += $row['kredit'];
    $no++;
}
$html .= "<tr>
    <th colspan='4' align='right'>Total</th>
    <th align='right'>".number_format($total_debet,2)."</th>
    <th align='right'>".number_format($total_kredit,2)."</th>
</tr>";
$html .= "</table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("cashflow-{$bulan}-{$tahun}.pdf");