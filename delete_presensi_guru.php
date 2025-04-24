<?php
include 'connection.php'; // Koneksi ke database

// Cek apakah no_reg tersedia di URL
if (isset($_GET['id_jadwal'])) {
    $id_jadwal = $_GET['id_jadwal'];

    // Mulai transaksi untuk memastikan keandalan penghapusan
    $conn->begin_transaction();

    try {
        // **1. Pastikan data dengan no_reg ada**
        $query_check = "SELECT id_jadwal FROM presensi WHERE id_jadwal = ?";
        $stmt_check = $conn->prepare($query_check);
        $stmt_check->bind_param("s", $id_jadwal);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            throw new Exception("Data dengan No Registrasi tidak ditemukan.");
        }
        $stmt_check->close();

        // **2. Hapus data hanya dari tabel registrasi_murid**
        $query_delete = "DELETE FROM presensi WHERE id_jadwal = ?";
        $stmt_delete = $conn->prepare($query_delete);
        $stmt_delete->bind_param("s", $id_jadwal);
        
        if (!$stmt_delete->execute()) {
            throw new Exception("Gagal menghapus data presensi.");
        }
        $stmt_delete->close();

        // **3. Commit transaksi jika penghapusan berhasil**
        $conn->commit();
        echo "<script>alert('Data presensi berhasil dihapus!'); window.location.href='hasil_presensi_guru.php';</script>";

    } catch (Exception $e) {
        // Rollback transaksi jika ada kesalahan
        $conn->rollback();
        echo "<script>alert('Gagal menghapus data! Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }

    $conn->close();
} else {
    echo "<script>alert('ID Jadwal tidak ditemukan!'); window.location.href='hasil_presensi_guru.php';</script>";
}
?>
