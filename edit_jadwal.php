<?php
include 'connection.php'; // Koneksi ke database

// ✅ Ambil ID Jadwal dari URL
if (!isset($_GET['id_jadwal'])) {
    die("Error: ID Jadwal tidak ditemukan.");
}

$id_jadwal = $_GET['id_jadwal'];

// ✅ Ambil Data Jadwal Berdasarkan ID
$query_jadwal = "SELECT * FROM jadwal WHERE id_jadwal = ?";
$stmt = $conn->prepare($query_jadwal);
$stmt->bind_param("s", $id_jadwal);
$stmt->execute();
$result = $stmt->get_result();
$data_jadwal = $result->fetch_assoc();

if (!$data_jadwal) {
    die("Error: Data jadwal tidak ditemukan.");
}

// ✅ Ambil Data Guru yang Bertugas dalam Detail Jadwal
$query_detail = "SELECT id_guru FROM detail_jadwal WHERE id_jadwal = ?";
$stmt_detail = $conn->prepare($query_detail);
$stmt_detail->bind_param("s", $id_jadwal);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();
$selected_guru = [];

while ($row = $result_detail->fetch_assoc()) {
    $selected_guru[] = $row['id_guru'];
}

// ✅ Ambil Data untuk Dropdown
$query_guru = "SELECT id_guru, nama_guru FROM guru";
$result_guru = $conn->query($query_guru);

$query_paket = "SELECT id_paket, paket FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

// Ambil hanya guru yang statusnya bukan "Tidak Aktif" (id_status_guru ≠ 2)
$query_guru = "SELECT g.id_guru, g.nama_guru 
               FROM guru g 
               WHERE g.id_status_guru != 2";
$result_guru = $conn->query($query_guru);

