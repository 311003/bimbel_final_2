<?php
include 'connection.php'; // Koneksi ke database

// Cek apakah id_pembayaran tersedia di URL
if (isset($_GET['id_pembayaran'])) {
    $id_pembayaran = $_GET['id_pembayaran']; // Ambil ID Pembayaran dari URL

    // Mulai transaksi untuk memastikan keandalan penghapusan
    $conn->begin_transaction();

    try {
        // **1. Pastikan data dengan id_pembayaran ada**
        $query_check = "SELECT id_pembayaran FROM pembayaran WHERE id_pembayaran = ?";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("s", $id_pembayaran);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            throw new Exception("Data dengan ID Pembayaran tidak ditemukan.");
        }
        $stmt_check->close();

        // **2. Hapus data hanya dari tabel pembayaran**
        $query_delete = "DELETE FROM pembayaran WHERE id_pembayaran = ?";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bind_param("s", $id_pembayaran);
        
        if (!$stmt_delete->execute()) {
            throw new Exception("Gagal menghapus data pembayaran.");
        }
        $stmt_delete->close();

        // **3. Commit transaksi jika penghapusan berhasil**
        $conn->commit();
        echo "<script>alert('Data pembayaran berhasil dihapus!'); window.location.href='hasil_data_pembayaran.php';</script>";

    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        $conn->rollback();
        echo "<script>alert('Gagal menghapus data! Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }

    $conn->close();
} else {
    echo "<script>alert('ID Pembayaran tidak ditemukan di URL!'); window.history.back();</script>";
}
?>
