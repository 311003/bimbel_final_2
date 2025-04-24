<?php
include 'connection.php'; // Koneksi ke database

// Generate ID Murid (Untuk murid baru)
$query_id = "SELECT LPAD(COALESCE(MAX(CAST(id_murid AS UNSIGNED)) + 1, 1), 2, '0') AS id_murid FROM master_murid";
$result = $conn->query($query_id);
$row = $result->fetch_assoc();
$new_id_murid = $row['id_murid'] ?? '01'; // Default '01' jika kosong

// Generate No Registrasi
$query_no_reg = "SELECT LPAD(COALESCE(MAX(CAST(no_reg AS UNSIGNED)) + 1, 1), 2, '0') AS no_reg FROM registrasi_murid";
$result = $conn->query($query_no_reg);
$row = $result->fetch_assoc();
$no_reg = $row['no_reg'] ?? '01'; // Default '01' jika kosong

// Ambil data murid lama
$query_murid = "SELECT id_murid, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp FROM master_murid";
$result_murid = $conn->query($query_murid);
$murid_data = [];

while ($row = $result_murid->fetch_assoc()) {
    $murid_data[$row['id_murid']] = $row;
}

// Ambil data paket bimbel
$query_paket = "SELECT id_paket, paket FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

// Jika tombol "Tambah" ditekan
if (isset($_POST['tambah'])) {
    $murid_baru = $_POST['murid_baru'] ?? ''; // "baru" atau "lama"
    $id_murid = ($murid_baru === "baru") ? $new_id_murid : ($_POST['murid_lama_select'] ?? '');

    $nama = $_POST['nama'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $kelas = $_POST['kelas'] ?? '';
    $asal_sekolah = $_POST['asal_sekolah'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tgl_reg = $_POST['tgl_reg'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    $id_paket = $_POST['id_paket'] ?? '';

    // Validasi agar semua field wajib diisi
    if (empty($id_murid) || empty($nama) || empty($tanggal_lahir) || empty($alamat) || empty($kelas) || empty($asal_sekolah) || empty($jenis_kelamin) || empty($tgl_reg) || empty($no_telp) || empty($id_paket)) {
        echo "<script>alert('Semua field harus diisi!'); window.history.back();</script>";
        exit();
    }

    // Jika murid baru, masukkan ke master_murid terlebih dahulu
    if ($murid_baru === "baru") {
        $query_insert_murid = "INSERT INTO master_murid (id_murid, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp, id_paket)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_murid = $conn->prepare($query_insert_murid);
        $stmt_murid->bind_param("sssssssss", $id_murid, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp, $id_paket);

        if (!$stmt_murid->execute()) {
            die("Error menyimpan murid baru: " . $stmt_murid->error);
        }
        $stmt_murid->close();
    }

    // Pastikan id_murid ada di master_murid sebelum registrasi
    $check_murid = $conn->query("SELECT id_murid FROM master_murid WHERE id_murid = '$id_murid'");
    if ($check_murid->num_rows === 0) {
        die("Error: ID Murid tidak ditemukan di master_murid setelah insert.");
    }

    // Simpan ke registrasi_murid
    $query_insert_registrasi = "INSERT INTO registrasi_murid (no_reg, id_murid, tgl_reg, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp, id_paket)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_registrasi = $conn->prepare($query_insert_registrasi);
    $stmt_registrasi->bind_param("sssssssssss", $no_reg, $id_murid, $tgl_reg, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp, $id_paket);

    if ($stmt_registrasi->execute()) {
        echo "<script>alert('Data murid berhasil ditambahkan!'); window.location.href='konfirmasi_registrasi.php';</script>";
    } else {
        die("Error menyimpan ke registrasi_murid: " . $stmt_registrasi->error);
    }

    $stmt_registrasi->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Dashboard - Owner</title>
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

  <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>

</div>
      <header id="header" class="header fixed-top d-flex align-items-center">
        <img src="assets/img/logo_bimbel.png" alt="Logo Bimbel XYZ"
            style="height: 60px; width: auto; display: block;">
        <span class="d-none d-lg-block ms-3 fs-4">Bimbel XYZ</span>
      </div>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>Kevin Anderson</h6>
              <span>Web Designer</span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                <i class="bi bi-question-circle"></i>
                <span>Need Help?</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header><!-- End Header -->

  <!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link" href="dashboard_owner.php">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <!-- Registrasi Murid -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#registrasi-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Registrasi Murid</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="registrasi-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="input_registrasi.php">
            <i class="bi bi-circle"></i>
            <span>Input</span>
          </a>
        </li>
        </li>
        <a href="konfirmasi_registrasi.php">
            <i class="bi bi-circle"></i>
            <span>Konfirmasi Registrasi </span>
          </a>
        </li>
        </li>
        <a href="view_konfirmasi_registrasi.php">
            <i class="bi bi-circle"></i>
            <span>View Konfirmasi Registrasi </span>
          </a>
        </li>
      </ul>
    </li><!-- End Registrasi Murid -->

    <!-- Jadwal -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#jadwal-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Jadwal</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="jadwal-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="input_jadwal.php">
            <i class="bi bi-circle"></i>
            <span>Input</span>
          </a>
        </li>
        <li>
          <a href="hasil_data_jadwal.php">
            <i class="bi bi-circle"></i>
            <span>Hasil Data</span>
          </a>
        </li>
      </ul>
    </li><!-- End Jadwal -->

   <!-- Pembayaran -->
   <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#pembayaran-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Pembayaran</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="pembayaran-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="hasil_data_pembayaran.php">
            <i class="bi bi-circle"></i>
            <span>Hasil Data</span>
          </a>
        </li>
      </ul>
    </li><!-- End Pembayaran -->

    <!-- Jadwal -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#master-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Master</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="master-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="master_murid.php">
            <i class="bi bi-circle"></i>
            <span>Murid</span>
          </a>
        </li>
        <li>
          <a href="master_guru.php">
            <i class="bi bi-circle"></i>
            <span>Guru</span>
          </a>
        </li>
        <li>
          <a href="master_paket.php">
            <i class="bi bi-circle"></i>
            <span>Paket</span>
          </a>
        </li>
        <li>
          <a href="master_user.php">
            <i class="bi bi-circle"></i>
            <span>User</span>
          </a>
        </li>
      </ul>
    </li><!-- End Jadwal -->

<!-- Logout -->
<li class="nav-item">
      <a class="nav-link" href="login.php">
        <i class="bi bi-cash"></i>
        <span>Logout</span>
      </a>
    </li><!-- Logout -->
  </ul>
</aside><!-- End Sidebar -->


<main id="main" class="main">

<div class="pagetitle">
  <h1>Input Registrasi</h1>
</div><!-- End Page Title -->

<form method="POST" action="" enctype="multipart/form-data" id="formRegistrasi">
    <div class="card p-5 mb-5">

        <!-- Nomor Registrasi -->
        <div class="form-group mb-3">
            <label for="no_reg">Nomor Registrasi</label>
            <input type="text" class="form-control" id="no_reg" name="no_reg" value="<?= htmlspecialchars($no_reg) ?>" readonly>
        </div>

        <!-- Tanggal Registrasi -->
        <div class="form-group mb-3">
            <label for="tgl_reg">Tanggal Registrasi</label>
            <input type="date" class="form-control" id="tgl_reg" name="tgl_reg" required>
        </div>

        <!-- Pilihan Murid Baru atau Lama -->
        <div class="mb-3">
            <label>Murid Baru atau Lama:</label><br>
            <input type="radio" name="murid_baru" value="baru" onclick="toggleMurid(true)" checked> Baru
            <input type="radio" name="murid_baru" value="lama" onclick="toggleMurid(false)"> Lama
        </div>

        <!-- ID Murid (Untuk Murid Baru) -->
        <div class="mb-3" id="id_murid_container">
            <label>ID Murid:</label>
            <input type="text" class="form-control" name="id_murid" id="id_murid" value="<?= htmlspecialchars($new_id_murid) ?>" readonly>
        </div>

        <!-- Dropdown Pilih Murid Lama -->
        <div class="mb-3" id="murid_lama_container" style="display:none;">
            <label>Pilih Murid Lama:</label>
            <select id="murid_lama_select" class="form-control" name="murid_lama_select" onchange="fillMuridData()">
                <option value="">-- Pilih Murid Lama --</option>
                <?php foreach ($murid_data as $id_murid => $murid) : ?>
                    <option value="<?= $id_murid; ?>"><?= htmlspecialchars($murid['nama']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Input Hidden untuk ID Murid Lama -->
        <input type="hidden" name="id_murid" id="id_murid_hidden">

        <!-- Nama Murid -->
        <div class="mb-3">
            <label>Nama Murid</label>
            <input type="text" class="form-control" name="nama" id="nama_murid" required>
        </div>

        <!-- Tanggal Lahir -->
        <div class="form-group mb-3">
            <label for="tanggal_lahir">Tanggal Lahir</label>
            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
        </div>

        <!-- Alamat Rumah -->
        <div class="form-group mb-3">
            <label for="alamat">Alamat Rumah</label>
            <input type="text" class="form-control" id="alamat" name="alamat" required>
        </div>

        <!-- Kelas -->
        <div class="form-group mb-3">
            <label for="kelas">Kelas</label>
            <input type="text" class="form-control" id="kelas" name="kelas" required>
        </div>

        <!-- Asal Sekolah -->
        <div class="form-group mb-3">
            <label for="asal_sekolah">Asal Sekolah</label>
            <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah" required>
        </div>

        <!-- Jenis Kelamin -->
        <div class="form-group mb-3">
            <label for="jenis_kelamin">Jenis Kelamin</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" id="L" name="jenis_kelamin" value="L" required>
                <label class="form-check-label" for="L">Laki-laki</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" id="P" name="jenis_kelamin" value="P">
                <label class="form-check-label" for="P">Perempuan</label>
            </div>
        </div>

        <!-- Nomor Telepon -->
        <div class="form-group mb-3">
            <label for="no_telp">Nomor Telepon</label>
            <input type="text" class="form-control" id="no_telp" name="no_telp" required>
        </div>

        <!-- Pilihan Paket Bimbel -->
        <div class="mb-3">
            <label>Paket Bimbel:</label>
            <select class="form-control" name="id_paket" required>
                <option value="">Pilih Paket</option>
                <?php 
                $query_paket = "SELECT id_paket, paket FROM paket_bimbel";
                $result_paket = $conn->query($query_paket);
                while ($paket = $result_paket->fetch_assoc()): 
                ?>
                    <option value="<?= htmlspecialchars($paket['id_paket']) ?>">
                        <?= htmlspecialchars($paket['paket']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-primary" name="tambah">Simpan</button>
        </div>
    </div>
</form>

<script>
function toggleMurid(isNew) {
    if (isNew) {
        document.getElementById("id_murid_container").style.display = "block";
        document.getElementById("murid_lama_container").style.display = "none";
        document.getElementById("id_murid_hidden").value = document.getElementById("id_murid").value;
    } else {
        document.getElementById("id_murid_container").style.display = "none";
        document.getElementById("murid_lama_container").style.display = "block";
        document.getElementById("id_murid_hidden").value = "";
    }
}

function fillMuridData() {
    var muridData = <?= json_encode($murid_data, JSON_PRETTY_PRINT); ?>;
    var selectedId = document.getElementById("murid_lama_select").value;

    if (!selectedId || !(selectedId in muridData)) {
        console.log("Data tidak ditemukan untuk ID:", selectedId);
        return;
    }

    var murid = muridData[selectedId];

    document.getElementById("nama_murid").value = murid.nama;
    document.getElementById("tanggal_lahir").value = murid.tanggal_lahir;
    document.getElementById("alamat").value = murid.alamat;
    document.getElementById("kelas").value = murid.kelas;
    document.getElementById("asal_sekolah").value = murid.asal_sekolah;
    document.getElementById("no_telp").value = murid.no_telp;
    document.getElementById("id_murid_hidden").value = selectedId; // Kirim ID Murid Lama ke PHP

    if (murid.jenis_kelamin === "L") {
        document.getElementById("L").checked = true;
    } else {
        document.getElementById("P").checked = true;
    }

    console.log("Data berhasil dimasukkan:", murid);
}
</script>


<!-- Vendor JS Files -->
<script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>
