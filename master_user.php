<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';
include 'db_connection.php';

// Query guru dan murid (harus di luar blok POST agar bisa dipakai di HTML)
$guru_result = $conn->query("SELECT id_guru, nama_guru FROM guru");
$murid_result = $conn->query("SELECT m.* FROM master_murid m 
                            LEFT JOIN tm_user u ON m.id_murid = u.id_ref 
                            LEFT JOIN registrasi_murid k ON k.id_murid=m.id_murid 
                            WHERE u.id_ref IS NULL AND k.konfirmasi_registrasi='Divalidasi'
                            ");

function createUser($username, $email, $password, $role, $id_ref)
{
    global $conn;
    $defaultStatus=1;
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO tm_user (username, email, password, role, id_ref, is_active) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $username, $email, $hashed, $role, $id_ref,$defaultStatus);

    return $stmt->execute();
}

function sendActivationEmail($toEmail, $toName, $activationCode)
{
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
    $id_ref   = $_POST['id_ref_murid'];
    if($role == 2){
        $id_ref   = $_POST['id_ref_guru'];
    }
    // $activation_code = bin2hex(random_bytes(16));

    if (createUser($username, $email, $password, $role, $id_ref)) {
        // sendActivationEmail($email, $username, $activation_code);
        // $message = "User berhasil dibuat dan email aktivasi telah dikirim!";
        $message = "User berhasil dibuat!";
        $murid_result = $conn->query("SELECT m.* FROM master_murid m 
                            LEFT JOIN tm_user u ON m.id_murid = u.id_ref 
                            LEFT JOIN registrasi_murid k ON k.id_murid=m.id_murid 
                            WHERE u.id_ref IS NULL AND k.konfirmasi_registrasi='Divalidasi'
                            ");
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

    <title>Master User</title>
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
    <link href="assets/css/custom.css" rel="stylesheet">

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>
    <?= require('layouts/header.php'); ?>
    <?= require('layouts/sidemenu_owner.php'); ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg rounded-4 p-4">
                    <h3 class="mb-4">Tambah Master Pengguna</h3>

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
                                <option value="2">Guru</option>
                                <option value="3">Murid</option>
                            </select>
                        </div>

                        <div id="ref-guru" style="display:none;" class="mb-3">
                            <label>Pilih Guru</label>
                            <select name="id_ref_guru" class="form-select">
                                <?php while ($g = $guru_result->fetch_assoc()) : ?>
                                    <option value="<?= $g['id_guru'] ?>"><?= $g['nama_guru'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div id="ref-murid" style="display:none;" class="mb-3">
                            <label>Pilih Murid</label>
                            <select name="id_ref_murid" class="form-select">
                                <?php while ($m = $murid_result->fetch_assoc()) : ?>
                                    <option value="<?= $m['id_murid'] ?>"><?= $m['nama'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Buat Akun Pengguna</button>
                        </div>
                        <div class="mt-3">
                            <a href="list_user.php" class="btn btn-outline-secondary">Lihat Daftar Pengguna</a>
                        </div>
                                </form>

                </div>
            </div>
        </div>
    </div>
    <?= require('layouts/footer.php') ?>
    <script>
        function updateIdRefOptions() {
            let role = document.getElementById("role").value;
            document.getElementById("ref-guru").style.display = (role === "2") ? "block" : "none";
            document.getElementById("ref-murid").style.display = (role === "3") ? "block" : "none";
        }
    </script>
</body>

</html>