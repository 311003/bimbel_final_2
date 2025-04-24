<?php
include 'connection.php'; // koneksi ke database

// Proses ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pembayaran = $_POST['id_pembayaran'];
    $jumlah_bayar = $_POST['jumlah_bayar'];

    // Validasi folder penyimpanan
    $upload_dir = 'uploads/bukti/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Validasi file
    if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] === 0) {
        $tmp_name = $_FILES['bukti_file']['tmp_name'];
        $original_name = basename($_FILES['bukti_file']['name']);
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'docx'];
        if (!in_array($ext, $allowed_ext)) {
            echo "<script>alert('Format file tidak diizinkan. Hanya JPG, PNG, PDF, DOCX'); history.back();</script>";
            exit();
        }

        // Generate nama file unik
        $new_name = uniqid('bukti_', true) . '.' . $ext;
        $destination = $upload_dir . $new_name;

        // Pindahkan file ke folder tujuan
        if (move_uploaded_file($tmp_name, $destination)) {
            // Simpan data ke tabel bukti_pembayaran
            $query = "INSERT INTO bukti_pembayaran (id_pembayaran, jumlah_bayar, bukti_pembayaran, tanggal_bayar) 
                      VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ids", $id_pembayaran, $jumlah_bayar, $destination);

            if ($stmt->execute()) {
                echo "<script>alert('Bukti pembayaran berhasil diunggah!'); window.location.href='bukti_pembayaran_owner.php?id_pembayaran=$id_pembayaran';</script>";
            } else {
                echo "<script>alert('Gagal menyimpan ke database: " . $conn->error . "'); history.back();</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Gagal mengunggah file ke server'); history.back();</script>";
        }
    } else {
        echo "<script>alert('File tidak ditemukan atau error saat upload'); history.back();</script>";
    }
}
?>

<!-- Form Upload Bukti -->
<div class="card p-4 mb-4">
  <h5>Upload Bukti Pembayaran Baru</h5>
  <form method="POST" action="" enctype="multipart/form-data">
    <!-- ID Pembayaran (hidden) -->
    <input type="hidden" name="id_pembayaran" value="<?= htmlspecialchars($_GET['id_pembayaran'] ?? '') ?>">

    <!-- Jumlah Bayar -->
    <div class="mb-2">
      <label for="jumlah_bayar">Jumlah Bayar:</label>
      <input type="number" step="0.01" name="jumlah_bayar" id="jumlah_bayar" class="form-control" required>
    </div>

    <!-- File Upload -->
    <div class="mb-2">
      <label for="bukti_file">File Bukti (JPG, PNG, PDF, DOCX):</label>
      <input type="file" name="bukti_file" id="bukti_file" class="form-control" 
             accept=".jpg,.jpeg,.png,.pdf,.docx" required>
    </div>

    <button type="submit" class="btn btn-primary">Upload</button>
  </form>
</div>
