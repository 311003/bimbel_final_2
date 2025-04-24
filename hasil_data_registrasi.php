<?php
include 'connection.php'; // Koneksi database

$result = null; // ✅ Pastikan $result selalu terdefinisi

if (isset($_GET['no_reg'])) {
    $no_reg = $_GET['no_reg'];

    // Debug: cek apakah no_reg ada
    echo "<script>console.log('No Reg: " . $no_reg . "');</script>";

    // 1. Update status murid menjadi "Divalidasi"
    $query_update = "UPDATE registrasi_murid SET status = 'Divalidasi' WHERE no_reg = ?";
    $stmt = $conn->prepare($query_update);
    $stmt->bind_param("s", $no_reg);

    if (!$stmt->execute()) {
        die("Error Update: " . $stmt->error); // Debugging error update
    }

    // 2. Pindahkan data ke registrasi_valid setelah berhasil update status
    $query_insert = "INSERT INTO registrasi_valid (no_reg, id_murid, tgl_reg, nama, tanggal_lahir, alamat, kelas, asal_sekolah, id_paket, jenis_kelamin, no_telp, status)
                     SELECT no_reg, id_murid, tgl_reg, nama, tanggal_lahir, alamat, kelas, asal_sekolah, id_paket, jenis_kelamin, no_telp, status
                     FROM registrasi_murid WHERE no_reg = ?";
    
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param("s", $no_reg);

    if (!$stmt_insert->execute()) {
        die("Error Insert: " . $stmt_insert->error); // Debugging error insert
    }

    // 3. Periksa status murid setelah dipindahkan
    $check_query = "SELECT status FROM registrasi_valid WHERE no_reg = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("s", $no_reg);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();

    if ($row['status'] == 'Dibatalkan') {
        // 4. Pindahkan data ke tabel registrasi_batal
        $insert_query = "INSERT INTO registrasi_batal (no_reg, id_murid, tgl_reg, nama, tanggal_lahir, alamat, kelas, asal_sekolah, id_paket, jenis_kelamin, no_telp, status)
                         SELECT no_reg, id_murid, tgl_reg, nama, tanggal_lahir, alamat, kelas, asal_sekolah, id_paket, jenis_kelamin, no_telp, status
                         FROM registrasi_valid WHERE no_reg = ?";
        $stmt_insert_batal = $conn->prepare($insert_query);
        $stmt_insert_batal->bind_param("s", $no_reg);
        $stmt_insert_batal->execute();

        // 5. Hapus dari registrasi_valid setelah dipindahkan
        $delete_query = "DELETE FROM registrasi_valid WHERE no_reg = ?";
        $stmt_delete = $conn->prepare($delete_query);
        $stmt_delete->bind_param("s", $no_reg);
        $stmt_delete->execute();

        echo "<script>alert('Murid dibatalkan dan dipindahkan ke daftar registrasi batal!'); window.location.href='view_konfirmasi_registrasi.php';</script>";
        exit;
    }

    echo "<script>alert('Murid berhasil divalidasi!'); window.location.href='view_konfirmasi_registrasi.php';</script>";
    echo "<script>alert('Murid berhasil divalidasi!'); window.location.href='hasil_data_registrasi.php';</script>";

    // 6. Ambil data murid yang sudah divalidasi
    $query = "SELECT rv.no_reg, rv.nama, rv.tanggal_lahir, rv.alamat, rv.kelas, rv.asal_sekolah, rv.jenis_kelamin, rv.no_telp, pb.id_paket
              FROM registrasi_valid rv
              JOIN paket_bimbel pb ON rv.id_paket = pb.id_paket
              WHERE rv.status = 'Divalidasi'";

    // 7. Jalankan query setelah data validasi diperbarui
    $result = $conn->query($query);

    if (!$result) {
        die("Query Error: " . $conn->error);
    }

    // 8. Debugging jumlah data yang ditemukan
    echo "<p>Jumlah data ditemukan: " . $result->num_rows . "</p>";

    // 9. Tutup statement
    $stmt->close();
    $stmt_insert->close();
    $stmt_check->close();
    $conn->close();
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
      <a class="nav-link" href="hasil_data_pembayaran.php">
        <i class="bi bi-cash"></i>
        <span>Pembayaran</span>
      </a>
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
    <div class="container mt-4">
        <h2 class="text-center">Data Murid Bimbel</h2>

        <h4 class="mt-4 text-success">✅ Murid yang Jadi Ikut Bimbel</h4>

        <table class="table table-bordered">
            <thead class="table-success">
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
                </tr>
            </thead>
            <tbody>
            <?php
            if ($result && $result->num_rows > 0) { // ✅ Pastikan $result tidak null atau error
                while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['no_reg']) . "</td>";
            echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tanggal_lahir']) . "</td>";
            echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
            echo "<td>" . htmlspecialchars($row['kelas']) . "</td>";
            echo "<td>" . htmlspecialchars($row['asal_sekolah']) . "</td>";
            echo "<td>" . ($row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan') . "</td>";
            echo "<td>" . htmlspecialchars($row['no_telp']) . "</td>";
            echo "<td>" . htmlspecialchars($row['id_paket']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='9' class='text-center'>Belum ada murid yang divalidasi</td></tr>";
    }
    ?>
</tbody>


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