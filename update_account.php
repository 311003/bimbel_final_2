<?php
session_start();
include 'connection.php';

switch($_SESSION['role']){
    case 2:
        require 'middlewares/validasi_guru.php';
    break;
    case 3:
        require 'middlewares/validasi_murid.php';
    break;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username=$_SESSION['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi dasar
    

    if ($password !== $confirm_password) {
        echo "<script>alert('Password dan konfirmasi tidak cocok.'); window.location='update_account.php';</script>";
        exit;
    }

    if (strlen($password) < 6) {
        echo "<script>alert('Password minimal 6 karakter.'); window.location='update_account.php';</script>";
        exit;
    }

    // Cek username/email
    $cek = $conn->prepare("SELECT * FROM tm_user WHERE username = ? ");
    $cek->bind_param("s", $username);
    $cek->execute();
    $cek_result = $cek->get_result();

    if ($cek_result->num_rows > 0) {
        // Role diset 0 dulu (belum tahu guru atau murid), id_ref juga kosong
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE tm_user SET  password=? WHERE username=?");
        $stmt->bind_param("ss",  $hashedPassword,$username,);

        if ($stmt->execute()) {
            echo "<script>alert('Password Berhasil di ubah.'); window.location='update_account.php';</script>";
        }
        exit;
    }
    echo "<script>alert('Password Gagal di ubah.'); window.location='update_account.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Upate Account</title>
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
    <?= require('layouts/sidemenu.php'); ?>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Update Password</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-body">

            <form method="POST" action="update_account.php">
                <div class="form-group mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group mb-3">
                    <label>Re-Password</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>



                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Ubah Password</button>
                </div>
            </form>
            </div>
            

        </div>
    </main>
    <?php
    require 'layouts/footer.php';
    ?>
</body>

</html>