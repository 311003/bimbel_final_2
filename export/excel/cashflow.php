<?php
include __DIR__ . '/../../connection.php';
require __DIR__ . '/../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil data
$query = $conn->prepare("SELECT * FROM cashflow WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? ORDER BY tanggal ASC");
$query->bind_param("ii", $bulan, $tahun);
$query->execute();
$result = $query->get_result();

// Setup Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Cashflow");

// Judul Laporan
$nama_bulan = date('F', mktime(0, 0, 0, $bulan, 1));
$judul = "Laporan Cashflow - {$nama_bulan} {$tahun}";
$sheet->setCellValue('A1', $judul);
$sheet->mergeCells('A1:F1');
$sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header
$headers = ["No", "Tanggal", "Tipe", "Keterangan", "Debet", "Kredit"];
$sheet->fromArray($headers, NULL, 'A3');

// Style header
$sheet->getStyle('A3:F3')->applyFromArray([
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D9D9D9']
    ],
    'font' => ['bold' => true],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ]
]);

// Isi Data
$rowNum = 4;
$no = 1;
$total_debet = $total_kredit = 0;

while ($row = $result->fetch_assoc()) {
    $sheet->fromArray([
        $no++,
        date('d-m-Y', strtotime($row['tanggal'])),
        $row['tipe'],
        $row['keterangan'],
        $row['debet'],
        $row['kredit']
    ], NULL, "A{$rowNum}");

    $sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray([
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);

    $total_debet += $row['debet'];
    $total_kredit += $row['kredit'];
    $rowNum++;
}

// Total Row
$sheet->fromArray(["", "", "", "Total", $total_debet, $total_kredit], NULL, "A{$rowNum}");
$sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray([
    'font' => ['bold' => true],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);
$rowNum++;

// Laba / Rugi
$keuntungan = $total_debet - $total_kredit;
$status = $keuntungan >= 0 ? "Laba / Surplus" : "Rugi / Defisit";
$sheet->fromArray(["", "", "", $status, abs($keuntungan), ""], NULL, "A{$rowNum}");
$sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => $keuntungan >= 0 ? '007700' : 'FF0000']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Auto-size kolom
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Output Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=cashflow-{$bulan}-{$tahun}.xlsx");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;