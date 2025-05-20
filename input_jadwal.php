<?php
include 'connection.php'; // Koneksi database
session_start();
echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug

// Fungsi Membuat ID Jadwal Otomatis
function generateIdJadwal($conn) {
  $query = "SELECT LPAD(COALESCE(MAX(CAST(id_jadwal AS UNSIGNED)) + 1, 1), 2, '0') AS id_jadwal FROM jadwal";
  $result = $conn->query($query);
  if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      return $row['id_jadwal'];
  } else {
      return '01';
  }
}

$newId = generateIdJadwal($conn);

// Fungsi Membuat ID Detail Jadwal Otomatis
function generateIdDetail($conn) {
    $query = "SELECT LPAD(COALESCE(MAX(CAST(id_detail AS UNSIGNED)) + 1, 1), 3, '0') AS id_detail FROM detail_jadwal";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id_detail'];
    } else {
        return '001';
    }
}

// Ambil Data Dropdown
$query_jadwal = "SELECT r.id_jadwal, r.id_paket, p.paket AS nama_paket 
                 FROM jadwal r
                 LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket";  
$result_jadwal = $conn->query($query_jadwal);

// Ambil hanya guru aktif
$query_guru = "SELECT g.id_guru, g.nama_guru 
               FROM guru g 
               WHERE g.id_status_guru != 2";
$result_guru = $conn->query($query_guru);

$query_paket = "SELECT id_paket, paket FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