// ✅ Jika Form Disubmit (Update Data)
if (isset($_POST['update_jadwal'])) {
    $id_paket = $_POST['id_paket'];
    $tanggal_jadwal = $_POST['tanggal_jadwal'];
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = $_POST['jam_keluar'];
    $id_guru_list = $_POST['id_guru']; // Array ID Guru

    // Validasi Input
    if (empty($id_guru_list) || empty($id_paket) || empty($tanggal_jadwal) || empty($jam_masuk) || empty($jam_keluar)) {
        echo "<script>alert('Harap isi semua data!'); window.history.back();</script>";
        exit();
    }

    // ✅ Update Data di Tabel `jadwal`
    $query_update_jadwal = "UPDATE jadwal SET id_paket=?, tanggal_jadwal=?, jam_masuk=?, jam_keluar=? WHERE id_jadwal=?";
    $stmt_update_jadwal = $conn->prepare($query_update_jadwal);
    $stmt_update_jadwal->bind_param("sssss", $id_paket, $tanggal_jadwal, $jam_masuk, $jam_keluar, $id_jadwal);

    if (!$stmt_update_jadwal->execute()) {
        die("Gagal memperbarui jadwal: " . $stmt_update_jadwal->error);
    }
    $stmt_update_jadwal->close();

    // ✅ Hapus Data Detail Jadwal Lama
    $query_delete_detail = "DELETE FROM detail_jadwal WHERE id_jadwal = ?";
    $stmt_delete = $conn->prepare($query_delete_detail);
    $stmt_delete->bind_param("s", $id_jadwal);
    $stmt_delete->execute();
    $stmt_delete->close();

    // ✅ Masukkan Data Baru ke Tabel `detail_jadwal`
    $query_insert_detail = "INSERT INTO detail_jadwal (id_jadwal, id_paket, id_guru, tanggal_jadwal, jam_masuk, jam_keluar) 
                            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_detail = $conn->prepare($query_insert_detail);

    foreach ($id_guru_list as $id_guru) {
        $stmt_detail->bind_param("ssssss", $id_jadwal, $id_paket, $id_guru, $tanggal_jadwal, $jam_masuk, $jam_keluar);
        if (!$stmt_detail->execute()) {
            die("Gagal menambahkan detail jadwal: " . $stmt_detail->error);
        }
    }
    $stmt_detail->close();
    $conn->close();

    echo "<script>alert('Jadwal berhasil diperbarui!'); window.location.href='hasil_data_jadwal.php';</script>";
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
    <h1>Edit Jadwal</h1>
</div>

<div class="card p-5 mb-5">
    <form method="POST" action="edit_jadwal.php?id_jadwal=<?= htmlspecialchars($id_jadwal) ?>">

        <!-- ID Jadwal -->
        <div class="form-group mb-3">
            <label>ID Jadwal</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($id_jadwal) ?>" readonly>
        </div>

        <!-- Pilih Paket (Tambahkan Label) -->
        <div class="form-group mb-3">
            <label>Paket Bimbel</label>
            <select class="form-control" name="id_paket" required>
                <option value="">-- Pilih Paket --</option>
                <?php while ($row = $result_paket->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($row['id_paket']) ?>"
                        <?= $data_jadwal['id_paket'] == $row['id_paket'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['paket']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- Pilih ID Guru -->
<div class="form-group mb-3" id="guru-container">
    <label for="id_guru">Pilih ID Guru</label>
    <div class="guru-entry mb-2 d-flex align-items-center">
        <select class="form-control me-2" name="id_guru[]" required onchange="autofillNamaGuru(this)">
            <option value="">-- Pilih ID Guru --</option>
            <?php while ($row = $result_guru->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($row['id_guru']) ?>" data-nama="<?= htmlspecialchars($row['nama_guru']) ?>">
                    <?= htmlspecialchars($row['id_guru']) ?> - <?= htmlspecialchars($row['nama_guru']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="text" class="form-control me-2" name="nama_guru[]" placeholder="Nama Guru" readonly>
        <button type="button" class="btn btn-danger" onclick="removeGuru(this)">Hapus</button>
    </div>
</div>

          <!-- Tombol Tambah Guru -->
          <div class="d-grid gap-2 mb-3">
              <button type="button" class="btn btn-success" onclick="addGuru()">Tambah Guru</button>
          </div>

        <!-- Tanggal Jadwal -->
        <div class="form-group mb-3">
            <label>Tanggal Jadwal</label>
            <input type="date" class="form-control" name="tanggal_jadwal" 
                   value="<?= htmlspecialchars($data_jadwal['tanggal_jadwal']) ?>" required>
        </div>

        <!-- Jam Masuk -->
        <div class="form-group mb-3">
            <label>Jam Masuk</label>
            <input type="time" class="form-control" name="jam_masuk" 
                   value="<?= htmlspecialchars($data_jadwal['jam_masuk']) ?>" required>
        </div>

        <!-- Jam Keluar -->
        <div class="form-group mb-3">
            <label>Jam Keluar</label>
            <input type="time" class="form-control" name="jam_keluar" 
                   value="<?= htmlspecialchars($data_jadwal['jam_keluar']) ?>" required>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="btn btn-primary" name="update_jadwal">Update Jadwal</button>
            <a href="hasil_data_jadwal.php" class="btn btn-secondary">Batal</a>
        </div>

    </form>
</div>
</main>

<script>
function autofillNamaGuru(selectElement) {
    let selectedOption = selectElement.options[selectElement.selectedIndex];
    let namaInput = selectElement.parentElement.querySelector('input[name="nama_guru[]"]');

    if (selectedOption.value !== "") {
        namaInput.value = selectedOption.getAttribute("data-nama");
    } else {
        namaInput.value = "";
    }
}

function addGuru() {
    let container = document.getElementById("guru-container");
    let guruEntries = container.querySelectorAll(".guru-entry");
    let lastGuru = guruEntries[guruEntries.length - 1];
    let newGuru = lastGuru.cloneNode(true);

    // Reset nilai dropdown dan input
    let select = newGuru.querySelector('select');
    let input = newGuru.querySelector('input[name="nama_guru[]"]');
    select.value = "";
    input.value = "";

    // Tambahkan event listener untuk dropdown guru baru
    select.onchange = function() {
        autofillNamaGuru(this);
    };

    container.appendChild(newGuru);
}

function removeGuru(button) {
    let container = document.getElementById("guru-container");
    let guruEntries = container.querySelectorAll(".guru-entry");

    // Hapus hanya jika lebih dari 1 input yang tersedia
    if (guruEntries.length > 1) {
        button.parentElement.remove();
    } else {
        alert("Minimal harus ada satu guru yang dipilih.");
    }
}
</script>
</body>
</html>