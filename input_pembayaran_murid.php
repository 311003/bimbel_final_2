<?php
include 'connection.php'; // Koneksi ke database

// **1. Generate ID Murid Otomatis**
$query_id = "SELECT LPAD(COALESCE(MAX(CAST(id_murid AS UNSIGNED)) + 1, 1), 3, '0') AS id_murid FROM master_murid";
$result = $conn->query($query_id);
$row = $result->fetch_assoc();
$new_id_murid = $row['id_murid']; // ID Murid baru

// **2. Ambil Data Paket Bimbel**
$query_paket = "SELECT id_paket, paket, biaya FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

// **3. Jika Form Disubmit**
if (isset($_POST['tambah'])) {
    $id_murid = isset($_POST['id_murid']) ? $_POST['id_murid'] : $new_id_murid; // Otomatis jika kosong
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $kelas = $_POST['kelas'];
    $asal_sekolah = $_POST['asal_sekolah'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $no_telp = $_POST['no_telp'];
    $id_paket = $_POST['id_paket'];
    $biaya = $_POST['biaya'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $keterangan = $_POST['keterangan'];

    // **4. Validasi Input Tidak Boleh Kosong**
    if (empty($id_murid) || empty($nama) || empty($tanggal_lahir) || empty($alamat) || empty($kelas) || 
        empty($asal_sekolah) || empty($jenis_kelamin) || empty($no_telp) || empty($id_paket) || 
        empty($biaya) || empty($metode_pembayaran) || empty($keterangan)) {
        echo "<script>alert('Harap isi semua data!'); window.history.back();</script>";
        exit();
    }

    // **5. Mulai Transaksi**
    $conn->begin_transaction();

    try {
        // **6. Masukkan Data Murid ke `master_murid` jika belum ada**
        $query_check_murid = "SELECT id_murid FROM master_murid WHERE id_murid = ?";
        $stmt_check = $conn->prepare($query_check_murid);
        $stmt_check->bind_param("s", $id_murid);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows == 0) {
            // Jika murid belum ada, tambahkan ke database
            $query_insert_murid = "INSERT INTO master_murid (id_murid, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_murid = $conn->prepare($query_insert_murid);
            $stmt_murid->bind_param("ssssssss", $id_murid, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp);
            $stmt_murid->execute();
            $stmt_murid->close();
        }
        $stmt_check->close();

        // **7. Masukkan Data Pembayaran ke `pembayaran`**
        $query_insert_pembayaran = "INSERT INTO pembayaran (id_paket, id_murid, biaya, metode_pembayaran, keterangan) 
                                    VALUES (?, ?, ?, ?, ?)";
        $stmt_pembayaran = $conn->prepare($query_insert_pembayaran);
        $stmt_pembayaran->bind_param("ssdss", $id_paket, $id_murid, $biaya, $metode_pembayaran, $keterangan);
        $stmt_pembayaran->execute();
        $stmt_pembayaran->close();

        // **8. Commit Transaksi**
        $conn->commit();
        echo "<script>alert('Pembayaran berhasil ditambahkan!'); window.location.href='hasil_data_pembayaran.php';</script>";
    } catch (Exception $e) {
        // Rollback jika terjadi kesalahan
        $conn->rollback();
        echo "<script>alert('Terjadi kesalahan saat menyimpan data! Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
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
        <h1>Input Pembayaran</h1>
    </div><!-- End Page Title -->

</select>

    <!-- Form Pembayaran -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="card p-5 mb-5">

           <!-- ID Paket (Otomatis berdasarkan murid) -->
           <div class="form-group mb-3">
                <label for="id_paket">ID Paket</label>
                <input type="text" class="form-control" id="id_paket" name="id_paket" readonly>
            </div>
        
             <!-- ID Murid (Otomatis berdasarkan murid) -->
           <div class="form-group mb-3">
                <label for="id_murid">ID Murid</label>
                <input type="text" class="form-control" id="id_murid" name="id_murid" readonly>
            </div>

        <!-- Pilihan Paket Bimbel -->
<div class="mb-3">
    <label>Pilih Paket:</label>
    <select class="form-control" name="id_paket" id="id_paket" required onchange="updateBiaya()">
        <option value="">Pilih Paket</option>
        <?php 
        if ($result_paket->num_rows > 0) {
            while ($paket = $result_paket->fetch_assoc()): 
                // Pastikan `biaya` tidak null untuk mencegah error
                $biaya = isset($paket['biaya']) ? $paket['biaya'] : 0;
        ?>
            <option value="<?= htmlspecialchars($paket['id_paket']) ?>" data-biaya="<?= htmlspecialchars($biaya) ?>">
                <?= htmlspecialchars($paket['paket']) ?>
            </option>
        <?php 
            endwhile; 
        } else {
            echo "<option value=''>Data paket tidak ditemukan</option>";
        }
        ?>
    </select>
</div>

           <!-- Biaya -->
<div class="form-group mb-3">
    <label for="biaya">Biaya</label>
    <input type="text" class="form-control" id="biaya" name="biaya">
</div>

            <!-- Metode Pembayaran -->
            <div class="form-group mb-3">
                <label for="metode_pembayaran">Metode Pembayaran</label>
                <select class="form-control" id="metode_pembayaran" name="metode_pembayaran" required>
                    <option value="">Pilih Metode Pembayaran</option>
                    <option value="Cash">Cash</option>
                    <option value="OVO">OVO</option>
                    <option value="DANA">DANA</option>
                    <option value="Go-Pay">Go-Pay</option>
                    <option value="BCA">BCA</option>
                    <option value="BNI">BNI</option>
                    <option value="BRI">BRI</option>
                    <option value="Mandiri">Mandiri</option>
                </select>
            </div>

            <!-- Keterangan Bayar -->
            <div class="form-group mb-3">
                <label for="keterangan_bayar">Keterangan</label>
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-outline-success" onclick="setKeterangan('Lunas')">Lunas</button>
                    <button type="button" class="btn btn-outline-danger" onclick="setKeterangan('Belum Lunas')">Belum Lunas</button>
                </div>
                <input type="hidden" id="keterangan_bayar" name="keterangan_bayar" required>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary" name="tambah">Submit</button>
            </div>

        </div>
    </form>

</main>

<!-- JavaScript untuk mengupdate biaya berdasarkan paket -->
<script>
function updateBiaya() {
    var select = document.getElementById('id_paket');
    var selectedOption = select.options[select.selectedIndex];
    var biaya = selectedOption.getAttribute('data-biaya') || '0'; // Default ke 0 jika kosong

    // Pastikan nilai yang ditampilkan dalam format angka dengan separator ribuan
    document.getElementById('biaya').value = formatRupiah(biaya);
}

// Fungsi untuk format angka ke Rupiah
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(angka);
}
</script>

<!-- JavaScript untuk mengatur biaya berdasarkan paket -->
<script>
function updateBiaya() {
    var select = document.getElementById('id_paket');
    var selectedOption = select.options[select.selectedIndex];
    var biaya = selectedOption.getAttribute('data-biaya') || '';
    document.getElementById('biaya').value = biaya;
}

function setKeterangan(value) {
    document.getElementById('keterangan_bayar').value = value;

    // Reset semua tombol ke warna default
    let buttons = document.querySelectorAll('.btn-group button');
    buttons.forEach(button => {
        button.classList.remove('btn-success', 'btn-danger');
        button.classList.add('btn-outline-success', 'btn-outline-danger');
    });

    // Set warna tombol aktif
    let selectedButton = [...buttons].find(btn => btn.innerText === value);
    if (selectedButton) {
        selectedButton.classList.remove('btn-outline-success', 'btn-outline-danger');
        if (value === 'Lunas') selectedButton.classList.add('btn-success');
        if (value === 'Belum Lunas') selectedButton.classList.add('btn-danger');
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
