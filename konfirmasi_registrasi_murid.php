<?php
include 'connection.php'; // Koneksi database

$query = "SELECT 
             r.no_reg,
             r.nama,
             r.tanggal_lahir,
             r.alamat,
             r.kelas,
             r.asal_sekolah,
             r.jenis_kelamin,
             r.no_telp,
             p.paket,
             r.konfirmasi_registrasi,
             r.konfirmasi_registrasi_murid  -- Ensure this column is selected
          FROM registrasi_murid r
          LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket";

// Jika tombol validasi ditekan
if (isset($_POST['validasi'])) {
    $no_reg = $_POST['no_reg'];
    $query_update = "UPDATE registrasi_murid SET konfirmasi_registrasi = 'Divalidasi' WHERE no_reg = ?";
    $stmt = $conn->prepare($query_update);
    $stmt->bind_param("s", $no_reg);
    $stmt->execute();
    echo "<script>alert('Murid berhasil divalidasi!'); window.location.href='konfirmasi_registrasi_murid.php';</script>";
    exit();
}

// Jika tombol batalkan ditekan
if (isset($_POST['batalkan'])) {
    $no_reg = $_POST['no_reg'];
    $query_delete = "DELETE FROM registrasi_murid WHERE no_reg = ?";
    $stmt = $conn->prepare($query_delete);
    $stmt->bind_param("s", $no_reg);
    $stmt->execute();
    echo "<script>alert('Murid berhasil dibatalkan!'); window.location.href='konfirmasi_registrasi_murid.php';</script>";
    exit();
}

// Ambil semua data registrasi murid dari database dengan nama paket
$query = "SELECT r.*, p.paket 
          FROM registrasi_murid r
          LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket";
$result = $conn->query($query);

// Ambil data murid yang ikut bimbel (konfirmasi_registrasi = 'Divalidasi')
$query_valid = "SELECT * FROM registrasi_murid WHERE konfirmasi_registrasi = 'Divalidasi'";
$result_valid = $conn->query($query_valid);

// Ambil data murid yang sudah dibatalkan
$query_batal = "SELECT r.*, p.paket 
                FROM registrasi_murid r
                LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
                WHERE r.konfirmasi_registrasi = 'Dibatalkan'";
$result_batal = $conn->query($query_batal);

// Tutup koneksi setelah semua query selesai
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
  </div>
      <header id="header" class="header fixed-top d-flex align-items-center">
        <img src="assets/img/logo_bimbel.png" alt="Logo Bimbel XYZ"
            style="height: 60px; width: auto; display: block;">
        <span class="d-none d-lg-block ms-3 fs-4">Bimbel XYZ</span>
      </div>
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
          <a href="konfirmasi_registrasi_murid.php">
            <i class="bi bi-circle"></i>
            <span>Hasil Data</span>
          </a>
        </li>
      </ul>
    </li><!-- End Registrasi Murid -->

    <!-- Pembayaran -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#pembayaran-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Pembayaran</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="pembayaran-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="input_pembayaran_murid.php">
            <i class="bi bi-circle"></i>
            <span>Input Pembayaran</span>
          </a>
        </li>
      </ul>
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
          <a href="input_presensi_murid.php">
            <i class="bi bi-circle"></i>
            <span>Input</span>
          </a>
        </li>
        <li>
          <a href="hasil_presensi_murid.php">
            <i class="bi bi-circle"></i>
            <span>Hasil Data</span>
          </a>
        </li>
      </ul>
    </li><!-- End Presensi -->

 <!-- Menu Murid-->
 <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#menu-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Menu</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="menu-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="view_jadwal_murid.php">
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
    <div class="container mt-4">
        <h4 class="mt-4">📌 Konfirmasi Data Registrasi Murid</h4>
        <table class="table table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>No Registrasi</th>
                    <th>Nama</th>
                    <th>Tanggal Lahir</th>
                    <th>Alamat</th>
                    <th>Kelas</th>
                    <th>Asal Sekolah</th>
                    <th>Jenis Kelamin</th>
                    <th>No Telepon</th>
                    <th>Paket</th>
                    <th>Konfirmasi Registrasi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($row['no_reg']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= htmlspecialchars($row['tanggal_lahir']) ?></td>
                        <td><?= htmlspecialchars($row['alamat']) ?></td>
                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                        <td><?= htmlspecialchars($row['asal_sekolah']) ?></td>
                        <td><?= ($row['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan' ?></td>
                        <td><?= htmlspecialchars($row['no_telp']) ?></td>
                        <td><?= !empty($row['paket']) ? htmlspecialchars($row['paket']) : '<i>Tidak Ditemukan</i>' ?></td>
                        <td>
                            <?php 
                                if ($row['konfirmasi_registrasi'] == 'Divalidasi') {
                                    echo "<span class='badge bg-success'>Divalidasi</span>";
                                } elseif ($row['konfirmasi_registrasi'] == 'Dibatalkan') {
                                    echo "<span class='badge bg-danger'>Dibatalkan</span>";
                                } else {
                                    echo "<span class='badge bg-secondary'>Belum Diproses</span>";
                                }
                            ?>
                        </td>
        
                            <?php 
                                if (isset($row['konfirmasi_registrasi_murid'])) {
                                    if ($row['konfirmasi_registrasi_murid'] != 'Divalidasi') { ?>
                                        <a href="#" onclick="konfirmasiValidasi('<?= urlencode($row['no_reg']) ?>')" class="btn btn-success btn-sm">
                                            Validasi
                                        </a>
                                    <?php }
                                    if ($row['konfirmasi_registrasi_murid'] != 'Dibatalkan') { ?>
                                        <a href="#" onclick="konfirmasiBatalkan('<?= urlencode($row['no_reg']) ?>')" class="btn btn-danger btn-sm">
                                            Batalkan
                                        </a>
                                    <?php }
                                } else {
                                }
                            ?>
                        </td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</main>
</body>
</html>

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