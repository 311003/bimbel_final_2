<?php
include 'connection.php';

$id_bukti = $_GET['id'] ?? null;
$id_pembayaran = $_GET['id_pembayaran'] ?? null;

if ($id_pembayaran) {
    // Cek file bukti dulu (jika ingin sekalian hapus file fisik)
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


