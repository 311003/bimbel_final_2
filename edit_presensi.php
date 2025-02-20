<?php
include 'connection.php'; // Koneksi ke database

// Ambil No Registrasi dari URL
$id_jadwal = isset($_GET['id_jadwal']) ? $_GET['id_jadwal'] : '';

if ($id_jadwal) {
    // Ambil data berdasarkan No Registrasi
    $query_jadwal = "SELECT * FROM paket_bimbel WHERE id_paket = ?";
    $stmt = $conn->prepare($query_jadwal);
    $stmt->bind_param("s", $id_paket);
    $stmt->execute();
    $result_jadwal = $stmt->get_result();
    $murid = $result_jadwal->fetch_assoc();
    $stmt->close();

    // Cek apakah data ditemukan
    if (!$jadwal) {
        die("<script>alert('Data tidak ditemukan untuk ID Jadwal: $id_jadwal'); window.history.back();</script>");
    }

    // Ambil ID Jadwal untuk update jadwal
    $id_jadwal = $jadwal['id_jadwal'];
} else {
    die("<script>alert('No Registrasi tidak valid!'); window.history.back();</script>");
}

// Ambil data paket bimbel
$query_jadwal = "SELECT id_jadwal, nama_murid FROM jadwal";
$result_paket = $conn->query($query_paket);

// Jika form disubmit untuk update data
if (isset($_POST['update'])) {
    // Ambil data dari form
    $id_jadwal = $_POST['id_jadwal'];
    $nama_murid = $_POST['nama_murid'];
    $tanggal_jadwal = $_POST['tanggal_jadwal'];
    $keterangan_presensi = $_POST['keterangan_presensi'];

    // Mulai transaksi untuk keamanan
    $conn->begin_transaction();

        // **1. Update data di tabel presensi**
        $query_update_presensi = "UPDATE presensi
        SET nama_murid = ?, tanggal_jadwal= ?, keterangan_presensi = ?
        WHERE id_jadwal = ?";

        $stmt_presensi = $conn->prepare($query_update_presensi);
        if (!$stmt_presensi) {
            throw new Exception("Error preparing statement (presensi): " . $conn->error);
        }

        $stmt_presensi->bind_param("sss", $nama_murid, $tanggal_jadwal, $keterangan_presensi);
        if (!$stmt_presensi->execute()) {
            throw new Exception("Error executing update (presensi): " . $stmt_presensi->error);
        }
        $stmt_presensi->close();
    }

    $conn->close();
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
          <a href="input_presensi.php">
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

<!-- Form Edit Presensi -->
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit Data Presensi</h1>
    </div>

    <form method="POST" action="">
        <div class="card p-5 mb-5">
            
            <!-- ID Presensi -->
            <div class="mb-3">
                <label>ID Presensi</label>
                <input type="text" class="form-control" name="id_presensi" value="<?= htmlspecialchars($presensi['id_presensi']) ?>" readonly>
            </div>

            <!-- ID Jadwal -->
            <div class="mb-3">
                <label>ID Jadwal</label>
                <input type="text" class="form-control" name="id_jadwal" value="<?= htmlspecialchars($presensi['id_jadwal']) ?>" readonly>
            </div>

            <!-- Nama Murid -->
            <div class="mb-3">
                <label>Nama Murid</label>
                <input type="text" class="form-control" name="nama_murid" value="<?= htmlspecialchars($presensi['nama_murid']) ?>" readonly>
            </div>

            <!-- Keterangan Presensi -->
            <div class="form-group mb-3">
                <label for="keterangan_presensi">Keterangan Presensi</label>
                <div class="btn-group w-100" role="group" aria-label="Keterangan Presensi">
                    <button type="button" class="btn btn-outline-success" onclick="setKeterangan('Hadir')" 
                        <?= ($presensi['keterangan_presensi'] == "Hadir") ? 'style="background-color:#28a745; color:white;"' : '' ?>>Hadir</button>
                    <button type="button" class="btn btn-outline-warning" onclick="setKeterangan('Sakit')" 
                        <?= ($presensi['keterangan_presensi'] == "Sakit") ? 'style="background-color:#ffc107; color:white;"' : '' ?>>Sakit</button>
                    <button type="button" class="btn btn-outline-info" onclick="setKeterangan('Izin')" 
                        <?= ($presensi['keterangan_presensi'] == "Izin") ? 'style="background-color:#17a2b8; color:white;"' : '' ?>>Izin</button>
                    <button type="button" class="btn btn-outline-danger" onclick="setKeterangan('Absen')" 
                        <?= ($presensi['keterangan_presensi'] == "Absen") ? 'style="background-color:#dc3545; color:white;"' : '' ?>>Absen</button>
                </div>
                <input type="hidden" id="keterangan_presensi" name="keterangan_presensi" value="<?= htmlspecialchars($presensi['keterangan_presensi']) ?>" required>
            </div>

            <!-- Tombol Update -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary w-100" name="update">Update Presensi</button>
                <a href="presensi_hasil.php" class="btn btn-secondary w-100 mt-2">Batal</a>
            </div>
        </div>
    </form>
</main>

<!-- JavaScript untuk mengatur tombol keterangan -->
<script>
function setKeterangan(value) {
    document.getElementById('keterangan_presensi').value = value;

    // Reset semua tombol ke warna default
    let buttons = document.querySelectorAll('.btn-group button');
    buttons.forEach(button => {
        button.style.backgroundColor = "";
        button.style.color = "";
    });

    // Set warna tombol aktif
    let selectedButton = [...buttons].find(btn => btn.innerText === value);
    if (selectedButton) {
        if (value === 'Hadir') selectedButton.style.backgroundColor = "#28a745", selectedButton.style.color = "white";
        if (value === 'Sakit') selectedButton.style.backgroundColor = "#ffc107", selectedButton.style.color = "white";
        if (value === 'Izin') selectedButton.style.backgroundColor = "#17a2b8", selectedButton.style.color = "white";
        if (value === 'Absen') selectedButton.style.backgroundColor = "#dc3545", selectedButton.style.color = "white";
    }
}
</script>

<!-- Bootstrap JS -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>