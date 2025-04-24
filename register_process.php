<?php
session_start();
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi dasar
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        echo "<script>alert('Semua field wajib diisi.'); window.location='register.php';</script>";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "<script>alert('Password dan konfirmasi tidak cocok.'); window.location='register.php';</script>";
        exit;
    }

    if (strlen($password) < 6) {
        echo "<script>alert('Password minimal 6 karakter.'); window.location='register.php';</script>";
        exit;
    }

    // Cek username/email
    $cek = $conn->prepare("SELECT * FROM tm_user WHERE username = ? OR email = ?");
    $cek->bind_param("ss", $username, $email);
    $cek->execute();
    $cek_result = $cek->get_result();

    if ($cek_result->num_rows > 0) {
        echo "<script>alert('Username atau email sudah digunakan.'); window.location='register.php';</script>";
        exit;
    }

    // Role diset 0 dulu (belum tahu guru atau murid), id_ref juga kosong
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $default_role = 0;

    $stmt = $conn->prepare("INSERT INTO tm_user (username, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("sssi", $username, $email, $hashedPassword, $default_role);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username; // Disimpan agar bisa digunakan saat isi identitas

        // Ambil data user yang baru saja dibuat
        $lastuser = $conn->prepare("SELECT * FROM tm_user WHERE email = ?");
        $lastuser->bind_param("s", $email);
        $lastuser->execute();
        $result = $lastuser->get_result();
        $user = $result->fetch_assoc();

        $_SESSION['user_register'] = $user;

        echo "<script>alert('Akun berhasil dibuat. Silakan isi data diri.'); window.location='register_public.php';</script>";
    } else {
        echo "<script>alert('Gagal membuat akun.'); window.location='register.php';</script>";
    }
}
?>
