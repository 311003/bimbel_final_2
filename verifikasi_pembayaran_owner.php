<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara

// Ambil id_pembayaran dari URL
$id_pembayaran = $_GET['id_pembayaran'] ?? '';

// Ambil data pembayaran berdasarkan id_pembayaran
// $query = "SELECT pembayaran.*, paket_bimbel.paket 
//           FROM pembayaran 
//           LEFT JOIN paket_bimbel ON pembayaran.id_paket = paket_bimbel.id_paket 
//           WHERE pembayaran.id_pembayaran = ?";
 $query = "SELECT 
 r.tanggal_bayar, r.no_reg, r.id_paket, r.id_murid, r.nama, r.paket, r.biaya,r.id_pembayaran,
 sum(b.jumlah_bayar) as jumlah_bayar, (r.biaya - sum(b.jumlah_bayar)) AS sisa_biaya, r.status_pembayaran,
 paket_bimbel.paket 
 FROM pembayaran r 
 left join bukti_pembayaran b on b.id_pembayaran = r.id_pembayaran
  LEFT JOIN paket_bimbel ON r.id_paket = paket_bimbel.id_paket 
 WHERE r.id_pembayaran = ? group by b.id_pembayaran";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $id_pembayaran);
$stmt->execute();
$data_pembayaran = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Cek apakah bukti pembayaran ada
$bukti_pembayaran = $data_pembayaran['bukti_pembayaran'] ?? null;

// Ambil data murid yang terkait dengan id_murid dari pembayaran
$query_murid = "SELECT * FROM registrasi_murid WHERE id_murid = ?";
$stmt_murid = $conn->prepare($query_murid);
$stmt_murid->bind_param("s", $data_pembayaran['r.']);
$stmt_murid->execute();
$result_murid = $stmt_murid->get_result();
$stmt_murid->close();

// Ambil data paket yang terkait
$query_paket = "SELECT * FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

// Cek jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Ambil data dari form
    $id_pembayaran = $_POST['id_pembayaran']; // ID Pembayaran yang dipilih
    $jumlah_bayar = $_POST['jumlah_bayar'];   // Jumlah pembayaran yang dimasukkan
    $id_paket = $_POST['id_paket'];           // ID Paket yang dipilih
    $id_murid = $_POST['id_murid'];           // ID Murid yang dipilih

    // Proses upload bukti pembayaran
    $bukti_pembayaran = null;
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $uploadDir = 'uploads/';
        if (!in_array($_FILES['bukti_pembayaran']['type'], $allowed_types) || !is_dir($uploadDir)) {
            die("File upload failed: Invalid file type or directory.");
        }
        $fileName = uniqid() . "_" . basename($_FILES['bukti_pembayaran']['name']);
        $uploadFile = $uploadDir . $fileName;
        if (!move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $uploadFile)) {
            die("Failed to upload file.");
        }
        $bukti_pembayaran = $uploadFile; // Set path untuk disimpan di database
    }

    // Ambil data pembayaran yang sudah ada
    $query = "SELECT biaya, jumlah_bayar, sisa_biaya, status_pembayaran FROM pembayaran WHERE id_pembayaran = ?";
    // $query = "SELECT r.biaya, sum(b.jumlah_bayar) as jumlah_bayar, (r.biaya - sum(b.jumlah_bayar)) AS sisa_biaya, r.status_pembayaran
    //   FROM pembayaran r 
    //   left join bukti_pembayaran b on b.id_pembayaran = r.id_pembayaran WHERE r.id_pembayaran = ? group by b.id_pembayaran";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id_pembayaran);
    $stmt->execute();
    $stmt->bind_result($biaya, $jumlah_bayar_lama, $sisa_biaya, $status_pembayaran);
    $stmt->fetch();
    $stmt->close();

    // Menghitung jumlah bayar dan sisa biaya
    $jumlah_bayar_total = $jumlah_bayar_lama + $jumlah_bayar;
    $sisa_biaya = $biaya - $jumlah_bayar_total;
    $status_pembayaran = ($sisa_biaya <= 0) ? 'Lunas' : 'Belum Lunas';    

    // Tentukan status pembayaran
    $status_pembayaran = ($sisa_biaya <= 0) ? 'Lunas' : 'Belum Lunas';

    // Update data pembayaran dengan bukti pembayaran
    // Update pembayaran table
    $query_update = "UPDATE pembayaran SET jumlah_bayar = ?, sisa_biaya = ?, status_pembayaran = ?, input_pembayaran = ?, tanggal_bayar = ? WHERE id_pembayaran = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("dsssss", $jumlah_bayar_total, $sisa_biaya, $status_pembayaran, $bukti_pembayaran, $id_pembayaran, $tanggal_bayae);
    if ($stmt_update->execute()) {

        // If update was successful, insert the bukti pembayaran
        $query_insert = "INSERT INTO bukti_pembayaran (id_pembayaran, id_murid, tanggal_bayar, jumlah_bayar, bukti_pembayaran) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($query_insert);
        $dateToday = date('Y-m-d h:i:s');
        $stmt_insert->bind_param("issss", $id_pembayaran, $id_murid, $dateToday, $jumlah_bayar, $bukti_pembayaran);
        if ($stmt_insert->execute()) {
            $keterangan="Pembarayan murid";
            $tipe="Pemasukan";
            $query_insert = "INSERT INTO cashflow (tipe,keterangan,tanggal) VALUES (?, ?,?)";
            $cash_insert = $conn->prepare($query_insert);
            $dateToday = date('Y-m-d h:i:s');
            $cash_insert->bind_param("sss", $tipe, $keterangan, $dateToday);
            $cash_insert->execute();
            echo "<script>alert('Data pembayaran berhasil diperbarui!'); window.location.href='hasil_data_pembayaran.php';</script>";
        } else {
            echo "<script>alert('Error memasukkan bukti pembayaran: " . $conn->error . "');</script>";
        }
        $stmt_insert->close();
    } else {
        echo "<script>alert('Error memperbarui data pembayaran: " . $conn->error . "');</script>";
    }
    $stmt_update->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Verifikasi Pembayaran Owner</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
  <link href="assets/css/custom.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>
