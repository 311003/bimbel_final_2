<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'connection.php';

switch ($_SESSION['role']) {
    case 2:
        require 'middlewares/validasi_guru.php';
        break;
    case 3:
        require 'middlewares/validasi_murid.php';
        break;
}

// ✅ Fungsi untuk Membuat ID Presensi Otomatis (01, 02, 03, dst.)
function generateIdPresensi($conn)
{
    $query = "SELECT LPAD(COALESCE(MAX(CAST(id_presensi AS UNSIGNED)) + 1, 1), 2, '0') AS id_presensi FROM presensi";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id_presensi']; // Mengembalikan ID presensi yang baru
    } else {
        return '01'; // Default ID jika belum ada data
    }
}

// Memanggil fungsi untuk mendapatkan ID presensi baru
$newId = generateIdPresensi($conn);

// Ambil data jadwal beserta informasi terkait
$query_jadwal = "SELECT 
                    j.id_jadwal, 
                    g.id_guru, 
                    g.nama_guru, 
                    p.id_paket, 
                    p.paket AS nama_paket,
                    j.tanggal_jadwal, 
                    j.jam_masuk, 
                    j.jam_keluar
                 FROM jadwal j
                 LEFT JOIN guru g ON j.id_guru = g.id_guru
                 LEFT JOIN paket_bimbel p ON j.id_paket = p.id_paket";
if ($_SESSION['role'] != 1) {
    $query_jadwal .= " WHERE g.id_guru = '" . $_SESSION['id_ref'] . "'";
}
$result_jadwal = $conn->query($query_jadwal);

// Ambil data murid
$query_murid = "SELECT id_murid, nama FROM master_murid";
$result_murid = $conn->query($query_murid);

