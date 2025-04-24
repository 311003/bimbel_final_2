<?php
include 'connection.php';

if (isset($_GET['no_reg'])) {
    $no_reg = $_GET['no_reg'];

    // 1. Ambil semua id_pembayaran berdasarkan no_reg
    $get_pembayaran = $conn->prepare("SELECT id_pembayaran FROM pembayaran WHERE no_reg = ?");
    $get_pembayaran->bind_param("s", $no_reg);
    $get_pembayaran->execute();
    $result = $get_pembayaran->get_result();

    while ($row = $result->fetch_assoc()) {
        $id_pembayaran = $row['id_pembayaran'];

        // 1.1 Hapus bukti_pembayaran berdasarkan id_pembayaran
        $delete_bukti = $conn->prepare("DELETE FROM bukti_pembayaran WHERE id_pembayaran = ?");
        $delete_bukti->bind_param("s", $id_pembayaran);
        $delete_bukti->execute();
        $delete_bukti->close();
    }
    $get_pembayaran->close();

    // 2. Hapus pembayaran berdasarkan no_reg
    $stmt2 = $conn->prepare("DELETE FROM pembayaran WHERE no_reg = ?");
    $stmt2->bind_param("s", $no_reg);
    $stmt2->execute();
    $stmt2->close();

    // 3. Hapus dari registrasi_valid
    $stmt3 = $conn->prepare("DELETE FROM registrasi_valid WHERE no_reg = ?");
    $stmt3->bind_param("s", $no_reg);
    $stmt3->execute();
    $stmt3->close();

    // 4. Hapus dari registrasi_murid
    $stmt4 = $conn->prepare("DELETE FROM registrasi_murid WHERE no_reg = ?");
    $stmt4->bind_param("s", $no_reg);

    if ($stmt4->execute()) {
        echo "<script>alert('Murid berhasil dihapus secara permanen!'); window.location.href='konfirmasi_registrasi.php';</script>";
    } else {
        echo "<script>alert('Terjadi kesalahan saat menghapus data!'); window.history.back();</script>";
    }

    $stmt4->close();
    $conn->close();
}
?>
