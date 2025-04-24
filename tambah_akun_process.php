<?php
include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];
    $id_ref = ($role == 2) ? $_POST["id_ref_guru"] : $_POST["id_ref_murid"];
    $is_active = 1;

    // Validasi
    if (empty($username) || empty($email) || empty($role) || empty($id_ref)) {
        echo "<script>alert('Semua field wajib diisi.'); window.location='tambah_akun.php';</script>";
        exit;
    }

    // Simpan ke tm_user
    $insert = "INSERT INTO tm_user (username, email, password, role, id_ref, is_active)
               VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert);
    $stmt->bind_param("sssisi", $username, $email, $password, $role, $id_ref, $is_active);

    if ($stmt->execute()) {
        echo "<script>alert('Akun berhasil dibuat!'); window.location='tambah_akun.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan akun.'); window.location='tambah_akun.php';</script>";
    }
}
?>
