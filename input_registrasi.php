<?php
include 'connection.php'; // Koneksi ke database

// Generate ID Murid (Format: 01, 02, 03, dst.) **(Hanya jika murid baru)**
$query_id = "SELECT LPAD(COALESCE(MAX(CAST(id_murid AS UNSIGNED)) + 1, 1), 2, '0') AS id_murid FROM master_murid";
$result = $conn->query($query_id);
$row = $result->fetch_assoc();
$new_id_murid = $row['id_murid']; // ID Murid untuk murid baru

// Generate No Registrasi (Format: 01, 02, 03, dst.)
$query_no_reg = "SELECT LPAD(COALESCE(MAX(CAST(no_reg AS UNSIGNED)) + 1, 1), 2, '0') AS no_reg FROM registrasi_murid";
$result = $conn->query($query_no_reg);
$row = $result->fetch_assoc();
$no_reg = $row['no_reg'];

// Ambil data murid lama
$query_murid = "SELECT id_murid, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp FROM master_murid";
$result_murid = $conn->query($query_murid);
$murid_data = [];

while ($row = $result_murid->fetch_assoc()) {
  $murid_data[$row['id_murid']] = $row; // Simpan data murid lama dalam array
}

// Ambil data paket bimbel
$query_paket = "SELECT id_paket, paket FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

