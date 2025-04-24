<?php
include 'connection.php'; // Database connection
session_start();

// Handling form submission for adding new payment data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $id_pembayaran = $_POST['id_pembayaran'];
    $jumlah_bayar = $_POST['jumlah_bayar'];

    // Query untuk mengambil data pembayaran yang sudah ada (biaya, jumlah_bayar lama, sisa_biaya, status_pembayaran)
    $query = "SELECT biaya, jumlah_bayar, sisa_biaya, status_pembayaran FROM pembayaran WHERE id_pembayaran = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id_pembayaran);
    $stmt->execute();
    $stmt->bind_result($biaya, $jumlah_bayar_lama, $sisa_biaya, $status_pembayaran);
    $stmt->fetch();
    $stmt->close();

    // Menghitung jumlah_bayar baru dan sisa_biaya
    $jumlah_bayar_total = $jumlah_bayar_lama + $jumlah_bayar;
    $sisa_biaya = $biaya - $jumlah_bayar_total;

    // Tentukan status pembayaran
    if ($sisa_biaya <= 0) {
        $status_pembayaran = 'Lunas';
    } else {
        $status_pembayaran = 'Belum Lunas';
    }

    // Update data pembayaran
    $query_update = "UPDATE pembayaran SET jumlah_bayar = ?, sisa_biaya = ?, status_pembayaran = ? WHERE id_pembayaran = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("dsss", $jumlah_bayar_total, $sisa_biaya, $status_pembayaran, $id_pembayaran);
    
    if ($stmt_update->execute()) {
        echo "<script>alert('Data pembayaran berhasil diperbarui!');</script>";
    } else {
        echo "<script>alert('Error memperbarui data pembayaran: " . $conn->error . "');</script>";
    }
    $stmt_update->close();

    if (!empty($data_pembayaran['bukti_pembayaran'])): ?>
      <div class="mt-2">
          <label>Bukti Pembayaran Saat Ini:</label><br>
          <?php if (in_array(pathinfo($data_pembayaran['bukti_pembayaran'], PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png'])): ?>
              <img src="<?= htmlspecialchars($data_pembayaran['bukti_pembayaran']) ?>" alt="Bukti Pembayaran" width="200px">
          <?php else: ?>
              <a href="<?= htmlspecialchars($data_pembayaran['bukti_pembayaran']) ?>" target="_blank">Lihat Bukti Pembayaran (PDF)</a>
          <?php endif; ?>
      </div>
  <?php endif; 
  
}

// Sinkronisasi data pembayaran sebelum ditampilkan
$update_status_sql = "
UPDATE pembayaran r
LEFT JOIN (
    SELECT 
        id_pembayaran, 
        COALESCE(SUM(jumlah_bayar), 0) AS total_bayar
    FROM bukti_pembayaran
    GROUP BY id_pembayaran
) b ON r.id_pembayaran = b.id_pembayaran
SET 
    r.jumlah_bayar = b.total_bayar,
    r.sisa_biaya = r.biaya - b.total_bayar,
    r.status_pembayaran = CASE
        WHEN b.total_bayar >= r.biaya THEN 'Lunas'
        ELSE 'Belum Lunas'
    END
";

$conn->query($update_status_sql);

// Fetch the validated students' payment data
// SELECT pembayaran.*, sum(bukti_pembayaran.jumlah_bayar) as total_bayar FROM pembayaran left join bukti_pembayaran on pembayaran.id_pembayaran = bukti_pembayaran.id_pembayaran group by pembayaran.id_pembayaran;
$idMurid=$_SESSION['id_ref'];
$query_pembayaran = "
SELECT 
    r.id_pembayaran,
    r.id_paket, 
    p.paket AS nama_paket, 
    p.biaya, 
    r.id_murid, 
    m.nama AS nama_murid,
    COALESCE(SUM(b.jumlah_bayar), 0) AS jumlah_bayar,
    (p.biaya - COALESCE(SUM(b.jumlah_bayar), 0)) AS sisa_biaya,
    CASE 
        WHEN COALESCE(SUM(b.jumlah_bayar), 0) >= p.biaya THEN 'Lunas'
        ELSE 'Belum Lunas'
    END AS status_pembayaran
FROM pembayaran r
LEFT JOIN bukti_pembayaran b ON b.id_pembayaran = r.id_pembayaran 
LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket
LEFT JOIN master_murid m ON r.id_murid = m.id_murid
LEFT JOIN registrasi_murid reg ON r.id_murid = reg.id_murid
WHERE reg.konfirmasi_registrasi = 'Divalidasi' AND  r.id_murid=". $idMurid."
GROUP BY 
    r.id_pembayaran,
    r.id_paket,
    p.paket,
    p.biaya,
    r.id_murid,
    m.nama
";

$result_pembayaran = $conn->query($query_pembayaran);

if (!$result_pembayaran) {
    echo "<script>alert('Error fetching payment data: " . $conn->error . "');</script>";
}
?>

<main id="main" class="main">
    <div class="container mt-4">
        <h2 class="text-center">Hasil Data Pembayaran</h2>

        <h4 class="mt-4 text-success">✅ Murid yang Divalidasi</h4>
        <table class="table table-bordered">
            <thead class="table-success">
                <tr>
                    <th>No Registrasi</th>
                    <th>Nama</th>
                    <th>Paket</th>
                    <th>Biaya</th>
                    <th>Jumlah Bayar</th>
                    <th>Sisa Biaya</th>
                    <th>Status Pembayaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result_pembayaran->fetch_assoc()) { ?>
                <tr>
                    <td><?= isset($row['id_pembayaran']) ? htmlspecialchars($row['id_pembayaran']) : 'N/A' ?></td>
                    <td><?= isset($row['nama_murid']) ? htmlspecialchars($row['nama_murid']) : 'Unknown' ?></td>
                    <td><?= isset($row['nama_paket']) ? htmlspecialchars($row['nama_paket']) : 'Unknown' ?></td>
                    <td>Rp <?= isset($row['biaya']) ? number_format($row['biaya'], 2, ',', '.') : '0,00' ?></td>
                    <td>Rp <?= isset($row['jumlah_bayar']) ? number_format($row['jumlah_bayar'], 2, ',', '.') : '0,00' ?></td>
                    <td>Rp <?= isset($row['sisa_biaya']) ? number_format($row['sisa_biaya'], 2, ',', '.') : '0,00' ?></td>
                    <td>
                        <?php if (isset($row['sisa_biaya']) && $row['sisa_biaya'] > 0): ?>
                            <span class="badge bg-warning">Belum Lunas</span>
                        <?php else: ?>
                            <span class="badge bg-success">Lunas</span>
                        <?php endif; ?>
                    </td>
                    <td>
                          <?php
                            if((intval($row['jumlah_bayar']) < intval($row['biaya'])) || (intval($row['sisa_biaya']) > 0)){
                          ?>
                            <a href="verifikasi_pembayaran_murid.php?id_pembayaran=<?= isset($row['id_pembayaran']) ? $row['id_pembayaran'] : '' ?>" class="btn btn-sm btn-warning">Verifikasi                        
                          <?php
                            }
                          ?>
                        <a href="bukti_pembayaran_murid.php?id_pembayaran=<?= isset($row['id_pembayaran']) ? $row['id_pembayaran'] : '' ?>" class="btn btn-sm btn-warning">Bukti Bayar
                    </td>
                </tr>
            <?php } ?>

            </tbody>
        </table>
    </div>
</main>
</body>
</html>

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
      <a class="nav-link" href="dashboard_murid.php">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->

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

     <!-- Menu Murid -->
   <li class="nav-item">
        <a class="nav-link collapsed" data-bs-target="#menu-murid" data-bs-toggle="collapse" href="#">
          <i class="bi bi-menu-button-wide"></i>
          <span>Menu Murid</span>
          <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="menu-murid" class="nav-content collapse" data-bs-parent="#sidebar-nav">
          <li><a href="view_presensi_murid.php"><i class="bi bi-circle"></i><span>Hasil Data Presensi</span></a></li>
        </ul>
      </li><!-- End Menu Murid -->

<!-- Logout -->
<li class="nav-item">
      <a class="nav-link" href="login.php">
        <i class="bi bi-cash"></i>
        <span>Logout</span>
      </a>
    </li><!-- Logout -->
  </ul>
</aside><!-- End Sidebar -->

            <tbody>
            <?php while ($row = $result_pembayaran->fetch_assoc()) { ?>
                <tr>
                    <td><?= isset($row['id_pembayaran']) ? htmlspecialchars($row['id_pembayaran']) : 'N/A' ?></td>
                    <td><?= isset($row['nama_murid']) ? htmlspecialchars($row['nama_murid']) : 'Unknown' ?></td>
                    <td><?= isset($row['nama_paket']) ? htmlspecialchars($row['nama_paket']) : 'Unknown' ?></td>
                    <td>Rp <?= isset($row['biaya']) ? number_format($row['biaya'], 2, ',', '.') : '0,00' ?></td>
                    <td>Rp <?= isset($row['jumlah_bayar']) ? number_format($row['jumlah_bayar'], 2, ',', '.') : '0,00' ?></td>
                    <td>Rp <?= isset($row['sisa_biaya']) ? number_format($row['sisa_biaya'], 2, ',', '.') : '0,00' ?></td>
                    <td>
                        <?php if (isset($row['sisa_biaya']) && $row['sisa_biaya'] > 0): ?>
                            <span class="badge bg-warning">Belum Lunas</span>
                        <?php else: ?>
                            <span class="badge bg-success">Lunas</span>
                        <?php endif; ?>
                    </td>
                    <td>
                          <?php
                            if((intval($row['jumlah_bayar']) < intval($row['biaya'])) || (intval($row['sisa_biaya']) > 0)){
                          ?>
                            <a href="verifikasi_pembayaran_murid.php?id_pembayaran=<?= isset($row['id_pembayaran']) ? $row['id_pembayaran'] : '' ?>" class="btn btn-sm btn-warning">Verifikasi                        
                          <?php
                            }
                          ?>
                        <a href="bukti_pembayaran_murid.php?id_pembayaran=<?= isset($row['id_pembayaran']) ? $row['id_pembayaran'] : '' ?>" class="btn btn-sm btn-warning">Bukti Bayar
                    </td>
                </tr>
            <?php } ?>

            </tbody>
        </table>
    </div>
</main>
</body>
</html>

<?php
$conn->close();
?>

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
