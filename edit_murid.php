<?php
include 'connection.php';

// Debugging: Display errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek parameter id_murid
if (isset($_GET['id_murid'])) {
  $id_murid = mysqli_real_escape_string($conn, $_GET['id_murid']);

  $query_select = "SELECT mm.*, sm.id_status_murid, sm.status_murid
                   FROM master_murid mm
                   LEFT JOIN status_murid sm ON mm.status_murid = sm.id_status_murid
                   WHERE mm.id_murid = ?";

  $stmt = $conn->prepare($query_select);
  $stmt->bind_param("s", $id_murid);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  $stmt->close();
}

    if (!$row) {
        echo "<script>alert('Data tidak ditemukan!'); window.location.href='master_murid.php';</script>";
        exit();
    }

// Ambil status
$statusResult = $conn->query("SELECT id_status_murid, status_murid FROM status_murid");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_murid = $_POST['id_murid'];
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $kelas = $_POST['kelas'];
    $asal_sekolah = $_POST['asal_sekolah'];
    $no_telp = $_POST['no_telp'];
    $status_murid = $_POST['status_murid'];

    $query_update = "UPDATE master_murid 
                     SET nama=?, tanggal_lahir=?, alamat=?, kelas=?, asal_sekolah=?, no_telp=?, status_murid=? 
                     WHERE id_murid=?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("sssssssi", $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $no_telp, $status_murid, $id_murid);
    $stmt_update->execute();
    $stmt_update->close();

    echo "<script>alert('Data berhasil diperbarui!'); window.location.href='master_murid.php';</script>";
    exit();
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
        <h1>Edit Murid</h1>
    </div>

    <div class="card p-5 mb-5">
        <form method="POST" action="">

            <!-- ID Murid (Read-Only) -->
            <div class="form-group mb-3">
                <label for="id_murid">ID Murid</label>
                <input type="text" class="form-control" id="id_murid" name="id_murid" 
                       value="<?= htmlspecialchars($row['id_murid']) ?>" readonly>
            </div>

            <!-- Nama Murid -->
            <div class="form-group mb-3">
                <label for="nama">Nama Murid</label>
                <input type="text" class="form-control" id="nama" name="nama" 
                       value="<?= htmlspecialchars($row['nama']) ?>" required>
            </div>

            <!-- Tanggal Lahir -->
            <div class="form-group mb-3">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                       value="<?= htmlspecialchars($row['tanggal_lahir']) ?>" required>
            </div>

            <!-- Alamat -->
            <div class="form-group mb-3">
                <label for="alamat">Alamat</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="2" required><?= htmlspecialchars($row['alamat']) ?></textarea>
            </div>

            <!-- No Telepon -->
            <div class="form-group mb-3">
                <label for="no_telp">No Telepon</label>
                <input type="text" class="form-control" id="no_telp" name="no_telp" 
                       value="<?= htmlspecialchars($row['no_telp']) ?>" required>
            </div>

            <!-- Kelas -->
            <div class="form-group mb-3">
                <label for="kelas">Kelas</label>
                <input type="text" class="form-control" id="kelas" name="kelas" 
                       value="<?= htmlspecialchars($row['kelas']) ?>" required>
            </div>

            <!-- Asal Sekolah -->
            <div class="form-group mb-3">
                <label for="asal_sekolah">Asal Sekolah</label>
                <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah" 
                       value="<?= htmlspecialchars($row['asal_sekolah']) ?>" required>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="master_murid.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>

<script>
// Fungsi untuk mengupdate status murid dengan AJAX
function updateStatus(id_murid, status_murid) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "update_status_murid.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                alert("Status berhasil diperbarui!");
            } else {
                alert("Terjadi kesalahan: " + xhr.responseText);
            }
        }
    };

    let data = "id_murid=" + encodeURIComponent(id_murid) + "&status_murid=" + encodeURIComponent(status_murid);
    xhr.send(data);
}

// Update status saat user memilih status baru
function handleUpdate(selectElement, id_murid, type) {
    let value = selectElement.value;

    if (type === 'status') {
        // Update status murid
        updateStatus(id_murid, value);
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