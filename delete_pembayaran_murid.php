<?php
include 'connection.php';

$id_pembayaran = $_GET['id'] ?? null;
$id_pembayaran = $_GET['id_pembayaran'] ?? null;

if ($id_pembayaran) {
    // Cek file bukti dulu (jika ingin sekalian hapus file fisik)
    $stmt = $conn->prepare("SELECT bukti_pembayaran FROM bukti_pembayaran WHERE id_pembayaran = ?");
    $stmt->bind_param("i", $id_pembayaran);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    $stmt->close();

    // Hapus file fisik
    if ($file_path && file_exists($file_path)) {
        unlink($file_path);
    }

    // Hapus dari database
    $stmt = $conn->prepare("DELETE FROM bukti_pembayaran WHERE id_pembayaran = ?");
    $stmt->bind_param("i", $id_pembayaran);
    if ($stmt->execute()) {
        echo "<script>alert('Bukti pembayaran berhasil dihapus'); window.location.href='input_pembayaran_murid.php?id_pembayaran=$id_pembayaran';</script>";
    } else {
        echo "<script>alert('Gagal menghapus'); history.back();</script>";
    }
    $stmt->close();
}
?>