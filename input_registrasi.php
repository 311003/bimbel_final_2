<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();

// Generate ID Murid (Untuk murid baru)
$query_id = "SELECT LPAD(COALESCE(MAX(CAST(id_murid AS UNSIGNED)) + 1, 1), 2, '0') AS id_murid FROM master_murid";
$result = $conn->query($query_id);
$row = $result->fetch_assoc();
$new_id_murid = $row['id_murid'] ?? '01'; // Default '01' jika kosong

// Generate No Registrasi
$query_no_reg = "SELECT LPAD(COALESCE(MAX(CAST(no_reg AS UNSIGNED)) + 1, 1), 2, '0') AS no_reg FROM registrasi_murid";
$result = $conn->query($query_no_reg);
$row = $result->fetch_assoc();
$no_reg = $row['no_reg'] ?? '01'; // Default '01' jika kosong

// Ambil data murid lama
$query_murid = "SELECT id_murid, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp FROM master_murid";
$result_murid = $conn->query($query_murid);
$murid_data = [];

while ($row = $result_murid->fetch_assoc()) {
    $murid_data[$row['id_murid']] = $row;
}

// Ambil data paket bimbel
$query_paket = "SELECT id_paket, paket FROM paket_bimbel";
$result_paket = $conn->query($query_paket);

