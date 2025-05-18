<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

switch($_SESSION['role']){
    case 2:
        require 'middlewares/validasi_guru.php';
    break;
    case 3:
        require 'middlewares/validasi_murid.php';
    break;
}

// ✅ Fungsi untuk Membuat ID Presensi Otomatis (01, 02, 03, dst.)
function generateIdPresensi($conn) {
    $query = "SELECT LPAD(COALESCE(MAX(CAST(id_presensi AS UNSIGNED)) + 1, 1), 2, '0') AS id_presensi FROM presensi";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id_presensi']; // Mengembalikan ID presensi yang baru
    } else {
        return '01'; // Default ID jika belum ada data
    }
}

// Ambil data jadwal beserta informasi terkait
$query_jadwal = "SELECT 
                    j.id_jadwal, 
                    g.id_guru, 
                    g.nama_guru, 
                    p.id_paket, 
                    p.paket AS nama_paket,
                    j.tanggal_jadwal, 
                    j.jam_masuk, 
                    j.jam_keluar
                 FROM jadwal j
                 LEFT JOIN guru g ON j.id_guru = g.id_guru
                 LEFT JOIN paket_bimbel p ON j.id_paket = p.id_paket";
$result_jadwal = $conn->query($query_jadwal);

// Ambil data murid
$query_murid = "SELECT id_murid, nama FROM master_murid";
$result_murid = $conn->query($query_murid);

$query_status = "SELECT id_status_murid, status_presensi FROM keterangan_presensi";
$result_status = $conn->query($query_status);

// Fetch existing presensi data based on an ID (if editing)
$id_presensi = isset($_GET['id_presensi']) ? $_GET['id_presensi'] : '';

if ($id_presensi) {
    $query_existing_presensi = "SELECT * FROM presensi WHERE id_presensi = ?";
    $stmt = $conn->prepare($query_existing_presensi);
    $stmt->bind_param("s", $id_presensi);
    $stmt->execute();
    $existing_presensi = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Fetch the associated detail_presensi data for the students, including status_murid from keterangan_presensi
    $query_detail = "SELECT dp.*, mm.nama
    FROM detail_presensi dp
    LEFT JOIN master_murid mm ON dp.id_murid = mm.id_murid
    WHERE dp.id_presensi = ?";
    $stmt_detail = $conn->prepare($query_detail);
    $stmt_detail->bind_param("s", $id_presensi);
    $stmt_detail->execute();
    $detail_presensi_result = $stmt_detail->get_result();
    $stmt_detail->close();
}

if (isset($_POST['tambah'])) {   // Prepare the data from the form
    $id_presensi = $_POST['id_presensi'];
    $id_jadwal = $_POST['id_jadwal'];
    $id_guru = $_POST['id_guru'];
    $id_paket = $_POST['id_paket'];
    $id_murid = $_POST['id_murid']; // This is an array
    $id_status_murid = $_POST['id_status_murid']; // This is an array
    $tanggal_presensi = $_POST['tanggal_presensi'];
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = $_POST['jam_keluar'];

    // Loop through each student and insert the data into the final_presensi table
    for ($i = 0; $i < count($id_murid); $i++) {
        $murid_id = $id_murid[$i];
        $status_murid = $id_status_murid[$i];

        // Prepare the SQL query to insert data into final_presensi table
        $query = "INSERT INTO final_presensi (id_presensi, id_jadwal, id_guru, id_paket, id_murid, id_status_murid, tanggal_presensi, jam_masuk, jam_keluar)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($query)) {
            // Bind parameters to the SQL query
            $stmt->bind_param("sssssssss", $id_presensi, $id_jadwal, $id_guru, $id_paket, $murid_id, $status_murid, $tanggal_presensi, $jam_masuk, $jam_keluar);

            if ($stmt->execute()) {
                // Debugging output for each inserted record
                echo "Data for Murid ID: " . $murid_id . " successfully inserted.<br>";
            } else {
                echo "Error inserting data for Murid ID: " . $murid_id . ": " . $stmt->error . "<br>";
            }

            $stmt->close();
        } else {
            echo "Error: " . $conn->error . "<br>";
        }
    }

    // After all data is inserted, redirect to hasil_presensi_guru.php
    header("Location: hasil_presensi_guru.php");
    exit(); // Ensure no further code is executed after the redirect
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Absensi Guru</title>
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
<?= require('layouts/sidemenu_guru.php');?>

