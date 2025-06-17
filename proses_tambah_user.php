<?php
session_start();
include 'connection.php'; // sesuaikan path jika perlu

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Validasi umum
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['message'] = "Semua field wajib diisi.";
        header("Location: master_user.php");
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Tambahkan id_ref berdasarkan role
    $id_ref_guru = isset($_POST['id_ref_guru']) ? (int)$_POST['id_ref_guru'] : null;
    $id_ref_murid = isset($_POST['id_ref_murid']) ? (int)$_POST['id_ref_murid'] : null;

    $id_ref = "NULL";
    if ($role == '2' && $id_ref_guru) {
        $id_ref = $id_ref_guru;
    } elseif ($role == '3' && $id_ref_murid) {
        $id_ref = $id_ref_murid;
    }

    // Cek email sudah digunakan
    $cek = mysqli_query($conn, "SELECT * FROM tm_user WHERE email = '$email'");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['message'] = "Email sudah digunakan.";
        header("Location: master_user.php");
        exit;
    }

    // Insert user baru
    $query = "INSERT INTO tm_user (username, email, password, role, id_ref, is_active) 
              VALUES ('$username', '$email', '$hashed_password', '$role', $id_ref, 1)";

    if (mysqli_query($conn, $query)) {
        $_SESSION['message'] = "Akun pengguna berhasil ditambahkan.";
        header("Location: list_user.php");
        exit;
    } else {
        $_SESSION['message'] = "Terjadi kesalahan: " . mysqli_error($conn);
        header("Location: master_user.php");
        exit;
    }
} else {
    $_SESSION['message'] = "Permintaan tidak valid.";
    header("Location: master_user.php");
    exit;
}
?>

<form action="proses_tambah_user.php" method="POST">
    <input type="text" name="username" placeholder="Nama User" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <select name="role" required>
        <option value="">--Pilih Role--</option>
        <option value="guru">Guru</option>
        <option value="murid">Murid</option>
    </select>
    <button type="submit">Tambah User</button>
</form>
