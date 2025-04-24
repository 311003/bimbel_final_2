<?php
include 'connection.php'; // Koneksi ke database

// ✅ Fungsi untuk Membuat ID Jadwal Otomatis (01, 02, 03, dst.)
function generateIdJadwal($conn) {
  $query = "SELECT LPAD(COALESCE(MAX(CAST(id_jadwal AS UNSIGNED)) + 1, 1), 2, '0') AS id_jadwal FROM jadwal";
  $result = $conn->query($query);

  if ($result && $result->num_rows > 0) {
      $row = $result->fetch_assoc();
      return $row['id_jadwal']; // Mengembalikan ID jadwal yang baru
  } else {
      return '01'; // Default ID jika belum ada data
  }
}

// Memanggil fungsi untuk mendapatkan ID baru
$newId = generateIdJadwal($conn);

// ✅ Fungsi untuk Membuat ID Detail Jadwal Otomatis
function generateIdDetail($conn) {
    $query = "SELECT LPAD(COALESCE(MAX(CAST(id_detail AS UNSIGNED)) + 1, 1), 3, '0') AS id_detail FROM detail_jadwal";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id_detail']; // ID baru untuk detail jadwal
    } else {
        return '001'; // Default jika belum ada data
    }
}

// ✅ Ambil Data untuk Dropdown
$query_jadwal = "SELECT r.id_jadwal, r.id_paket, p.paket AS nama_paket 
                 FROM jadwal r
                 LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket";  
$result_jadwal = $conn->query($query_jadwal);

$query_guru = "SELECT id_guru, nama_guru FROM guru";
$result_guru = $conn->query($query_guru);

// Ambil hanya guru yang statusnya bukan "Tidak Aktif" (id_status_guru ≠ 2)
$query_guru = "SELECT g.id_guru, g.nama_guru 
               FROM guru g 
               WHERE g.id_status_guru != 2";
$result_guru = $conn->query($query_guru);

$query_paket = "SELECT id_paket, paket FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

// ✅ Jika Form Disubmit
if (isset($_POST['tambah_jadwal'])) {
    $id_jadwal = generateIdJadwal($conn); // Auto-generate ID jadwal
    $id_guru_list = $_POST['id_guru']; // Array ID guru
    $id_paket = $_POST['id_paket'];
    $tanggal_jadwal = $_POST['tanggal_jadwal'];
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = $_POST['jam_keluar'];

    // Validasi Input
    if (empty($id_guru_list) || empty($id_paket) || empty($tanggal_jadwal) || empty($jam_masuk) || empty($jam_keluar)) {
        echo "<script>alert('Harap isi semua data!'); window.history.back();</script>";
        exit();
    }

    // ✅ Masukkan Data ke Tabel `jadwal`
    $query_insert_jadwal = "INSERT INTO jadwal (id_jadwal, id_guru, id_paket, tanggal_jadwal, jam_masuk, jam_keluar) 
                            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_jadwal = $conn->prepare($query_insert_jadwal);
    $stmt_jadwal->bind_param("ssssss", $id_jadwal, $id_guru_list, $id_paket, $tanggal_jadwal, $jam_masuk, $jam_keluar);

    if (!$stmt_jadwal->execute()) {
        die("Gagal menambahkan jadwal: " . $stmt_jadwal->error);
    }
    $stmt_jadwal->close();

    // ✅ Masukkan Data ke Tabel `detail_jadwal`
    $query_insert_detail = "INSERT INTO detail_jadwal (id_jadwal, id_guru, id_paket, tanggal_jadwal, jam_masuk, jam_keluar) 
    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_detail = $conn->prepare($query_insert_detail); // Gunakan variabel baru $stmt_detail

    foreach ($id_guru_list as $id_guru) {
    $stmt_detail->bind_param("ssssss", $id_jadwal, $id_paket, $id_guru_list, $tanggal_jadwal, $jam_masuk, $jam_keluar);

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
        <h1>Input Jadwal</h1>
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
                <a href="hasil_data_registrasi.php" class="btn btn-secondary">Batal</a>
            </div>

        </form>
    </div>
</main>

<!-- JavaScript untuk mengisi Nama Guru Otomatis -->
<script>
function autofillNamaGuru(selectElement) {
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    document.getElementById("nama_guru").value = selectedOption.getAttribute("data-nama") || "";
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

</body>
</html>