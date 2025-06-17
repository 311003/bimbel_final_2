<?php
include __DIR__ . '/../../connection.php';
require __DIR__ . '/../../vendor/autoload.php';
session_start();
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

if ($guru) {
    $query_sql .= " AND pg.id_guru = ?";
    $params[] = $guru;
    $types .= "i";
}
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

// Inisialisasi Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$nama_bulan = date('F', mktime(0, 0, 0, $bulan, 1));
$judul = "Laporan Gaji Guru - $nama_bulan $tahun";

// Judul
$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', $judul);
$sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header
$header = ['No', 'Nama Guru', 'Tanggal', 'Paket', 'Total Gaji', 'Total Bayar', 'Sisa Bayar', 'Status'];
$sheet->fromArray($header, NULL, 'A3');
$sheet->getStyle('A3:H3')->applyFromArray([
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D9D9D9']
    ],
    'font' => ['bold' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

$rowNum = 4;
$no = 1;
$total_bayar = $sisa_bayar = 0;

while ($row = $result->fetch_assoc()) {
    $sheet->fromArray([
        $no++,
        $row['nama_guru'],
        date('d-m-Y', strtotime($row['tanggal_bayar'])),
        $row['paket'],
        $row['gaji'],
        $row['total_bayar'],
        $row['sisa_bayar'],
        $row['status']
    ], NULL, "A$rowNum");

    $sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    $total_bayar += $row['total_bayar'];
    $sisa_bayar += $row['sisa_bayar'];
    $rowNum++;
}

$total = $total_bayar - $sisa_bayar;
$summary_status = $total >= 0 ? 'Lunas' : 'Belum Lunas';

// Total Row
$sheet->mergeCells("A{$rowNum}:D{$rowNum}");
$sheet->setCellValue("A{$rowNum}", "Total");
$sheet->setCellValue("F{$rowNum}", $total_bayar);
$sheet->setCellValue("G{$rowNum}", $sisa_bayar);
$sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
    'font' => ['bold' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
]);
$rowNum++;

// Status Row
$sheet->mergeCells("A{$rowNum}:E{$rowNum}");
$sheet->setCellValue("A{$rowNum}", $summary_status);
$sheet->setCellValue("F{$rowNum}", abs($total));

$sheet->getStyle("A{$rowNum}:H{$rowNum}")->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => $total >= 0 ? '007700' : 'FF0000']
    ],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
]);

// Auto-size kolom
foreach (range('A', 'H') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=rekap_gaji_guru_{$bulan}_{$tahun}.xlsx");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
