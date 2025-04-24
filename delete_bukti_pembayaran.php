<?php
include 'connection.php';

$id_bukti = $_GET['id'] ?? null; // ID bukti unik yang akan dihapus
$id_pembayaran = $_GET['id_pembayaran'] ?? null; // Untuk redirect kembali

if ($id_bukti && $id_pembayaran) {
    // Ambil path file berdasarkan ID bukti
    $stmt = $conn->prepare("SELECT bukti_pembayaran FROM bukti_pembayaran WHERE id_bukti = ?");
    $stmt->bind_param("i", $id_bukti);
    $stmt->execute();
    $stmt->bind_result($file_path);
    $stmt->fetch();
    $stmt->close();

    // Hapus file fisik
    if ($file_path && file_exists($file_path)) {
        unlink($file_path);
    }

    // Hapus data hanya berdasarkan ID BUKTI
    $stmt_del = $conn->prepare("DELETE FROM bukti_pembayaran WHERE id_bukti = ?");
    $stmt_del->bind_param("i", $id_bukti);
    if ($stmt_del->execute()) {
        echo "<script>alert('Transaksi berhasil dihapus'); window.location.href='hasil_data_pembayaran.php?id_pembayaran=$id_pembayaran';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data'); history.back();</script>";
    }
    $stmt_del->close();
} else {
    echo "<script>alert('ID tidak valid'); history.back();</script>";
}
?>