<?= require('layouts/header.php');?>
<?= require('layouts/sidemenu_owner.php');?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Verifikasi Pembayaran</h1>
    </div>

    <div class="card p-5 mb-5">
        <form method="POST" action="verifikasi_pembayaran_owner.php" enctype="multipart/form-data">

            <!-- ID Pembayaran -->
            <div class="form-group mb-3">
                <label for="id_pembayaran">ID Pembayaran</label>
                <input type="text" class="form-control" id="id_pembayaran" name="id_pembayaran" value="<?= htmlspecialchars($data_pembayaran['id_pembayaran']) ?>" readonly>
            </div>

            <!-- ID Murid -->
            <div class="form-group mb-3">
                <label for="id_murid">ID Murid</label>
                <input type="text" class="form-control" id="id_murid" name="id_murid" 
                      value="<?= htmlspecialchars($data_pembayaran['id_murid']) ?> - <?= htmlspecialchars($data_pembayaran['nama']) ?>" 
                      readonly>
            </div>

            <!-- Nama Murid (Otomatis Terisi) -->
            <div class="form-group mb-3">
                <label for="nama">Nama Murid</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($data_pembayaran['nama']) ?>" placeholder="Nama Murid" readonly>
            </div>

        <!-- Tampilkan Nama Paket -->
            <div class="form-group mb-3">
                <label for="nama_paket">Pilihan Paket</label>
                <input type="text" class="form-control" id="nama_paket"
                      value="<?= htmlspecialchars($data_pembayaran['paket']) ?>" readonly>
            </div>

            <!-- Simpan ID Paket untuk dikirim ke server -->
            <input type="hidden" name="id_paket" value="<?= htmlspecialchars($data_pembayaran['id_paket']) ?>">

            <!-- Biaya Paket -->
            <div class="form-group mb-3">
                <label for="biaya">Biaya Paket</label>
                <input type="text" class="form-control" id="biaya" name="biaya" value="<?= htmlspecialchars($data_pembayaran['biaya']) ?>" readonly>
            </div>

            <!-- Jumlah Bayar -->
            <div class="form-group mb-3">
                <label for="jumlah_bayar">Jumlah Bayar</label>
                <input type="number" class="form-control" id="jumlah_bayar" name="jumlah_bayar" min="0" value="<?= htmlspecialchars($data_pembayaran['jumlah_bayar']) ?>" required>
            </div>

            <!-- Sisa Biaya -->
            <div class="form-group mb-3">
                <label for="sisa_biaya">Sisa Biaya</label>
                <input type="text" class="form-control" id="sisa_biaya" name="sisa_biaya" value="<?= htmlspecialchars($data_pembayaran['sisa_biaya']) ?>" readonly>
            </div>

            <!-- Status Pembayaran -->
            <div class="form-group mb-3">
                <label for="status_pembayaran">Status Pembayaran</label>
                <input type="text" class="form-control" id="status_pembayaran" name="status_pembayaran" value="<?= htmlspecialchars($data_pembayaran['status_pembayaran']) ?>" readonly>
            </div>

             <!-- Bukti Pembayaran -->
             <div class="form-group mb-3">
                <label for="bukti_pembayaran">Upload Bukti Pembayaran</label>
                <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" accept="image/*,application/pdf">
                <?php if (!empty($data_pembayaran['bukti_pembayaran'])): ?>
                    <div class="mt-2">
                        <label>Bukti Pembayaran Saat Ini:</label><br>
                        <a href="<?= $data_pembayaran['bukti_pembayaran'] ?>" target="_blank">Lihat Bukti Pembayaran</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary" name="verifikasi_pembayaran">Verifikasi Pembayaran</button>
                <a href="hasil_data_pembayaran.php" class="btn btn-secondary">Batal</a>
            </div>

        </form>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const jumlahBayarInput = document.getElementById("jumlah_bayar_input");
    const biayaPaketInput = document.getElementById("biaya_paket_input");
    const sisaBiayaField = document.getElementById("sisa_biaya");
    const statusPembayaranField = document.getElementById("status_pembayaran");

    jumlahBayarInput.addEventListener("input", function () {
        const biaya = parseFloat(biayaPaketInput.value) || 0;
        const jumlahBayar = parseFloat(jumlahBayarInput.value) || 0;
        const sisa = biaya - jumlahBayar;

        sisaBiayaField.value = sisa;
        statusPembayaranField.value = (sisa <= 0) ? "Lunas" : "Belum Lunas";
    });
});
</script>
</body>
</html>

<script>
    // Fungsi untuk mengisi otomatis data murid berdasarkan ID yang dipilih
    function autofillMuridDetails(selectElement) {
        // Ambil data dari option yang dipilih
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        
        // Ambil nama_murid dan biaya dari atribut data
        var namaMurid = selectedOption.getAttribute('data-nama');
        var biaya = selectedOption.getAttribute('data-biaya');
        
        // Isi field nama_murid dan biaya
        document.getElementById('nama_murid').value = namaMurid;
        document.getElementById('biaya').value = biaya;
    }

    sisaInput.value = new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR'
}).format(sisa);
</script>

<main>
<?= require('layouts/footer.php');?>
</body>
<html>