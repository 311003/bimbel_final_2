<?php
// ===== proses_registrasi_murid.php (versi FIXED) =====
session_start();
include 'connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_murid = $_POST['id_murid'];
    // $nama = $_SESSION['username']; // Gunakan username sebagai nama murid untuk pencocokan
    $nama = $_POST['nama']; // Gunakan username sebagai nama murid untuk pencocokan
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $kelas = $_POST['kelas'];
    $asal_sekolah = $_POST['asal_sekolah'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $no_telp = $_POST['no_telp'];
    $id_paket = $_POST['id_paket'];
    $tgl_reg = date('Y-m-d');

    if (empty($id_murid) || empty($nama) || empty($tanggal_lahir) || empty($alamat) || empty($kelas) ||
        empty($asal_sekolah) || empty($jenis_kelamin) || empty($no_telp) || empty($id_paket)) {
        echo "<script>alert('Harap isi semua data!'); window.history.back();</script>";
        exit;
    }

    // 1. Insert ke master_murid
    $query_murid = "INSERT INTO master_murid (id_murid, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp, status_murid)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
    $stmt1 = $conn->prepare($query_murid);
    $stmt1->bind_param("ssssssss", $id_murid, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp);

    if (!$stmt1->execute()) {
        echo "<script>alert('Gagal menyimpan data murid.'); window.history.back();</script>";
        exit;
    }

    // 5. Update ke tm_user dengan id_ref dan role 3 (murid)
    $last_id = $id_murid;
    $username = isset($_SESSION['user_register'])?$_SESSION['user_register']['username']:$_SESSION['username'];
    $stmt = $conn->prepare("UPDATE tm_user SET id_ref = ?, role = 3 WHERE username = ?");
    $stmt->bind_param("ss", $last_id, $username);
    $stmt->execute();
    if(isset($_SESSION['user_register'])){
        unset($_SESSION['user_register']);
    }
    $stmt->close();

    // 2. Generate nomor registrasi baru
    $result_reg = $conn->query("SELECT LPAD(COALESCE(MAX(CAST(no_reg AS UNSIGNED)) + 1, 1), 2, '0') AS no_reg FROM registrasi_murid");
    $row_reg = $result_reg->fetch_assoc();
    $no_reg = $row_reg['no_reg'] ?? '01';

    // 3. Insert ke registrasi_murid
    $query_reg = "INSERT INTO registrasi_murid (no_reg, id_murid, tgl_reg, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp, id_paket)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt2 = $conn->prepare($query_reg);
    $stmt2->bind_param("sssssssssss", $no_reg, $id_murid, $tgl_reg, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp, $id_paket);

    if (!$stmt2->execute()) {
        echo "<script>alert('Gagal menyimpan data registrasi.'); window.history.back();</script>";
        exit;
    }
    $stmt2->close();
    // 4. Insert ke registrasi_valid
    // $stmt3 = $conn->prepare("INSERT INTO registrasi_valid (no_reg, id_murid, id_paket) VALUES (?, ?, ?)");
    // $stmt3->bind_param("sss", $no_reg, $id_murid, $id_paket);

    // if (!$stmt3->execute()) {
    //     echo "<script>alert('Gagal menyimpan data validasi.'); window.history.back();</script>";
    //     exit;
    // }
    // $stmt3->close();
    

    echo "<script>alert('Data murid berhasil disimpan.'); window.location='terima_kasih.php';</script>";
}
?>
