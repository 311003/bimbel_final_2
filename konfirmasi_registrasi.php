<?php
include 'connection.php'; // Koneksi database

// Jika tombol validasi ditekan
if (isset($_POST['validasi'])) {
    $no_reg = $_POST['no_reg'];

    // Ambil id_paket dan biaya dari registrasi_murid dan paket_bimbel
    $query_select = "SELECT r.id_murid, r.nama, r.id_paket, p.biaya 
                     FROM registrasi_murid r
                     LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
                     WHERE r.no_reg = ?";
    $stmt_select = $conn->prepare($query_select);
    $stmt_select->bind_param("s", $no_reg);
    $stmt_select->execute();
    $stmt_select->bind_result($id_murid, $nama, $id_paket, $biaya);
    $stmt_select->fetch();
    $stmt_select->close();

    // Debugging: Cek apakah id_murid, id_paket, dan biaya valid
    if (empty($id_murid) || empty($id_paket) || empty($biaya)) {
        echo "<script>alert('Data tidak lengkap: ID Murid, ID Paket, atau Biaya tidak ditemukan!'); window.history.back();</script>";
        exit();
    }

    // Update status konfirmasi registrasi
    $query_update = "UPDATE registrasi_murid SET konfirmasi_registrasi = 'Divalidasi' WHERE no_reg = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("s", $no_reg);
    if (!$stmt_update->execute()) {
        echo "<script>alert('Error memperbarui status validasi: " . $conn->error . "'); window.history.back();</script>";
        exit();
    }

// Debugging: Menampilkan nilai parameter
echo "no_reg: $no_reg, id_murid: $id_murid, nama: $nama, id_paket: $id_paket, paket: $paket, biaya: $biaya";

// Insert data pembayaran
$query_insert = "INSERT INTO pembayaran 
                 (no_reg, id_murid, nama, id_paket, paket, biaya, jumlah_bayar, sisa_biaya, status_pembayaran, input_pembayaran) 
                 VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'Belum Lunas', NOW())";

// Persiapkan dan bind parameter
$stmt_insert = $conn->prepare($query_insert);
$stmt_insert->bind_param("sssssds", $no_reg, $id_murid, $nama, $id_paket, $paket, $biaya, $biaya);

// Eksekusi query
if (!$stmt_insert->execute()) {
    echo "<script>alert('Error menambahkan pembayaran: " . $stmt_insert->error . "'); window.history.back();</script>";
    exit();
} else {
    echo "<script>alert('Murid berhasil divalidasi dan data pembayaran berhasil ditambahkan!'); window.location.href='view_konfirmasi_registrasi.php';</script>";
    echo "<script>alert('Murid berhasil divalidasi dan data pembayaran berhasil ditambahkan!'); window.location.href='hasil_data_pembayaran.php';</script>";
}

$stmt_insert->close();

}

// Jika tombol batalkan ditekan
if (isset($_POST['batalkan'])) {
    $no_reg = $_POST['no_reg'];
    $query_delete = "DELETE FROM registrasi_murid WHERE no_reg = ?";
    $stmt = $conn->prepare($query_delete);
    $stmt->bind_param("s", $no_reg);
    $stmt->execute();
    echo "<script>alert('Murid berhasil dibatalkan!'); window.location.href='view_konfirmasi_registrasi.php';</script>";
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
                    <th>Aksi</th>
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
                        <td>
                            <?php if ($row['konfirmasi_registrasi'] != 'Divalidasi') { ?>
                                <a href="#" onclick="konfirmasiValidasi('<?= urlencode($row['no_reg']) ?>')" class="btn btn-success btn-sm">
                                    Validasi
                                </a>
                            <?php } ?>
                            <?php if ($row['konfirmasi_registrasi'] != 'Dibatalkan') { ?>
                                <a href="#" onclick="konfirmasiBatalkan('<?= urlencode($row['no_reg']) ?>')" class="btn btn-danger btn-sm">
                                    Batalkan
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        function konfirmasiValidasi(no_reg) {
            if (confirm("Apakah Anda yakin ingin memvalidasi murid ini?")) {
                window.location.href = "registrasi_valid.php?no_reg=" + encodeURIComponent(no_reg);
            }
        }

        function konfirmasiBatalkan(no_reg) {
            if (confirm("Apakah Anda yakin ingin membatalkan pendaftaran murid ini?")) {
                window.location.href = "delete_registrasi.php?no_reg=" + encodeURIComponent(no_reg);
            }
        }

        function ubahStatus(no_reg, status) {
            if (confirm("Apakah Anda yakin ingin mengubah status murid ini menjadi " + status + "?")) {
                let xhr = new XMLHttpRequest();
                xhr.open("POST", "update_status_murid.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        alert("Status berhasil diperbarui!");
                        location.reload();
                    }
                };

                xhr.send("no_reg=" + encodeURIComponent(no_reg) + "&konfirmasi_registrasi=" + encodeURIComponent(status));
            }
        }
    </script>

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