// Jika Form Disubmit
if (isset($_POST['tambah_jadwal'])) {
    $id_jadwal = generateIdJadwal($conn);
    $id_guru_list = $_POST['id_guru']; // array jika multi-select, atau single ID
    $id_paket = $_POST['id_paket'];
    $tanggal_jadwal = $_POST['tanggal_jadwal'];
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = $_POST['jam_keluar'];

    // Validasi wajib isi
    if (empty($id_guru_list) || empty($id_paket) || empty($tanggal_jadwal) || empty($jam_masuk) || empty($jam_keluar)) {
        echo "<script>alert('Harap isi semua data!'); window.history.back();</script>";
        exit();
    }

    // Cek bentrok jadwal untuk setiap guru
    foreach ((array)$id_guru_list as $id_guru) {
        $cek_bentrok = $conn->prepare("SELECT * FROM jadwal 
            WHERE tanggal_jadwal = ? 
            AND id_guru = ?
            AND (
                (? < jam_keluar AND ? > jam_masuk)
            )
        ");
        $cek_bentrok->bind_param("ssss", 
            $tanggal_jadwal, $id_guru, 
            $jam_masuk, $jam_keluar
        );
        $cek_bentrok->execute();
        $result_bentrok = $cek_bentrok->get_result();
    
        if ($result_bentrok->num_rows > 0) {
            echo "<script>alert('Jadwal bentrok! Guru sudah memiliki jadwal pada waktu tersebut.'); window.history.back();</script>";
            exit();
        if (strtotime($tanggal_jadwal) < strtotime(date("Y-m-d"))) {
            echo "<script>alert('Tanggal jadwal tidak boleh di masa lalu!'); window.history.back();</script>";
            exit();
            }
            
        }
        $cek_bentrok->close();
    }    

    // ✅ Masukkan ke tabel `jadwal`
    $query_insert_jadwal = "INSERT INTO jadwal (id_jadwal, id_guru, id_paket, tanggal_jadwal, jam_masuk, jam_keluar) 
                            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_jadwal = $conn->prepare($query_insert_jadwal);
    $stmt_jadwal->bind_param("ssssss", $id_jadwal, $id_guru_list, $id_paket, $tanggal_jadwal, $jam_masuk, $jam_keluar);

    if (!$stmt_jadwal->execute()) {
        die("Gagal menambahkan jadwal: " . $stmt_jadwal->error);
    }
    $stmt_jadwal->close();

    // ✅ Masukkan ke tabel `detail_jadwal`
    $query_insert_detail = "INSERT INTO detail_jadwal (id_jadwal, id_guru, id_paket, tanggal_jadwal, jam_masuk, jam_keluar) 
                            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_detail = $conn->prepare($query_insert_detail);

    foreach ((array)$id_guru_list as $id_guru) {
        $stmt_detail->bind_param("ssssss", $id_jadwal, $id_guru, $id_paket, $tanggal_jadwal, $jam_masuk, $jam_keluar);
        if (!$stmt_detail->execute()) {
            die("Gagal menambahkan detail jadwal: " . $stmt_detail->error);
        }
    }

    $stmt_detail->close();
    $conn->close();

    echo "<script>alert('Jadwal dan detail jadwal berhasil ditambahkan!'); window.location.href='hasil_data_jadwal.php';</script>";
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Input Jadwal</title>
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
        <h1>Tambah Data Jadwal</h1>
    </div>

    <div class="card p-5 mb-5">
        <form method="POST" action="input_jadwal.php">

            <!-- ID Jadwal -->
            <div class="form-group mb-3">
                <label for="id_jadwal">ID Jadwal</label>
                <input type="text" class="form-control" id="id_jadwal" name="id_jadwal" value="<?= htmlspecialchars($newId) ?>" readonly>
            </div>

            <!-- Pilih ID Paket -->
            <div class="form-group mb-3">
                <label for="id_paket">Pilih Paket</label>
                <select class="form-control" id="id_paket" name="id_paket" required>
                    <option value="">-- Pilih Paket --</option>
                    <?php while ($row = $result_paket->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['id_paket']) ?>">
                            <?= htmlspecialchars($row['id_paket']) ?> - <?= htmlspecialchars($row['paket']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Pilih ID Guru -->
            <div class="form-group mb-3">
                <label for="id_guru">Pilih ID Guru</label>
                <select class="form-control" id="id_guru" name="id_guru" required onchange="autofillNamaGuru(this)">
                    <option value="">-- Pilih ID Guru --</option>
                    <?php while ($row = $result_guru->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['id_guru']) ?>" data-nama="<?= htmlspecialchars($row['nama_guru']) ?>">
                            <?= htmlspecialchars($row['id_guru']) ?> - <?= htmlspecialchars($row['nama_guru']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Nama Guru (Otomatis Terisi) -->
            <div class="form-group mb-3">
                <label for="nama_guru">Nama Guru</label>
                <input type="text" class="form-control" id="nama_guru" name="nama_guru" placeholder="Nama Guru" readonly>
            </div>

            <!-- Tanggal Jadwal -->
            <div class="form-group mb-3">
                <label for="tanggal_jadwal">Tanggal Jadwal</label>
                <input type="date" class="form-control" id="tanggal_jadwal" name="tanggal_jadwal" required>
            </div>

            <!-- Jam Masuk -->
            <div class="form-group mb-3">
                <label for="jam_masuk">Jam Masuk</label>
                <input type="time" class="form-control" id="jam_masuk" name="jam_masuk" required>
            </div>

            <!-- Jam Keluar -->
            <div class="form-group mb-3">
                <label for="jam_keluar">Jam Keluar</label>
                <input type="time" class="form-control" id="jam_keluar" name="jam_keluar" required>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary" name="tambah_jadwal">Tambah Jadwal</button>
                <a href="hasil_data_jadwal.php" class="btn btn-secondary">Batal</a>
            </div>

        </form>
    </div>
</main>

<?= require('layouts/footer.php');?>

<!-- JavaScript untuk mengisi Nama Guru Otomatis -->
<script>
function autofillNamaGuru(selectElement) {
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    document.getElementById("nama_guru").value = selectedOption.getAttribute("data-nama") || "";
}

const today = new Date().toISOString().split('T')[0];
    document.getElementById("tanggal_jadwal").setAttribute("min", today);
</script>
</body>
</html>