<main id="main" class="main">

    <div class="pagetitle">
      <h1>Absensi Presensi</h1>
    </div><!-- End Page Title -->

    <div class="card p-5 mb-5">
    <form method="POST" action="absensi_guru.php">
        <!-- ID Presensi -->
        <div class="form-group mb-3">
            <label for="id_presensi">ID Presensi</label>
            <input type="text" class="form-control" id="id_presensi" name="id_presensi" value="<?= htmlspecialchars($existing_presensi['id_presensi'] ?? $newId) ?>" readonly>
        </div>

        <!-- Pilih Jadwal -->
        <div class="form-group mb-3">
            <label for="id_jadwal">ID Jadwal</label>
            <input type="text" class="form-control" id="id_jadwal" name="id_jadwal" value="<?= htmlspecialchars($existing_presensi['id_jadwal'] ?? $newId) ?>" readonly>
        </div>

        <!-- ID Guru -->
        <div class="form-group mb-3">
            <label for="id_guru">ID Guru</label>
            <input type="text" class="form-control" id="id_guru" name="id_guru" value="<?= htmlspecialchars($existing_presensi['id_guru'] ?? '') ?>" readonly>
        </div>

        <!-- ID Paket -->
        <div class="form-group mb-3">
            <label for="id_paket">ID Paket</label>
            <input type="text" class="form-control" id="id_paket" name="id_paket" value="<?= htmlspecialchars($existing_presensi['id_paket'] ?? '') ?>" readonly>
        </div>

        <!-- Presensi Murid -->
<div class="form-group mb-3" id="murid-container">
    <label for="id_murid">Pilih ID Murid</label>
    <?php while ($detail = $detail_presensi_result->fetch_assoc()): ?>
        <div class="murid-entry mb-2 d-flex align-items-center">
            <!-- Murid ID and Nama -->
            <select class="form-control me-2" name="id_murid[]" required>
                <option value="<?= htmlspecialchars($detail['id_murid']) ?>" data-nama="<?= htmlspecialchars($detail['nama']) ?>">
                    <?= htmlspecialchars($detail['id_murid']) ?> - <?= htmlspecialchars($detail['nama']) ?>
                </option>
            </select>
            
            <!-- Keterangan Presensi Dropdown -->
            <!-- Keterangan Presensi Dropdown -->
<select class="form-control me-2" name="id_status_murid[]">
    <option value="">-- Pilih Keterangan --</option>
    <?php
    // Query untuk mengambil data status_murid dari tabel keterangan_presensi
    $query_keterangan = "SELECT * FROM keterangan_presensi";
    $result_keterangan = $conn->query($query_keterangan);

    if (!$result_keterangan) {
        echo "<option value=''>Error: " . $conn->error . "</option>"; // Menampilkan error jika query gagal
    } else {
        while ($status = $result_keterangan->fetch_assoc()): ?>
            <option value="<?= htmlspecialchars($status['id_status_murid']) ?>"
                <?= isset($detail['id_status_murid']) && $detail['id_status_murid'] == $status['id_status_murid'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($status['status_presensi']) ?>
            </option>
        <?php endwhile;
    }
    ?>
</select>

        </div>
    <?php endwhile; ?>
</div>

        <!-- Tanggal Presensi -->
        <div class="mb-3">
            <label for="tanggal_presensi" class="form-label">Tanggal Presensi</label>
            <input type="date" class="form-control" id="tanggal_presensi" name="tanggal_presensi" value="<?= htmlspecialchars($existing_presensi['tanggal_presensi'] ?? '') ?>" readonly>
        </div>

        <!-- Jam Masuk -->
        <div class="mb-3">
            <label for="jam_masuk" class="form-label">Jam Masuk</label>
            <input type="time" class="form-control" id="jam_masuk" name="jam_masuk" value="<?= htmlspecialchars($existing_presensi['jam_masuk'] ?? '') ?>" readonly>
        </div>

        <!-- Jam Keluar -->
        <div class="mb-3">
            <label for="jam_keluar" class="form-label">Jam Keluar</label>
            <input type="time" class="form-control" id="jam_keluar" name="jam_keluar" value="<?= htmlspecialchars($existing_presensi['jam_keluar'] ?? '') ?>" readonly>
        </div>

        <!-- Tombol Submit -->
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary" name="tambah">Simpan</button>
        </div>
    </form>
</div>

</main>
<?= require('layouts/footer.php');?>
</body>
</html>