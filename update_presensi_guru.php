<?php
include 'connection.php'; // Koneksi ke database

// Ambil Id Presensi dari URL
$id_presensi = isset($_GET['id_presensi']) ? $_GET['id_presensi'] : '';

if ($id_jadwal) {
    // Ambil data berdasarkan Jadwal
    $query_murid = "SELECT * FROM presensi WHERE id_jadwal = ?";
    $stmt = $conn->prepare($query_murid);
    $stmt->bind_param("s", $id_jadwal);
    $stmt->execute();
    $result_presensi = $stmt->get_result();
    $presensi = $result_presensi->fetch_assoc();
    $stmt->close();

    // Cek apakah data ditemukan
    if (!$presensi) {
        die("<script>alert('Data tidak ditemukan untuk Presensi: $id_presensi'); window.history.back();</script>");
    }

    // Ambil ID Murid untuk update master_murid
    $id_presensi = $presensi['id_presensi'];
} else {
    die("<script>alert('Id Presensi tidak valid!'); window.history.back();</script>");
}

// Ambil data paket bimbel
$query_murid = "SELECT id_murid, paket FROM paket_bimbel";
$result_murid = $conn->query($query_murid);

// Jika form disubmit untuk update data
if (isset($_POST['update'])) {
    // Ambil data dari form
    $id_jadwal = $_POST['id_jadwal'];
    $keterangan_presensi = $_POST['keterangan_presensi'];

    // Mulai transaksi untuk keamanan
    $conn->begin_transaction();

    try {
        // **1. Update data di tabel registrasi_murid**
        $query_update_presensi = "UPDATE presensi
        SET id_presensi = ?, id_jadwal = ?, keterangan_presensi = ?
        WHERE id_presensi = ?";

        $stmt_presensi = $conn->prepare($query_update_presensi);
        if (!$stmt_presensi) {
            throw new Exception("Error preparing statement (presensi): " . $conn->error);
        }

        $stmt_presensi->bind_param("ss", $id_jadwal, $keterangan_presensi);
        if (!$stmt_presensi->execute()) {
            throw new Exception("Error executing update (presensi): " . $stmt_presensi->error);
        }
        $stmt_presensi->close();

        // **2. Update data di tabel master_murid**
        $query_update_master_murid = "UPDATE master_murid
        SET id_presensi = ?, id_jadwal = ?, keterangan_presensi = ?
        WHERE id_jadwal = ?";

        $stmt_master_murid = $conn->prepare($query_update_murid);
        if (!$stmt_master_murid) {
            throw new Exception("Error preparing statement (master_murid): " . $conn->error);
        }

        $stmt_master_murid->bind_param("ss", $id_jadwal, $keterangan_presensi);
        if (!$stmt_master_murid->execute()) {
            throw new Exception("Error executing update (master_murid): " . $stmt_master_murid->error);
        }
        $stmt_master_murid->close();

        // Commit transaksi jika semua update berhasil
        $conn->commit();
        echo "<script>alert('Data berhasil diperbarui di kedua tabel!'); window.location.href='hasil_presensi_guru.php';</script>";

    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        $conn->rollback();
        echo "<script>alert('Gagal mengupdate data! Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }

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
      <a class="nav-link" href="dashboard_guru.php">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->

   <!-- Menu Guru -->
<li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#menu-guru" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i>
          <span>Menu Guru</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="menu-guru" class="nav-content collapse" data-bs-parent="#sidebar-nav">
          <li><a href="view_jadwal_guru.php"><i class="bi bi-circle"></i><span>Hasil Data Jadwal</span></a></li>
          <li><a href="view_registrasi_guru.php"><i class="bi bi-circle"></i><span>Hasil Data Registrasi</span></a></li>
          <li><a href="view_data_guru.php"><i class="bi bi-table"></i><span>Hasil Data Guru</span></a></li>
        </ul>
      </li><!-- End Menu Guru -->

    <!-- Presensi -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#presensi-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Presensi</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="presensi-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="input_presensi_guru.php">
            <i class="bi bi-circle"></i>
            <span>Input</span>
          </a>
        </li>
        <li>
          <a href="hasil_presensi_guru.php">
            <i class="bi bi-circle"></i>
            <span>Hasil Data</span>
          </a>
        </li>
      </ul>
    </li><!-- End Presensi -->

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
        <h1>Edit Data Jadwal</h1>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="card p-5 mb-5">
            <!-- ID Jadwal -->
            <div class="mb-3">
                <label>ID Jadwal</label>
                <input type="text" class="form-control" name="id_jadwal" value="<?= htmlspecialchars($paket['id_jadwal'] ?? '') ?>" readonly>
            </div>

            <!-- Nama Murid -->
            <div class="mb-3">
                <label>Nama Murid</label>
                <input type="text" class="form-control" name="nama_murid" value="<?= htmlspecialchars($paket['nama_murid'] ?? '') ?>" readonly>
            </div>

           <!-- Keterangan Presensi -->
        <div class="form-group mb-3">
            <label for="keterangan">Keterangan Presensi</label>
            <div class="btn-group w-100" role="group" aria-label="Keterangan Presensi">
                <button type="button" class="btn btn-outline-success" onclick="setKeterangan('Hadir')">Hadir</button>
                <button type="button" class="btn btn-outline-warning" onclick="setKeterangan('Sakit')">Sakit</button>
                <button type="button" class="btn btn-outline-info" onclick="setKeterangan('Izin')">Izin</button>
                <button type="button" class="btn btn-outline-danger" onclick="setKeterangan('Absen')">Absen</button>
            </div>
            <input type="hidden" id="keterangan" name="keterangan" required>
        </div>
</main>

<!-- Tombol Update -->
<div class="text-center">
                <button type="submit" class="btn btn-primary w-100" name="update">Update Presensi</button>
                <a href="hasil_presensi_guru.php" class="btn btn-secondary w-100 mt-2">Batal</a>
            </div>
        </div>
    </form>
</main>

<!-- JavaScript untuk mengatur tombol keterangan -->
<script>
function setKeterangan(value) {
    document.getElementById('keterangan').value = value;

    // Reset semua tombol ke warna default
    let buttons = document.querySelectorAll('.btn-group button');
    buttons.forEach(button => {
        button.classList.remove('btn-success', 'btn-warning', 'btn-info', 'btn-danger');
        button.classList.add('btn-outline-success', 'btn-outline-warning', 'btn-outline-info', 'btn-outline-danger');
    });

    // Set warna tombol aktif
    let selectedButton = [...buttons].find(btn => btn.innerText === value);
    if (selectedButton) {
        selectedButton.classList.remove('btn-outline-success', 'btn-outline-warning', 'btn-outline-info', 'btn-outline-danger');
        if (value === 'Hadir') selectedButton.classList.add('btn-success');
        if (value === 'Sakit') selectedButton.classList.add('btn-warning');
        if (value === 'Izin') selectedButton.classList.add('btn-info');
        if (value === 'Absen') selectedButton.classList.add('btn-danger');
    }
}
</script>

<!-- Bootstrap JS -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>