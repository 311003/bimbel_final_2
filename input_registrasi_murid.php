<?php
include 'connection.php'; // Koneksi ke database

// Generate ID Murid secara otomatis (format: 01, 02, 03, dst.)
$query_id = "SELECT LPAD(COALESCE(MAX(CAST(id_murid AS UNSIGNED)) + 1, 1), 2, '0') AS id_murid FROM master_murid";
$result = $conn->query($query_id);
$row = $result->fetch_assoc();
$new_id_murid = $row['id_murid']; // ID Murid baru jika belum ada

// Generate No Registrasi (Format: 01, 02, 03, dst.)
$query_no_reg = "SELECT LPAD(COALESCE(MAX(CAST(no_reg AS UNSIGNED)) + 1, 1), 2, '0') AS no_reg FROM registrasi_murid";
$result = $conn->query($query_no_reg);
$row = $result->fetch_assoc();
$no_reg = $row['no_reg'];

// Ambil data paket bimbel
$query_paket = "SELECT id_paket, paket FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

// Jika form disubmit
if (isset($_POST['tambah'])) {
    $id_murid = $_POST['id_murid']; // ID Murid yang diinputkan
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $kelas = $_POST['kelas'];
    $asal_sekolah = $_POST['asal_sekolah'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tgl_reg = $_POST['tgl_reg'];
    $no_telp = $_POST['no_telp'];
    $id_paket = $_POST['id_paket'];

    // Cek apakah murid sudah terdaftar di master_murid
    $query_check_murid = "SELECT id_murid FROM master_murid WHERE id_murid = ?";
    $stmt_check = $conn->prepare($query_check_murid);
    $stmt_check->bind_param("s", $id_murid);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows == 0) {
        // Jika murid belum ada, masukkan ke master_murid
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
    $stmt_check->close();

    // Tambahkan ke registrasi_murid
    $query_insert_registrasi = "INSERT INTO registrasi_murid (no_reg, id_murid, tgl_reg, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp, id_paket)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_registrasi = $conn->prepare($query_insert_registrasi);
    $stmt_registrasi->bind_param("sssssssssss", $no_reg, $id_murid, $tgl_reg, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp, $id_paket);

    if ($stmt_registrasi->execute()) {
        echo "<script>alert('Data murid berhasil ditambahkan!'); window.location.href='view_registrasi_murid.php';</script>";
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
      <a class="nav-link" href="dashboard_murid.php">
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
          <a href="input_registrasi_murid.php">
            <i class="bi bi-circle"></i>
            <span>Input</span>
          </a>
        </li>
        <li>
          <a href="view_registrasi_murid.php">
            <i class="bi bi-circle"></i>
            <span>Hasil Data</span>
          </a>
        </li>
      </ul>
    </li><!-- End Registrasi Murid -->

 <!-- Menu Murid-->
 <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#menu-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Menu</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="menu-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="input_pembayaran_murid.php">
            <i class="bi bi-circle"></i>
            <span>Input Pembayaran</span>
          </a>
        </li>
        <li>
          <a href="view_registrasi_murid.php">
            <i class="bi bi-circle"></i>
            <span>Data Jadwal</span>
          </a>
        </li>
      </ul>
    </li><!-- End Menu -->


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
        <h1>Input Registrasi Murid</h1>
    </div><!-- End Page Title -->

    <form method="POST" action="">
        <div class="card p-5 mb-5">
            <!-- ID Murid (Otomatis) -->
            <div class="mb-3">
                <label>ID Murid</label>
                <input type="text" class="form-control" name="id_murid" value="<?= htmlspecialchars($new_id_murid) ?>" readonly>
            </div>

            <!-- Nama Murid -->
            <div class="mb-3">
                <label>Nama Murid</label>
                <input type="text" class="form-control" name="nama" required>
            </div>

            <!-- Tanggal Lahir -->
            <div class="mb-3">
                <label>Tanggal Lahir</label>
                <input type="date" class="form-control" name="tanggal_lahir" required>
            </div>

            <!-- Alamat -->
            <div class="mb-3">
                <label>Alamat</label>
                <input type="text" class="form-control" name="alamat" required>
            </div>

            <!-- Kelas -->
            <div class="mb-3">
                <label>Kelas</label>
                <input type="text" class="form-control" name="kelas" required>
            </div>

            <!-- Asal Sekolah -->
            <div class="mb-3">
                <label>Asal Sekolah</label>
                <input type="text" class="form-control" name="asal_sekolah" required>
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

            <!-- No Telepon -->
            <div class="mb-3">
                <label>No. Telepon</label>
                <input type="text" class="form-control" name="no_telp" required>
            </div>

            <!-- Tanggal Registrasi -->
            <div class="mb-3">
                <label>Tanggal Registrasi</label>
                <input type="date" class="form-control" name="tgl_reg" required>
            </div>

            <!-- Pilihan Paket -->
            <div class="mb-3">
                <label>Pilih Paket Bimbel</label>
                <select class="form-control" name="id_paket" required>
                    <?php while ($row = $result_paket->fetch_assoc()) { ?>
                        <option value="<?= $row['id_paket'] ?>"><?= $row['paket'] ?></option>
                    <?php } ?>
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
