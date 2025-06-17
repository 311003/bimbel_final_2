<?php
session_start();
include __DIR__ . '/../../connection.php';
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

// Spreadsheet init
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$nama_bulan = date('F', mktime(0, 0, 0, $bulan, 1));
$judul = "Laporan Pembayaran Murid - $nama_bulan $tahun";

// Judul
$sheet->mergeCells('A1:I1');
$sheet->setCellValue('A1', $judul);
$sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header
$header = ['No', 'Nama Murid', 'Tanggal', 'Paket', 'Total Biaya', 'Total Bayar', 'Sisa Biaya', 'Status Pembayaran', 'Status Murid'];
$sheet->fromArray($header, NULL, 'A3');
$sheet->getStyle('A3:I3')->applyFromArray([
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
    'font' => ['bold' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

$rowNum = 4;
$no = 1;
$total_bayar = $sisa_biaya = 0;

while ($row = $result->fetch_assoc()) {
    $sheet->fromArray([
        $no++,
        $row['nama'],
        date('d-m-Y', strtotime($row['tanggal_bayar'])),
        $row['paket'],
        $row['biaya'],
        $row['total_bayar'],
        $row['sisa_biaya'],
        $row['status_bayar'],
        $row['status']
    ], NULL, "A{$rowNum}");

    $sheet->getStyle("A{$rowNum}:I{$rowNum}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    $total_bayar += $row['total_bayar'];
    $sisa_biaya += $row['sisa_biaya'];
    $rowNum++;
}

// Total
$total = $total_bayar - $sisa_biaya;
$summary_status = $total >= 0 ? 'Laba' : 'Belum Lunas';

$sheet->mergeCells("A{$rowNum}:D{$rowNum}");
$sheet->setCellValue("A{$rowNum}", "Total");
$sheet->setCellValue("F{$rowNum}", $total_bayar);
$sheet->setCellValue("G{$rowNum}", $sisa_biaya);
$sheet->getStyle("A{$rowNum}:I{$rowNum}")->applyFromArray([
    'font' => ['bold' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
]);
$rowNum++;

// Status akhir
$sheet->mergeCells("A{$rowNum}:E{$rowNum}");
$sheet->setCellValue("A{$rowNum}", $summary_status);
$sheet->setCellValue("F{$rowNum}", abs($total));
$sheet->getStyle("A{$rowNum}:I{$rowNum}")->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => $total >= 0 ? '007700' : 'FF0000']
    ],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

// Auto size
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=rekap_pembayaran_murid_{$bulan}_{$tahun}.xlsx");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
