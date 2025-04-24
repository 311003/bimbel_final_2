<?php
include 'connection.php'; // Koneksi ke database

// Ambil No Registrasi dari URL
$no_reg = isset($_GET['no_reg']) ? $_GET['no_reg'] : '';

if ($no_reg) {
    // Ambil data berdasarkan No Registrasi
    $query_murid = "SELECT * FROM registrasi_murid WHERE no_reg = ?";
    $stmt = $conn->prepare($query_murid);
    $stmt->bind_param("s", $no_reg);
    $stmt->execute();
    $result_murid = $stmt->get_result();
    $murid = $result_murid->fetch_assoc();
    $stmt->close();

    // Cek apakah data ditemukan
    if (!$murid) {
        die("<script>alert('Data tidak ditemukan untuk No Registrasi: $no_reg'); window.history.back();</script>");
    }

    // Ambil ID Murid untuk update master_murid
    $id_murid = $murid['id_murid'];
} else {
    die("<script>alert('No Registrasi tidak valid!'); window.history.back();</script>");
}

// Ambil data paket bimbel
$query_paket = "SELECT id_paket, paket FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

// Jika form disubmit untuk update data
if (isset($_POST['update'])) {
    // Ambil data dari form
    $no_reg = $_POST['no_reg'];
    $id_murid = $_POST['id_murid'];
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $kelas = $_POST['kelas'];
    $asal_sekolah = $_POST['asal_sekolah'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tgl_reg = $_POST['tgl_reg'];
    $no_telp = $_POST['no_telp'];
    $id_paket = $_POST['id_paket'];
    $status = $_POST['status'];

    // Mulai transaksi untuk keamanan
    $conn->begin_transaction();

    try {
        // **1. Update data di tabel registrasi_murid**
        $query_update_registrasi = "UPDATE registrasi_murid 
        SET id_murid = ?, tgl_reg = ?, nama = ?, tanggal_lahir = ?, alamat = ?, kelas = ?, asal_sekolah = ?, jenis_kelamin = ?, no_telp = ?, id_paket = ?, id_status_murid = ?
        WHERE no_reg = ?";

        $stmt_registrasi = $conn->prepare($query_update_registrasi);
        if (!$stmt_registrasi) {
            throw new Exception("Error preparing statement (registrasi_murid): " . $conn->error);
        }

        $stmt_registrasi->bind_param("ssssssssssss", $id_murid, $tgl_reg, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp, $id_paket, $no_reg, $id_status_murid);
        if (!$stmt_registrasi->execute()) {
            throw new Exception("Error executing update (registrasi_murid): " . $stmt_registrasi->error);
        }
        $stmt_registrasi->close();

        // **2. Update data di tabel master_murid**
        $query_update_master_murid = "UPDATE master_murid 
        SET nama = ?, tanggal_lahir = ?, alamat = ?, kelas = ?, asal_sekolah = ?, jenis_kelamin = ?, no_telp = ?, id_status_murid = ? 
        WHERE id_murid = ?";

        $stmt_master_murid = $conn->prepare($query_update_master_murid);
        if (!$stmt_master_murid) {
            throw new Exception("Error preparing statement (master_murid): " . $conn->error);
        }

        $stmt_master_murid->bind_param("ssssssss", $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp, $id_murid);
        if (!$stmt_master_murid->execute()) {
            throw new Exception("Error executing update (master_murid): " . $stmt_master_murid->error);
        }
        $stmt_master_murid->close();

        // Commit transaksi jika semua update berhasil
        $conn->commit();
        echo "<script>alert('Data berhasil diperbarui di kedua tabel!'); window.location.href='hasil_data_registrasi.php';</script>";

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
        <h1>Edit Data Murid</h1>
    </div>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="card p-5 mb-5">
            <!-- No Registrasi -->
            <div class="mb-3">
                <label>No Registrasi</label>
                <input type="text" class="form-control" name="no_reg" value="<?= htmlspecialchars($murid['no_reg'] ?? '') ?>" readonly>
            </div>

            <!-- Tanggal Registrasi -->
            <div class="form-group mb-3">
                <label for="tgl_reg">Tanggal Registrasi</label>
                <input type="date" class="form-control" id="tgl_reg" name="tgl_reg" value="<?= isset($murid['tgl_reg']) ? date('Y-m-d', strtotime($murid['tgl_reg'])) : '' ?>" required>
            </div>
            <!-- Nama Murid -->
            <div class="mb-3">
                <label>ID murid</label>
                <input type="text" class="form-control" name="id_murid" value="<?= htmlspecialchars($murid['id_murid'] ?? '') ?>" readonly>
            </div>

            <!-- Nama Murid -->
            <div class="mb-3">
                <label>Nama Murid</label>
                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($murid['nama'] ?? '') ?>" required>
            </div>

            <!-- Tanggal Lahir -->
            <div class="form-group mb-3">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= $murid['tanggal_lahir'] ?? '' ?>" required>
            </div>

            <!-- Alamat Rumah -->
            <div class="form-group mb-3">
                <label for="alamat">Alamat Rumah</label>
                <input type="text" class="form-control" id="alamat" name="alamat" value="<?= htmlspecialchars($murid['alamat'] ?? '') ?>" required>
            </div>

            <!-- Kelas -->
            <div class="form-group mb-3">
                <label for="kelas">Kelas</label>
                <input type="text" class="form-control" id="kelas" name="kelas" value="<?= htmlspecialchars($murid['kelas'] ?? '') ?>" required>
            </div>

            <!-- Asal Sekolah -->
            <div class="form-group mb-3">
                <label for="asal_sekolah">Asal Sekolah</label>
                <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah" value="<?= htmlspecialchars($murid['asal_sekolah'] ?? '') ?>" required>
            </div>

            <!-- Jenis Kelamin -->
            <div class="form-group mb-3">
                <label for="jenis_kelamin">Jenis Kelamin</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="L" name="jenis_kelamin" value="L" <?= (isset($murid['jenis_kelamin']) && $murid['jenis_kelamin'] == 'L') ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="L">Laki-laki</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" id="P" name="jenis_kelamin" value="P" <?= (isset($murid['jenis_kelamin']) && $murid['jenis_kelamin'] == 'P') ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="P">Perempuan</label>
                </div>
            </div>

            <!-- Nomor Telepon -->
            <div class="form-group mb-3">
                <label for="no_telp">Nomor Telepon</label>
                <input type="text" class="form-control" id="no_telp" name="no_telp" value="<?= htmlspecialchars($murid['no_telp'] ?? '') ?>" required>
            </div>

            <!-- Pilihan Paket Bimbel -->
            <div class="mb-3">
                <label>Paket Bimbel:</label>
                <select class="form-control" name="id_paket">
                    <?php while ($paket = $result_paket->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($paket['id_paket']) ?>" <?= (isset($murid['id_paket']) && $murid['id_paket'] == $paket['id_paket']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($paket['paket']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary" name="update">Update</button>
            </div>
        </div>
    </form>
</main>

</body>
</html>