<?php
include 'connection.php'; // Pastikan koneksi database tersedia

// Pastikan data yang diterima aman
if (isset($_POST['id_murid']) && isset($_POST['id_paket'])) {
    $id_murid = $_POST['id_murid'];
    $id_paket = $_POST['id_paket'];

    // Debugging: Output the received values
    echo "ID Murid: " . $id_murid . "<br>";
    echo "ID Paket: " . $id_paket . "<br>";

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // Update paket bimbel di registrasi_valid
        $query_update_paket = "UPDATE registrasi_valid SET id_paket = ? WHERE id_murid = ?";
        $stmt_update_paket = $conn->prepare($query_update_paket);
        $stmt_update_paket->bind_param("ii", $id_paket, $id_murid);
        
        if ($stmt_update_paket->execute()) {
            echo "Paket berhasil diperbarui di registrasi_valid.<br>";
        } else {
            echo "Gagal memperbarui paket di registrasi_valid.<br>";
        }
        $stmt_update_paket->close();

        // Juga update data paket di master_murid jika perlu
        $query_update_master = "UPDATE master_murid SET id_paket = ? WHERE id_murid = ?";
        $stmt_update_master = $conn->prepare($query_update_master);
        $stmt_update_master->bind_param("ii", $id_paket, $id_murid);
        
        if ($stmt_update_master->execute()) {
            echo "Paket berhasil diperbarui di master_murid.<br>";
        } else {
            echo "Gagal memperbarui paket di master_murid.<br>";
        }
        $stmt_update_master->close();

        // Commit transaksi jika sukses
        $conn->commit();
        echo "Update sukses!<br>";
    } catch (Exception $e) {
        // Rollback jika ada error
        $conn->rollback();
        echo "Error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Invalid parameters!<br>";
}
?>

<form method="POST" action="update_paket_murid.php">
    <div class="form-group mb-3">
        <label for="id_murid">ID Murid</label>
        <input type="text" class="form-control" id="id_murid" name="id_murid" value="<?= htmlspecialchars($data_murid['id_murid']) ?>" readonly>
    </div>

    <div class="form-group mb-3">
        <label for="id_paket">ID Paket</label>
        <select class="form-control" id="id_paket" name="id_paket" required>
            <option value="">-- Pilih ID Paket --</option>
            <?php while ($paket = $result_paket->fetch_assoc()) : ?>
                <option value="<?= htmlspecialchars($paket['id_paket']) ?>" <?= ($paket['id_paket'] == $data_pembayaran['id_paket']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($paket['paket']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="form-group mb-3">
        <button type="submit" class="btn btn-primary">Update Paket</button>
    </div>
</form>
