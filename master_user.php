<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
include 'db_connection.php';

// Query guru dan murid (harus di luar blok POST agar bisa dipakai di HTML)
$guru_result = $conn->query("SELECT id_guru, nama_guru FROM guru");
$murid_result = $conn->query("SELECT id_murid, nama FROM master_murid");

function createUser($username, $email, $password, $role, $id_ref, $activation_code) {
    global $conn;

    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO tm_user (username, email, password, role, id_ref, is_active, activation_code) 
                            VALUES (?, ?, ?, ?, ?, 0, ?)");
    $stmt->bind_param("ssssis", $username, $email, $hashed, $role, $id_ref, $activation_code);

    return $stmt->execute();
}

function sendActivationEmail($toEmail, $toName, $activationCode) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_brevo_email@example.com'; // ganti dengan email Brevo kamu
        $mail->Password   = 'your_brevo_smtp_key';          // ganti dengan SMTP key Brevo
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('your_brevo_email@example.com', 'Sistem Bimbel');
        $mail->addAddress($toEmail, $toName);

        $activationLink = "http://localhost/bimbel_final/activation.php?code=$activationCode";

        $mail->isHTML(true);
        $mail->Subject = 'Aktivasi Akun Bimbel';
        $mail->Body    = "
            <h3>Halo $toName!</h3>
            <p>Silakan klik link berikut untuk mengaktifkan akun Anda:</p>
            <a href='$activationLink'>$activationLink</a>
            <br><br>
            <small>Abaikan email ini jika Anda tidak mendaftar.</small>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle form
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $role     = $_POST['role'];
    $id_ref   = $_POST['id_ref'];
    $activation_code = bin2hex(random_bytes(16));

    if (createUser($username, $email, $password, $role, $id_ref, $activation_code)) {
        sendActivationEmail($email, $username, $activation_code);
        $message = "User berhasil dibuat dan email aktivasi telah dikirim!";
    } else {
        $message = "Gagal membuat user.";
    }
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

  </header><!-- End Header -->v

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

<!-- Tampilan HTML dengan Bootstrap -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function updateIdRefOptions() {
            let role = document.getElementById("role").value;
            document.getElementById("ref-guru").style.display = (role === "guru") ? "block" : "none";
            document.getElementById("ref-murid").style.display = (role === "murid") ? "block" : "none";
        }
    </script>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg rounded-4 p-4">
                    <h3 class="mb-4">Tambah Master User</h3>

                    <?php if ($message): ?>
                        <div class="alert alert-info"><?= $message ?></div>
                    <?php endif; ?>

                    <form method="POST" action="master_user.php">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Role</label>
                            <select name="role" id="role" class="form-select" onchange="updateIdRefOptions()" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="guru">Guru</option>
                                <option value="murid">Murid</option>
                            </select>
                        </div>

                        <div id="ref-guru" style="display:none;" class="mb-3">
                            <label>Pilih Guru</label>
                            <select name="id_ref" class="form-select">
                                <?php while ($g = $guru_result->fetch_assoc()) : ?>
                                    <option value="<?= $g['id_guru'] ?>"><?= $g['nama_guru'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div id="ref-murid" style="display:none;" class="mb-3">
                            <label>Pilih Murid</label>
                            <select name="id_ref" class="form-select">
                                <?php while ($m = $murid_result->fetch_assoc()) : ?>
                                    <option value="<?= $m['id_murid'] ?>"><?= $m['nama'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Buat User</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

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