// Jika tombol "Tambah" ditekan
if (isset($_POST['tambah'])) {
    $murid_baru = $_POST['murid_baru'] ?? ''; // "baru" atau "lama"
    $id_murid = ($murid_baru === "baru") ? $new_id_murid : ($_POST['murid_lama_select'] ?? '');

    $nama = $_POST['nama'] ?? '';
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = $_POST['alamat'] ?? '';
    $kelas = $_POST['kelas'] ?? '';
    $asal_sekolah = $_POST['asal_sekolah'] ?? '';
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    $tgl_reg = $_POST['tgl_reg'] ?? '';
    $no_telp = $_POST['no_telp'] ?? '';
    $id_paket = $_POST['id_paket'] ?? '';

    // Validasi agar semua field wajib diisi
    if (empty($id_murid) || empty($nama) || empty($tanggal_lahir) || empty($alamat) || empty($kelas) || empty($asal_sekolah) || empty($jenis_kelamin) || empty($tgl_reg) || empty($no_telp) || empty($id_paket)) {
        echo "<script>alert('Semua field harus diisi!'); window.history.back();</script>";
        exit();
    }

    // Jika murid baru, masukkan ke master_murid terlebih dahulu
    if ($murid_baru === "baru") {
        $query_insert_murid = "INSERT INTO master_murid (id_murid, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_murid = $conn->prepare($query_insert_murid);
        $stmt_murid->bind_param("ssssssss", $id_murid, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp);

        if (!$stmt_murid->execute()) {
            die("Error menyimpan murid baru: " . $stmt_murid->error);
        }
        $stmt_murid->close();
    }

    // Pastikan id_murid ada di master_murid sebelum registrasi
    $check_murid = $conn->query("SELECT id_murid FROM master_murid WHERE id_murid = '$id_murid'");
    if ($check_murid->num_rows === 0) {
        die("Error: ID Murid tidak ditemukan di master_murid setelah insert.");
    }

    // Simpan ke registrasi_murid
    $query_insert_registrasi = "INSERT INTO registrasi_murid (no_reg, id_murid, tgl_reg, nama, tanggal_lahir, alamat, kelas, asal_sekolah, jenis_kelamin, no_telp, id_paket)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_registrasi = $conn->prepare($query_insert_registrasi);
    $stmt_registrasi->bind_param("sssssssssss", $no_reg, $id_murid, $tgl_reg, $nama, $tanggal_lahir, $alamat, $kelas, $asal_sekolah, $jenis_kelamin, $no_telp, $id_paket);

    if ($stmt_registrasi->execute()) {
        echo "<script>alert('Data murid berhasil ditambahkan!'); window.location.href='konfirmasi_registrasi.php';</script>";
    } else {
        die("Error menyimpan ke registrasi_murid: " . $stmt_registrasi->error);
    }

    $stmt_registrasi->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Input Registrasi</title>
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
    <?= require('layouts/sidemenu.php'); ?>

    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Tambah Data Registrasi</h1>
        </div><!-- End Page Title -->

        <form method="POST" action="" enctype="multipart/form-data" id="formRegistrasi">
            <div class="card p-5 mb-5">

                <!-- Nomor Registrasi -->
                <div class="form-group mb-3">
                    <label for="no_reg">Nomor Registrasi</label>
                    <input type="text" class="form-control" id="no_reg" name="no_reg" value="<?= htmlspecialchars($no_reg) ?>" readonly>
                </div>

                <!-- Tanggal Registrasi -->
                <div class="form-group mb-3">
                    <label for="tgl_reg">Tanggal Registrasi</label>
                    <input type="date" class="form-control" id="tgl_reg" name="tgl_reg" required>
                </div>

                <!-- Pilihan Murid Baru atau Lama -->
                <div class="mb-3">
                    <label>Murid Baru atau Lama</label><br>
                    <input type="radio" name="murid_baru" value="baru" onclick="toggleMurid(true)" checked> Baru
                    <input type="radio" name="murid_baru" value="lama" onclick="toggleMurid(false)"> Lama
                </div>

                <!-- ID Murid (Untuk Murid Baru) -->
                <div class="mb-3" id="id_murid_container">
                    <label>ID Murid</label>
                    <input type="text" class="form-control" name="id_murid" id="id_murid" value="<?= htmlspecialchars($new_id_murid) ?>" readonly>
                </div>

                <!-- Dropdown Pilih Murid Lama -->
                <div class="mb-3" id="murid_lama_container" style="display:none;">
                    <label>Pilih Murid Lama:</label>
                    <select id="murid_lama_select" class="form-control" name="murid_lama_select" onchange="fillMuridData()">
                        <option value="">-- Pilih Murid Lama --</option>
                        <?php foreach ($murid_data as $id_murid => $murid) : ?>
                            <option value="<?= $id_murid; ?>"><?= htmlspecialchars($murid['nama']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Input Hidden untuk ID Murid Lama -->
                <input type="hidden" name="id_murid" id="id_murid_hidden">

                <!-- Nama Murid -->
                <div class="mb-3">
                    <label>Nama Murid</label>
                    <input type="text" class="form-control" name="nama" id="nama_murid" required>
                </div>

                <!-- Tanggal Lahir -->
                <div class="form-group mb-3">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" required>
                </div>

                <!-- Alamat Rumah -->
                <div class="form-group mb-3">
                    <label for="alamat">Alamat Rumah</label>
                    <input type="text" class="form-control" id="alamat" name="alamat" required>
                </div>

                <!-- Kelas -->
                <div class="form-group mb-3">
                    <label for="kelas">Kelas</label>
                    <input type="text" class="form-control" id="kelas" name="kelas" required>
                </div>

                <!-- Asal Sekolah -->
                <div class="form-group mb-3">
                    <label for="asal_sekolah">Asal Sekolah</label>
                    <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah" required>
                </div>

                <!-- Jenis Kelamin -->
                <div class="form-group mb-3">
                    <label for="jenis_kelamin">Jenis Kelamin</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="L" name="jenis_kelamin" value="L" required>
                        <label class="form-check-label" for="L">Laki-laki</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="P" name="jenis_kelamin" value="P">
                        <label class="form-check-label" for="P">Perempuan</label>
                    </div>
                </div>

                <!-- Nomor Telepon -->
                <div class="form-group mb-3">
                    <label for="no_telp">Nomor Telepon</label>
                    <input type="text" class="form-control" id="no_telp" name="no_telp" required>
                </div>

                <!-- Pilihan Paket Bimbel -->
                <div class="mb-3">
                    <label>Paket Bimbel</label>
                    <select class="form-control" name="id_paket" required>
                        <option value="">Pilih Paket</option>
                        <?php
                        $query_paket = "SELECT id_paket, paket FROM paket_bimbel";
                        $result_paket = $conn->query($query_paket);
                        while ($paket = $result_paket->fetch_assoc()):
                        ?>
                            <option value="<?= htmlspecialchars($paket['id_paket']) ?>">
                                <?= htmlspecialchars($paket['paket']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="text-center">
                    <button type="submit" class="btn btn-primary" name="tambah">Simpan</button>
                </div>
            </div>
        </form>
    </main>
    <?= require('layouts/footer.php'); ?>


    <script>
        function toggleMurid(isNew) {
            if (isNew) {
                document.getElementById("id_murid_container").style.display = "block";
                document.getElementById("murid_lama_container").style.display = "none";
                document.getElementById("id_murid_hidden").value = document.getElementById("id_murid").value;
            } else {
                document.getElementById("id_murid_container").style.display = "none";
                document.getElementById("murid_lama_container").style.display = "block";
                document.getElementById("id_murid_hidden").value = "";
            }
        }

        function fillMuridData() {
            var muridData = <?= json_encode($murid_data, JSON_PRETTY_PRINT); ?>;
            var selectedId = document.getElementById("murid_lama_select").value;

            if (!selectedId || !(selectedId in muridData)) {
                console.log("Data tidak ditemukan untuk ID:", selectedId);
                return;
            }

            var murid = muridData[selectedId];

            document.getElementById("nama_murid").value = murid.nama;
            document.getElementById("tanggal_lahir").value = murid.tanggal_lahir;
            document.getElementById("alamat").value = murid.alamat;
            document.getElementById("kelas").value = murid.kelas;
            document.getElementById("asal_sekolah").value = murid.asal_sekolah;
            document.getElementById("no_telp").value = murid.no_telp;
            document.getElementById("id_murid_hidden").value = selectedId; // Kirim ID Murid Lama ke PHP

            if (murid.jenis_kelamin === "L") {
                document.getElementById("L").checked = true;
            } else {
                document.getElementById("P").checked = true;
            }

            console.log("Data berhasil dimasukkan:", murid);
        }

        const today = new Date().toISOString().split('T')[0];
        document.getElementById("tgl_reg").setAttribute("min", today);

        // Tambahan untuk membatasi tanggal lahir (tidak boleh hari ini atau ke depan)
        const yesterday = new Date();
        yesterday.setDate(yesterday.getDate() - 1);
        const maxTanggalLahir = yesterday.toISOString().split('T')[0];
        document.getElementById("tanggal_lahir").setAttribute("max", maxTanggalLahir);
    </script>
</body>

</html>