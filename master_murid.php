<?php
include 'connection.php'; // Ensure the connection to the database

// Ambil daftar status murid
$statusQuery = "SELECT * FROM status_murid";
$statusResult = $conn->query($statusQuery);
$statusOptions = [];

while ($row = $statusResult->fetch_assoc()) {
    $statusOptions[$row['id_status_murid']] = $row['status_murid'];
}

// Ambil filter status jika ada
$filter_status = $_GET['filter_status'] ?? '';

// Query to fetch data including status murid
$query = "
    SELECT 
        mm.id_murid, 
        mm.nama, 
        mm.tanggal_lahir, 
        mm.alamat, 
        mm.no_telp, 
        mm.kelas, 
        mm.asal_sekolah, 
        sm.status_murid AS status_murid
    FROM master_murid mm
    LEFT JOIN status_murid sm ON mm.status_murid = sm.id_status_murid
    WHERE mm.id_murid NOT IN (SELECT id_murid FROM registrasi_batal)";

if (!empty($filter_status)) {
    $query .= " AND sm.id_status_murid = '$filter_status'";
}

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
        <h1>Tabel Murid</h1>
    </div>

    <!-- Filter Status -->
    <form method="GET" action="master_murid.php" class="mb-3">
        <label for="filter_status">Filter Status:</label>
        <select name="filter_status" id="filter_status" onchange="this.form.submit()">
            <option value="">Semua</option>
            <?php
            foreach ($statusOptions as $id => $status) {
                $selected = ($filter_status == $id) ? "selected" : "";
                echo "<option value='$id' $selected>$status</option>";
            }
            ?>
        </select>
    </form>
        
    <!-- Begin Page Content -->
    <div class="container-fluid">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Murid</th>
                    <th>Nama</th>
                    <th>Tanggal Lahir</th>
                    <th>Alamat</th>
                    <th>No Telepon</th>
                    <th>Kelas</th>
                    <th>Asal Sekolah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id_murid']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                        echo "<td>" . htmlspecialchars(date('d F Y', strtotime($row['tanggal_lahir']))) . "</td>";
                        echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['no_telp']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['kelas']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['asal_sekolah']) . "</td>";
                        echo "<td>
                            <form method='POST' action='update_status_murid.php'>
                                <select name='status_murid' onchange='this.form.submit()'>
                                    <option value='Aktif'" . ($row['status_murid'] == 'Aktif' ? ' selected' : '') . ">Aktif</option>
                                    <option value='Tidak Aktif'" . ($row['status_murid'] == 'Tidak Aktif' ? ' selected' : '') . ">Tidak Aktif</option>
                                </select>
                                <input type='hidden' name='id_murid' value='" . $row['id_murid'] . "' />
                            </form>
                        </td>";
                        echo "<td>
                                <a href='edit_murid.php?id_murid=" . $row['id_murid'] . "' class='btn btn-sm btn-warning'>Edit</a>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='11' class='text-center'>Data tidak ditemukan</td></tr>";
                }
                ?>
            </tbody>
        </table>
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

// Add a function to handle the update when a new status or package is selected
function handleUpdate(select, id_murid, type) {
    let value = select.value;
    let form = document.createElement('form');
    form.method = 'POST';
    form.action = 'master_murid.php'; // Make sure the correct file is used

    let hiddenInput1 = document.createElement('input');
    hiddenInput1.type = 'hidden';
    hiddenInput1.name = 'id_murid';
    hiddenInput1.value = id_murid;
    form.appendChild(hiddenInput1);

    if (type == "status") {
        let hiddenInput2 = document.createElement('input');
        hiddenInput2.type = 'hidden';
        hiddenInput2.name = 'status_murid';
        hiddenInput2.value = value;
        form.appendChild(hiddenInput2);
    }

    document.body.appendChild(form);
    form.submit();
}

</script>
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