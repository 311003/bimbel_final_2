<?php
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // ✅ Bypass login sebagai OWNER
    if ($username === 'owner' && $password === 'palingtop94') {
        $_SESSION['id_user'] = 1; // Sesuai id_user owner di database
        $_SESSION['username'] = 'owner';
        $_SESSION['role'] = 1;
        $_SESSION['id_ref'] = null;

        header("Location: dashboard_owner.php");
        exit;
    }

    // ✅ Login normal user (guru / murid)
    $stmt = $conn->prepare("SELECT * FROM tm_user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        echo "<script>alert('Username tidak ditemukan!'); window.location='login.php';</script>";
        exit;
    }

    if ((int)$user['is_active'] !== 1) {
        echo "<script>alert('Akun Anda belum aktif. Hubungi admin.'); window.location='login.php';</script>";
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        echo "<script>alert('Password salah!'); window.location='login.php';</script>";
        exit;
    }

    // ✅ Simpan session setelah berhasil login
    $_SESSION['id_user'] = $user['id_user'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['id_ref'] = $user['id_ref'];

    // ✅ Redirect sesuai role
    switch ((int)$user['role']) {
        case 1:
            header("Location: dashboard_owner.php");
            break;
        case 2:
            if (empty($user['id_ref'])) {
                echo "<script>alert('Akun guru belum lengkap. Hubungi admin.'); window.location='login.php';</script>";
                exit;
            }
            header("Location: dashboard_guru.php");
            break;
        case 3:
            if (empty($user['id_ref'])) {
                echo "<script>alert('Akun murid belum lengkap. Hubungi admin.'); window.location='login.php';</script>";
                exit;
            }
            header("Location: dashboard_murid.php");
            break;
        default:
            echo "<script>alert('Role tidak dikenali.'); window.location='login.php';</script>";
            break;
    }

    exit;
} else {
    header("Location: login.php");
    exit;
}
