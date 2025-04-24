<?php
include 'connection.php'; // Koneksi ke database

// Pastikan form dikirim dengan metode POST
if (isset($_POST['update_pembayaran'])) {
    $id_pembayaran = $_POST['id_pembayaran'];
    $id_murid = $_POST['id_murid'];
    $id_paket = $_POST['id_paket'];
    $biaya = $_POST['biaya'];
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $keterangan = $_POST['keterangan'];

    // Validasi data tidak boleh kosong
    if (empty($id_murid) || empty($id_paket) || empty($biaya) || empty($metode_pembayaran) || empty($keterangan)) {
        echo "<script>alert('Harap isi semua kolom!'); window.history.back();</script>";
        exit();
    }

    // Query Update Data
    $query_update = "UPDATE pembayaran 
                     SET id_murid = ?, id_paket = ?, biaya = ?, metode_pembayaran = ?, keterangan = ?
                     WHERE id_pembayaran = ?";

    $stmt = $conn->prepare($query_update);
    $stmt->bind_param("ssdsss", $id_murid, $id_paket, $biaya, $metode_pembayaran, $keterangan, $id_pembayaran);

    if ($stmt->execute()) {
        echo "<script>alert('Data pembayaran berhasil diperbarui!'); window.location.href='hasil_data_pembayaran.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat menyimpan data!'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Akses tidak diizinkan!'); window.location.href='hasil_data_pembayaran.php';</script>";
}
?>