<?php
include 'connection.php'; // Koneksi ke database

// Ambil daftar murid yang sudah terdaftar beserta jadwalnya
$query_murid = "SELECT r.id_murid, r.nama AS nama_murid, j.id_jadwal, j.tanggal_jadwal
                FROM registrasi_murid r
                LEFT JOIN jadwal j ON r.id_murid = j.id_murid
                ORDER BY j.tanggal_jadwal DESC";
$result_murid = $conn->query($query_murid);

// Jika form disubmit untuk tambah presensi
if (isset($_POST['tambah_presensi'])) {
    if (!isset($_POST['id_jadwal']) || !isset($_POST['keterangan_presensi'])) {
        die("<script>alert('Harap pilih murid dan isi keterangan presensi!'); window.history.back();</script>");
    }

    $id_jadwal = $_POST['id_jadwal'];
    $keterangan_presensi = $_POST['keterangan_presensi'];

    // Ambil nama murid berdasarkan id_jadwal
    $query_get_murid = "SELECT r.nama FROM registrasi_murid r
                        JOIN jadwal j ON r.id_murid = j.id_murid
                        WHERE j.id_jadwal = ?";
    $stmt_murid = $conn->prepare($query_get_murid);
    $stmt_murid->bind_param("s", $id_jadwal);
    $stmt_murid->execute();
    $result_murid = $stmt_murid->get_result();
    $data_murid = $result_murid->fetch_assoc();
    $stmt_murid->close();

    if (!$data_murid) {
        die("<script>alert('Nama murid tidak ditemukan! Pastikan ID Jadwal valid.'); window.history.back();</script>");
    }

    $nama_murid = $data_murid['nama'];

    // **Cek apakah data dengan id_jadwal sudah ada di presensi**
    $query_check = "SELECT id_jadwal FROM presensi WHERE id_jadwal = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("s", $id_jadwal);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        die("<script>alert('Data presensi untuk jadwal ini sudah ada!'); window.history.back();</script>");
    }
    $stmt_check->close();

    // **INSERT Data ke Tabel `presensi` tanpa menyertakan `id_presensi`**
    $query_insert = "INSERT INTO presensi (id_jadwal, nama_murid, keterangan_presensi) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query_insert);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("sss", $id_jadwal, $nama_murid, $keterangan_presensi);

    if ($stmt->execute()) {
        echo "<script>alert('Presensi berhasil ditambahkan!'); window.location.href='presensi_hasil.php';</script>";
    } else {
        die("Gagal menambahkan presensi: " . $stmt->error);
    }

    $stmt->close();
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

<main id="main" class="main">
    <div class="pagetitle">
      <h1>Input Presensi</h1>
    </div><!-- End Page Title -->

    <div class="card p-5 mb-5">
      <form method="POST" action="presensi_input.php">

        <!-- Pilih Murid -->
        <div class="form-group mb-3">
            <label for="id_murid">Pilih Murid</label>
            <select class="form-control" id="id_murid" name="id_murid" onchange="updateJadwal()" required>
                <option value="">-- Pilih Murid --</option>
                <?php while ($row = $result_murid->fetch_assoc()): ?>
                    <option value="<?= $row['id_jadwal']; ?>" data-jadwal="<?= $row['tanggal_jadwal']; ?>">
                        <?= $row['nama_murid']; ?> - (Jadwal: <?= $row['tanggal_jadwal']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- ID Jadwal (Otomatis muncul setelah memilih murid) -->
        <div class="form-group mb-3">
            <label for="id_jadwal">ID Jadwal</label>
            <input type="text" class="form-control" id="id_jadwal" name="id_jadwal" readonly required>
        </div>

        <!-- Nama Murid -->
        <div class="form-group mb-3">
            <label for="nama_murid">Nama Murid</label>
            <input type="text" class="form-control" id="nama_murid" name="nama_murid" readonly required>
        </div>

        <!-- Keterangan Presensi -->
        <div class="form-group mb-3">
            <label for="keterangan_presensi">Keterangan Presensi</label>
            <div class="btn-group w-100" role="group">
                <button type="button" class="btn btn-outline-success" onclick="setKeterangan('Hadir')">Hadir</button>
                <button type="button" class="btn btn-outline-warning" onclick="setKeterangan('Sakit')">Sakit</button>
                <button type="button" class="btn btn-outline-info" onclick="setKeterangan('Izin')">Izin</button>
                <button type="button" class="btn btn-outline-danger" onclick="setKeterangan('Absen')">Absen</button>
            </div>
            <input type="hidden" id="keterangan_presensi" name="keterangan_presensi" required>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-primary w-100" name="tambah_presensi">Tambah Presensi</button>
            <a href="presensi_hasil.php" class="btn btn-secondary w-100 mt-2">Batal</a>
        </div>

      </form>
    </div>
</main>

<!-- JavaScript -->
<script>
function setKeterangan(value) {
    document.getElementById('keterangan_presensi').value = value;
    let buttons = document.querySelectorAll('.btn-group button');
    buttons.forEach(button => {
        button.classList.remove('btn-success', 'btn-warning', 'btn-info', 'btn-danger');
        button.classList.add('btn-outline-success', 'btn-outline-warning', 'btn-outline-info', 'btn-outline-danger');
    });

    let selectedButton = [...buttons].find(btn => btn.innerText === value);
    if (selectedButton) {
        selectedButton.classList.remove('btn-outline-success', 'btn-outline-warning', 'btn-outline-info', 'btn-outline-danger');
        if (value === 'Hadir') selectedButton.classList.add('btn-success');
        if (value === 'Sakit') selectedButton.classList.add('btn-warning');
        if (value === 'Izin') selectedButton.classList.add('btn-info');
        if (value === 'Absen') selectedButton.classList.add('btn-danger');
    }
}

function updateJadwal() {
    let select = document.getElementById("id_murid");
    let idJadwal = select.value;
    document.getElementById("id_jadwal").value = idJadwal ? idJadwal : "";
}
</script>

<!-- Vendor JS Files -->
<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
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

</body>
</html>