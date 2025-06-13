<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();

// Debugging: Display errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek parameter id_murid
if (isset($_GET['id_murid'])) {
    $id_murid = mysqli_real_escape_string($conn, $_GET['id_murid']);

    $query_select = "SELECT mm.*, sm.id_status_murid, sm.status_murid
                   FROM master_murid mm
                   LEFT JOIN status_murid sm ON mm.status_murid = sm.id_status_murid
                   WHERE mm.id_murid = ?";

    $stmt = $conn->prepare($query_select);
    $stmt->bind_param("s", $id_murid);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
}

if (!$row) {
    echo "<script>alert('Data tidak ditemukan!'); window.location.href='master_murid.php';</script>";
    exit();
}

// Ambil status
$statusResult = $conn->query("SELECT id_status_murid, status_murid FROM status_murid");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_murid = $_POST['id_murid'];
    $nama = $_POST['nama'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $alamat = $_POST['alamat'];
    $kelas = $_POST['kelas'];
    $asal_sekolah = $_POST['asal_sekolah'];
    $no_telp = $_POST['no_telp'];
    $status_murid = $_POST['status_murid'];

    $query_update = "UPDATE master_murid 
                     SET nama=?, tanggal_lahir=?, alamat=?, kelas=?, asal_sekolah=?, no_telp=?, status_murid=? 
                     WHERE id_murid=?";
    $stmt_update = $conn->prepare($query_update);
    $stmt_update->bind_param("sssssssi", $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $no_telp, $status_murid, $id_murid);
    $stmt_update->execute();
    $stmt_update->close();

    echo "<script>alert('Data berhasil diperbarui!'); window.location.href='master_murid.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Edit Murid</title>
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="assets/img/favicon.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/custom.css" rel="stylesheet">

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body>
    <?= require('layouts/header.php'); ?>
    <?= require('layouts/sidemenu_owner.php'); ?>

    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Edit Murid</h1>
        </div>

        <div class="card p-5 mb-5">
            <form method="POST" action="">

                <!-- ID Murid (Read-Only) -->
                <div class="form-group mb-3">
                    <label for="id_murid">ID Murid</label>
                    <input type="text" class="form-control" id="id_murid" name="id_murid"
                        value="<?= htmlspecialchars($row['id_murid']) ?>" readonly>
                </div>

                <!-- Nama Murid -->
                <div class="form-group mb-3">
                    <label for="nama">Nama Murid</label>
                    <input type="text" class="form-control" id="nama" name="nama"
                        value="<?= htmlspecialchars($row['nama']) ?>" required>
                </div>

                <!-- Tanggal Lahir -->
                <div class="form-group mb-3">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir"
                        value="<?= htmlspecialchars($row['tanggal_lahir']) ?>" required>
                </div>

                <!-- Alamat -->
                <div class="form-group mb-3">
                    <label for="alamat">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="2" required><?= htmlspecialchars($row['alamat']) ?></textarea>
                </div>

                <!-- No Telepon -->
                <div class="form-group mb-3">
                    <label for="no_telp">No Telepon</label>
                    <input type="text" class="form-control" id="no_telp" name="no_telp"
                        value="<?= htmlspecialchars($row['no_telp']) ?>" required>
                </div>

                <!-- Kelas -->
                <div class="form-group mb-3">
                    <label for="kelas">Kelas</label>
                    <input type="text" class="form-control" id="kelas" name="kelas"
                        value="<?= htmlspecialchars($row['kelas']) ?>" required>
                </div>

                <!-- Asal Sekolah -->
                <div class="form-group mb-3">
                    <label for="asal_sekolah">Asal Sekolah</label>
                    <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah"
                        value="<?= htmlspecialchars($row['asal_sekolah']) ?>" required>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Perbarui</button>
                    <a href="master_murid.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </main>
</body>

</html>

<script>
    // Fungsi untuk mengupdate status murid dengan AJAX
    function updateStatus(id_murid, status_murid) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "update_status_murid.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    alert("Status berhasil diperbarui!");
                } else {
                    alert("Terjadi kesalahan: " + xhr.responseText);
                }
            }
        };

        let data = "id_murid=" + encodeURIComponent(id_murid) + "&status_murid=" + encodeURIComponent(status_murid);
        xhr.send(data);
    }

    // Update status saat user memilih status baru
    function handleUpdate(selectElement, id_murid, type) {
        let value = selectElement.value;

        if (type === 'status') {
            // Update status murid
            updateStatus(id_murid, value);
        }
    }

    // Membatasi input tanggal lahir agar tidak bisa hari ini atau tanggal setelahnya
    window.addEventListener("DOMContentLoaded", function() {
        const tanggalLahirInput = document.getElementById("tanggal_lahir");
        if (tanggalLahirInput) {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            const maxDate = yesterday.toISOString().split('T')[0];
            tanggalLahirInput.setAttribute("max", maxDate);
        }
    });
</script>

</main>
<?= require('layouts/footer.php'); ?>
</body>
<html>