// Jika form disubmit untuk tambah presensi
if (isset($_POST['tambah_presensi'])) {
    if (!isset($_POST['id_jadwal']) || !isset($_POST['id_murid'])) {
        die("<script>alert('Harap pilih jadwal dan isi data murid!'); window.history.back();</script>");
    }

    $id_presensi = generateIdPresensi($conn); // Auto-generate ID
    $id_jadwal = $_POST['id_jadwal'];
    $tanggal_presensi = $_POST['tanggal_presensi'];
    $jam_masuk = $_POST['jam_masuk'];
    $jam_keluar = $_POST['jam_keluar'];

    // Ambil data jadwal yang dipilih
    $query_get_jadwal = "SELECT j.id_jadwal, g.id_guru, p.id_paket FROM jadwal j
                         LEFT JOIN guru g ON j.id_guru = g.id_guru
                         LEFT JOIN paket_bimbel p ON j.id_paket = p.id_paket
                         WHERE j.id_jadwal = ?";
    $stmt_jadwal = $conn->prepare($query_get_jadwal);
    $stmt_jadwal->bind_param("s", $id_jadwal);
    $stmt_jadwal->execute();
    $result_jadwal = $stmt_jadwal->get_result();
    $data_jadwal = $result_jadwal->fetch_assoc();
    $stmt_jadwal->close();

    if (!$data_jadwal) {
        die("<script>alert('Jadwal tidak ditemukan! Pastikan ID valid.'); window.history.back();</script>");
    }

    $id_guru = $data_jadwal['id_guru'];
    $id_paket = $data_jadwal['id_paket'];

    // Insert data presensi utama
    $query_insert = "INSERT INTO presensi (id_presensi, id_jadwal, id_guru, tanggal_presensi, jam_masuk, jam_keluar, id_paket)
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query_insert);
    $stmt->bind_param("iiissss", $id_presensi, $id_jadwal, $id_guru, $tanggal_presensi, $jam_masuk, $jam_keluar, $id_paket);

    if ($stmt->execute()) {
        // Ambil array id_murid dan 
        $id_murid_array = $_POST['id_murid'];

        // Insert ke detail_presensi untuk setiap murid
        $query_detail = "INSERT INTO detail_presensi (id_presensi, id_jadwal, id_guru, tanggal_presensi, jam_masuk, jam_keluar, id_paket, id_murid) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_detail = $conn->prepare($query_detail);

        foreach ($id_murid_array as $key => $id_murid) {

            $stmt_detail->bind_param("iiissssi", $id_presensi, $id_jadwal, $id_guru, $tanggal_presensi, $jam_masuk, $jam_keluar, $id_paket, $id_murid);
            $stmt_detail->execute();
        }
        $stmt_detail->close();
    
        //Insert Pembaaran Guru
        $guru = $conn->prepare("SELECT * FROM guru WHERE id_guru= ? ");
        $guru->bind_param("i", $id_guru);
        $guru->execute();
        $guru_result = $guru->get_result();
        $guru = $guru_result->fetch_assoc();

        $status_bayar='Belum Lunas';
        $nama=$guru['nama_guru'];
        $gaji=$guru['gaji'];
        $sisa_bayar=$guru['gaji'];

        $query_detail = "INSERT INTO pembayaran_guru (id_presensi, id_guru,id_jadwal,  id_paket,  nama, gaji,sisa_bayar,status_pembayaran,tanggal_bayar) 
                VALUES (?, ?, ?, ?,?,?,?,?,?)";
        $stmt_detail = $conn->prepare($query_detail);
        $stmt_detail->bind_param("iiissssss", $id_presensi, $id_guru, $id_jadwal, $id_paket, $nama,$gaji,$sisa_bayar,$status_bayar,$tanggal_presensi);
        $stmt_detail->execute();
        $stmt_detail->close();

        echo "<script>alert('Presensi berhasil ditambahkan!'); window.location.href='hasil_presensi_guru.php';</script>";
    } else {
        die("Gagal menambahkan presensi: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - Dashboard Guru</title>
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
    <?= require('layouts/sidemenu_guru.php'); ?>
</body>

<main id="main" class="main">

    <div class="pagetitle">
        <h1>Tambah Data Presensi</h1>
    </div><!-- End Page Title -->

    <div class="card p-5 mb-5">
        <form method="POST" action="input_presensi_guru.php">

            <!-- ID Presensi -->
            <div class="form-group mb-3">
                <label for="id_presensi">ID Presensi</label>
                <input type="text" class="form-control" id="id_presensi" name="id_presensi" value="<?= htmlspecialchars($newId) ?>" readonly>
            </div>

            <!-- Pilih Jadwal -->
            <div class="mb-3">
                <label for="id_jadwal" class="form-label">Pilih Jadwal</label>
                <select class="form-control" id="id_jadwal" name="id_jadwal" required onchange="autofillData()">
                    <option value="">-- Pilih Jadwal --</option>
                    <?php while ($row = $result_jadwal->fetch_assoc()): ?>
                        <option
                            value="<?= htmlspecialchars($row['id_jadwal']) ?>"
                            data-id_guru="<?= htmlspecialchars($row['id_guru']) ?>"
                            data-id_paket="<?= htmlspecialchars($row['id_paket']) ?>"
                            data-tanggal="<?= htmlspecialchars($row['tanggal_jadwal']) ?>"
                            data-jam_masuk="<?= htmlspecialchars($row['jam_masuk']) ?>"
                            data-jam_keluar="<?= htmlspecialchars($row['jam_keluar']) ?>">
                            <?= htmlspecialchars($row['id_jadwal']) ?> - <?= htmlspecialchars($row['nama_guru']) ?> (<?= htmlspecialchars($row['nama_paket']) ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- ID Guru -->
            <div class="form-group mb-3">
                <label for="id_guru">ID Guru</label>
                <input type="text" class="form-control" id="id_guru" name="id_guru" readonly>
            </div>

            <!-- ID Paket -->
            <div class="form-group mb-3">
                <label for="id_paket">ID Paket</label>
                <input type="text" class="form-control" id="id_paket" name="id_paket" readonly>
            </div>

            <!-- Pilih ID Murid -->
            <div class="form-group mb-3" id="murid-container">
                <label for="id_murid">Pilih ID Murid</label>
                <div class="murid-entry mb-2 d-flex align-items-center">
                    <select class="form-control me-2" name="id_murid[]" required onchange="autofillNamaMurid(this)">
                        <option value="">-- Pilih ID Murid --</option>
                        <?php while ($row = $result_murid->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row['id_murid']) ?>" data-nama="<?= htmlspecialchars($row['nama']) ?>">
                                <?= htmlspecialchars($row['id_murid']) ?> - <?= htmlspecialchars($row['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <input type="text" class="form-control me-2" name="nama_murid[]" placeholder="Nama Murid" readonly>
                    <button type="button" class="btn btn-danger" onclick="removeMurid(this)">Hapus</button>
                </div>
            </div>

            <!-- Tombol Tambah Murid -->
            <div class="d-grid gap-2 mb-3">
                <button type="button" class="btn btn-success" onclick="addMurid()">Tambah Murid</button>
            </div>

            <!-- Tanggal Presensi -->
            <div class="mb-3">
                <label for="tanggal_presensi" class="form-label">Tanggal Presensi</label>
                <input type="date" class="form-control" id="tanggal_presensi" name="tanggal_presensi" readonly>
            </div>

            <!-- Jam Masuk -->
            <div class="mb-3">
                <label for="jam_masuk" class="form-label">Jam Masuk</label>
                <input type="time" class="form-control" id="jam_masuk" name="jam_masuk" readonly>
            </div>

            <!-- Jam Keluar -->
            <div class="mb-3">
                <label for="jam_keluar" class="form-label">Jam Keluar</label>
                <input type="time" class="form-control" id="jam_keluar" name="jam_keluar" readonly>
            </div>

            <!-- Tombol Submit -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" name="tambah_presensi">Simpan Presensi</button>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    </body>

    <!-- Bootstrap JS -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    </body>

</html>

</html>


<!-- JavaScript -->
<script>
    function autofillData() {
        // Ambil elemen dropdown dan input field
        let jadwalSelect = document.getElementById("id_jadwal");
        let selectedOption = jadwalSelect.options[jadwalSelect.selectedIndex];

        // Ambil data dari atribut yang sudah diset sebelumnya
        let idGuru = selectedOption.getAttribute("data-id_guru");
        let idPaket = selectedOption.getAttribute("data-id_paket");
        let tanggalPresensi = selectedOption.getAttribute("data-tanggal");
        let jamMasuk = selectedOption.getAttribute("data-jam_masuk");
        let jamKeluar = selectedOption.getAttribute("data-jam_keluar");

        // Isi input field secara otomatis
        document.getElementById("id_guru").value = idGuru ? idGuru : '';
        document.getElementById("id_paket").value = idPaket ? idPaket : '';
        document.getElementById("tanggal_presensi").value = tanggalPresensi ? tanggalPresensi : '';
        document.getElementById("jam_masuk").value = jamMasuk ? jamMasuk : '';
        document.getElementById("jam_keluar").value = jamKeluar ? jamKeluar : '';
    }


    function autofillNamaMurid(selectElement) {
        let selectedOption = selectElement.options[selectElement.selectedIndex];
        let namaMurid = selectedOption.getAttribute("data-nama");

        // Ambil input nama murid yang berada di sebelah select dropdown
        let namaInput = selectElement.parentElement.querySelector('input[name="nama_murid[]"]');
        namaInput.value = namaMurid ? namaMurid : '';
    }

    // Fungsi untuk menambahkan dropdown murid baru
    function addMurid() {
        let container = document.getElementById("murid-container");
        let muridEntries = container.querySelectorAll(".murid-entry");
        let lastMurid = muridEntries[muridEntries.length - 1];
        let newMurid = lastMurid.cloneNode(true);

        // Reset nilai dropdown dan input
        let select = newMurid.querySelector('select');
        let input = newMurid.querySelector('input[name="nama_murid[]"]');
        select.value = "";
        input.value = "";

        container.appendChild(newMurid);
    }

    // Fungsi untuk menghapus dropdown murid
    function removeMurid(button) {
        let container = document.getElementById("murid-container");
        let muridEntries = container.querySelectorAll(".murid-entry");

        // Hapus hanya jika lebih dari 1 input yang tersedia
        if (muridEntries.length > 1) {
            button.parentElement.remove();
        } else {
            alert("Minimal harus ada satu murid yang dipilih.");
        }
    }

    console.log("Tanggal:", tanggalPresensi, "Jam Masuk:", jamMasuk, "Jam Keluar:", jamKeluar);

    const today = new Date().toISOString().split('T')[0];
    document.getElementById("tanggal_jadwal").setAttribute("min", today);
</script>

</main>
<?= require('layouts/footer.php'); ?>
</body>

</html>