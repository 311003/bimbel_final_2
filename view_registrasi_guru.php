<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include

$query = "SELECT r.no_reg, r.id_murid, r.tgl_reg, r.nama, r.tanggal_lahir, r.alamat, 
                 r.jenis_kelamin, r.kelas, r.asal_sekolah, r.no_telp, p.paket AS nama_paket
          FROM registrasi_murid r
          LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket";

$result = $conn->query($query);
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
      <h1>Hasil Data Registrasi</h1>
    </div><!-- End Page Title -->

   <!-- Begin Page Content -->
<div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No Registrasi</th>
                        <th>ID Murid</th>
                        <th>Tanggal Registrasi</th>
                        <th>Nama</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Jenis Kelamin</th>
                        <th>Kelas</th>
                        <th>Asal Sekolah</th>
                        <th>Paket</th>
                        <th>Nomor Telepon</th>                                   
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['no_reg'] . "</td>";
                            echo "<td>" . $row['id_murid'] . "</td>";
                            echo "<td>" . $row['tgl_reg'] . "</td>";
                            echo "<td>" . $row['nama'] . "</td>";
                            echo "<td>" . $row['tanggal_lahir'] . "</td>";
                            echo "<td>" . $row['alamat'] . "</td>";
                            echo "<td>" . $row['jenis_kelamin'] . "</td>";
                            echo "<td>" . $row['kelas'] . "</td>";
                            echo "<td>" . $row['asal_sekolah'] . "</td>";
                            echo "<td>" . $row['nama_paket'] . "</td>"; // Tampilkan nama paket
                            echo "<td>" . $row['no_telp'] . "</td>";
                            echo "<td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>Data tidak ditemukan</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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