if (isset($_POST['tambah'])) {
    // Ambil data dari form
    $murid_baru = $_POST['murid_baru']; // "baru" atau "lama"
    $id_murid = ($murid_baru == "baru") ? $new_id_murid : $_POST['id_murid']; // Jika baru, gunakan ID baru; jika lama, gunakan yang dipilih
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $kelas = $_POST['kelas'];
    $asal_sekolah = $_POST['asal_sekolah'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tgl_reg = $_POST['tgl_reg'];
    $no_telp = $_POST['no_telp'];
    $id_paket = $_POST['id_paket'];

    // Jika murid baru, tambahkan ke master_murid
    if ($murid_baru == "baru") {
        $query_insert_murid = "INSERT INTO master_murid (id_murid, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_murid = $conn->prepare($query_insert_murid);
        $stmt_murid->bind_param("ssssssss", $id_murid, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp);

        if (!$stmt_murid->execute()) {
            echo "<script>alert('Terjadi kesalahan saat menyimpan data ke master_murid!'); window.history.back();</script>";
            exit();
        }
        $stmt_murid->close();
    }

    // Tambahkan ke registrasi_murid (baik baru maupun lama)
    $query_insert_registrasi = "INSERT INTO registrasi_murid (no_reg, id_murid, tgl_reg, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp, id_paket)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_registrasi = $conn->prepare($query_insert_registrasi);
    $stmt_registrasi->bind_param("sssssssssss", $no_reg, $id_murid, $tgl_reg, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp, $id_paket);

    if ($stmt_registrasi->execute()) {
        echo "<script>alert('Data murid berhasil ditambahkan!'); window.location.href='hasil_data_registrasi.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat menyimpan data ke registrasi_murid!'); window.history.back();</script>";
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

  <title>Dashboard - NiceAdmin Bootstrap Template</title>
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

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.html" class="logo d-flex align-items-center">
        <img src="assets/img/logo.png" alt="">
        <span class="d-none d-lg-block">NiceAdmin</span>
      </a>
      <i class="bi bi-list toggle-sidebar-btn"></i>
    </div><!-- End Logo -->

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <input type="text" name="query" placeholder="Search" title="Enter search keyword">
        <button type="submit" title="Search"><i class="bi bi-search"></i></button>
      </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              You have 4 new notifications
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>Lorem Ipsum</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>30 min. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-x-circle text-danger"></i>
              <div>
                <h4>Atque rerum nesciunt</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>1 hr. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-check-circle text-success"></i>
              <div>
                <h4>Sit rerum fuga</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>2 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-info-circle text-primary"></i>
              <div>
                <h4>Dicta reprehenderit</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>4 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>
            <li class="dropdown-footer">
              <a href="#">Show all notifications</a>
            </li>

          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-chat-left-text"></i>
            <span class="badge bg-success badge-number">3</span>
          </a><!-- End Messages Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
            <li class="dropdown-header">
              You have 3 new messages
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-1.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Maria Hudson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>4 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-2.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Anna Nelson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>6 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-3.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>David Muldon</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>8 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="dropdown-footer">
              <a href="#">Show all messages</a>
            </li>

          </ul><!-- End Messages Dropdown Items -->

        </li><!-- End Messages Nav -->

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2">K. Anderson</span>
          </a><!-- End Profile Iamge Icon -->

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
        <li>
          <a href="hasil_data_registrasi.php">
            <i class="bi bi-circle"></i>
            <span>Hasil Data</span>
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
      <a class="nav-link" href="hasil_data_pembayaran.php">
        <i class="bi bi-cash"></i>
        <span>Pembayaran</span>
      </a>
    </li><!-- End Pembayaran -->

    <!-- Presensi -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#presensi-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Presensi</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="presensi-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="presensi_input.php">
            <i class="bi bi-circle"></i>
            <span>Input</span>
          </a>
        </li>
        <li>
          <a href="presensi_hasil.php">
            <i class="bi bi-circle"></i>
            <span>Hasil Data</span>
          </a>
        </li>
      </ul>
    </li><!-- End Presensi -->

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

    <!-- Form -->
<form method="POST" action="" enctype="multipart/form-data">
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
            <select class="form-control" name="id_murid" id="murid_lama_select" onchange="fillMuridData()">
                <option value="">Pilih Murid</option>
                <?php foreach ($murid_data as $murid): ?>
                    <option value="<?= htmlspecialchars($murid['id_murid']) ?>">
                        ID: <?= htmlspecialchars($murid['id_murid']) ?> - <?= htmlspecialchars($murid['nama']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Nama Murid -->
        <div class="mb-3" id="nama_murid_container">
            <label>Nama Murid</label>
            <input type="text" class="form-control" name="nama" id="nama_murid">
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
                <input class="form-check-input" type="radio" id="P" name="jenis_kelamin" value="P" required>
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
            <select class="form-control" name="id_paket">
                <option value="">Pilih Paket</option>
                <?php while ($paket = $result_paket->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($paket['id_paket']) ?>">
                        <?= htmlspecialchars($paket['paket']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-primary" name="tambah">Submit</button>
        </div>

    </div>
</form>

<!-- Script untuk menyembunyikan/memunculkan field dan autofill data murid -->
<script>
function toggleMurid(isBaru) {
    document.getElementById('id_murid_container').style.display = isBaru ? 'block' : 'none';
    document.getElementById('murid_lama_container').style.display = isBaru ? 'none' : 'block';
    document.getElementById('nama_murid_container').style.display = isBaru ? 'block' : 'none';

    if (isBaru) {
        document.getElementById('nama_murid').value = "";
        document.getElementById('tanggal_lahir').value = "";
        document.getElementById('alamat').value = "";
        document.getElementById('kelas').value = "";
        document.getElementById('asal_sekolah').value = "";
        document.getElementById('no_telp').value = "";
        document.querySelector('input[name="jenis_kelamin"][value="L"]').checked = false;
        document.querySelector('input[name="jenis_kelamin"][value="P"]').checked = false;
    }
}

var muridData = <?= json_encode($murid_data, JSON_PRETTY_PRINT); ?>;
console.log("Data Murid:", muridData); // Debugging: Cek apakah data ada di Console

function fillMuridData() {
    var selectedId = document.getElementById('murid_lama_select').value;

    if (selectedId && muridData[selectedId]) {
        document.getElementById('nama_murid').value = muridData[selectedId].nama;
        document.getElementById('tanggal_lahir').value = muridData[selectedId].tanggal_lahir;
        document.getElementById('alamat').value = muridData[selectedId].alamat;
        document.getElementById('kelas').value = muridData[selectedId].kelas;
        document.getElementById('asal_sekolah').value = muridData[selectedId].asal_sekolah;
        document.getElementById('no_telp').value = muridData[selectedId].no_telp;
        document.querySelector('input[name="jenis_kelamin"][value="' + muridData[selectedId].jenis_kelamin + '"]').checked = true;
    }
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
