<?php
include 'connection.php';
require 'classes/Cashflow.php';

$id_bukti = $_GET['id'] ?? null; // ID bukti unik yang akan dihapus
$id_pembayaran = $_GET['id_pembayaran'] ?? null; // Untuk redirect kembali

if ($id_bukti && $id_pembayaran) {
    // Ambil path file berdasarkan ID bukti
    $stmt = $conn->prepare("SELECT bukti_pembayaran, jumlah_bayar,tanggal_bayar FROM bukti_pembayaran WHERE id_bukti = ?");
    $stmt->bind_param("i", $id_bukti);
    $stmt->execute();
    $stmt->bind_result($file_path, $jumlah_bayar, $tanggal_bayar);
    $stmt->fetch();
    $stmt->close();

    //update pembayaran

    $query = "SELECT biaya, jumlah_bayar, sisa_biaya, status_pembayaran FROM pembayaran WHERE id_pembayaran = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id_pembayaran);
    $stmt->execute();
    $stmt->bind_result($biaya, $jumlah_bayar_lama, $sisa_biaya, $status_pembayaran);
    $stmt->fetch();
    $stmt->close();

    // Menghitung jumlah_bayar baru dan sisa_biaya
    $jumlah_bayar_total = $jumlah_bayar_lama - $jumlah_bayar;
    $sisa_biaya = $biaya - $jumlah_bayar_total;

    // Tentukan status pembayaran
    if ($sisa_biaya <= 0) {
      $status_pembayaran = 'Lunas';
    } else {
      $status_pembayaran = 'Belum Lunas';
    }

    // Update data pembayaran
    $query_update = "UPDATE pembayaran SET jumlah_bayar = ?, sisa_biaya = ?, status_pembayaran = ? WHERE id_pembayaran = ?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("dsss", $jumlah_bayar_total, $sisa_biaya, $status_pembayaran, $id_pembayaran);
    $stmt_update->execute();
    $stmt_update->close();
    $cashflow = new Cashflow($conn);
    $cashflow->remove('bukti_pembayaran',$id_bukti);

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
