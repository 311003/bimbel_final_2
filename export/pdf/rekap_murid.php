<?php
session_start();
include __DIR__ . '/../../connection.php';
require __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;

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
        pm.jumlah_bayar AS total_bayar,
        pm.sisa_biaya,
        pm.status_pembayaran AS status_bayar,
        sm.status_murid AS status,
        pb.paket,
        m.nama
    FROM pembayaran pm
    LEFT JOIN master_murid m ON pm.id_murid = m.id_murid
    LEFT JOIN paket_bimbel pb ON pm.id_paket = pb.id_paket
    LEFT JOIN status_murid sm ON sm.id_status_murid = m.status_murid
    WHERE MONTH(pm.tanggal_bayar) = ? AND YEAR(pm.tanggal_bayar) = ?
";

$params = [$bulan, $tahun];
$types = "ii";

if ($murid) {
    $query_sql .= " AND pm.id_murid = ?";
    $params[] = $murid;
    $types .= "i";
}
if ($status) {
    $query_sql .= " AND m.status_murid = ?";
    $params[] = $status;
    $types .= "s";
}
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

$no = 1;
$total_bayar = $sisa_biaya = 0;
$rows = [];

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $total_bayar += $row['total_bayar'];
    $sisa_biaya += $row['sisa_biaya'];
}

$total = $total_bayar - $sisa_biaya;
$summary_status = $total >= 0 ? 'Laba' : 'Belum Lunas';
$color = $total >= 0 ? 'green' : 'red';
$nama_bulan = date('F', mktime(0, 0, 0, $bulan, 1));

$html = "
<style>
    body { font-family: sans-serif; font-size: 11px; }
    h2 { text-align: center; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table, th, td { border: 1px solid #000; }
    th, td { padding: 6px; text-align: center; }
</style>

<h2>Laporan Pembayaran Murid - {$nama_bulan} {$tahun}</h2>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Murid</th>
            <th>Tanggal</th>
            <th>Paket</th>
            <th>Total Biaya</th>
            <th>Total Bayar</th>
            <th>Sisa Biaya</th>
            <th>Status Pembayaran</th>
            <th>Status Murid</th>
        </tr>
    </thead>
    <tbody>";

foreach ($rows as $row) {
    $html .= "<tr>
        <td>{$no}</td>
        <td>" . htmlspecialchars($row['nama']) . "</td>
        <td>" . date('d-m-Y', strtotime($row['tanggal_bayar'])) . "</td>
        <td>" . htmlspecialchars($row['paket']) . "</td>
        <td>" . number_format($row['biaya'], 0, ',', '.') . "</td>
        <td>" . number_format($row['total_bayar'], 0, ',', '.') . "</td>
        <td>" . number_format($row['sisa_biaya'], 0, ',', '.') . "</td>
        <td>{$row['status_bayar']}</td>
        <td>{$row['status']}</td>
    </tr>";
    $no++;
}

$html .= "
<tr>
    <td colspan='5'><strong>Total</strong></td>
    <td><strong>" . number_format($total_bayar, 0, ',', '.') . "</strong></td>
    <td><strong>" . number_format($sisa_biaya, 0, ',', '.') . "</strong></td>
    <td colspan='2'></td>
</tr>
<tr>
    <td colspan='5'><strong>Status Keseluruhan</strong></td>
    <td colspan='4'><strong style='color: {$color}'>" . $summary_status . " - " . number_format(abs($total), 0, ',', '.') . "</strong></td>
</tr>
";

$html .= "</tbody></table>";

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_pembayaran_murid_{$bulan}_{$tahun}.pdf");
exit;
