<?php
include __DIR__ . '/../../connection.php';
require __DIR__ . '/../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$query = $conn->prepare("SELECT * FROM cashflow WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? ORDER BY tanggal ASC");
$query->bind_param("ii", $bulan, $tahun);
$query->execute();
$result = $query->get_result();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Cashflow");

$sheet->fromArray(["No", "Tanggal", "Tipe", "Keterangan", "Debet", "Kredit"], NULL, 'A1');

$rowNum = 2;
$no = 1; $total_debet = $total_kredit = 0;
while ($row = $result->fetch_assoc()) {
    $sheet->fromArray([
        $no++,
        date('d-m-Y', strtotime($row['tanggal'])),
        $row['tipe'],
        $row['keterangan'],
        $row['debet'],
        $row['kredit']
    ], NULL, "A$rowNum");
    $total_debet += $row['debet'];
    $total_kredit += $row['kredit'];
    $rowNum++;
}
$sheet->fromArray(["", "", "", "Total", $total_debet, $total_kredit], NULL, "A$rowNum");

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=cashflow-{$bulan}-{$tahun}.xlsx");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;