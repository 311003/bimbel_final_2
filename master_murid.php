<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara

// Ambil daftar status murid
$statusQuery = "SELECT * FROM status_murid";
$statusResult = $conn->query($statusQuery);
$statusOptions = [];

while ($row = $statusResult->fetch_assoc()) {
    $statusOptions[$row['id_status_murid']] = $row['status_murid'];
}

// Ambil filter status jika ada
$filter_status = $_GET['filter_status'] ?? '';

// Query to fetch data including status murid
$query = "
    SELECT 
        mm.id_murid, 
        mm.nama, 
        mm.tanggal_lahir, 
        mm.alamat, 
        mm.no_telp, 
        mm.kelas, 
        mm.asal_sekolah, 
        sm.status_murid AS status_murid
    FROM master_murid mm
    LEFT JOIN status_murid sm ON mm.status_murid = sm.id_status_murid
    WHERE mm.id_murid NOT IN (SELECT id_murid FROM registrasi_batal)";

if (!empty($filter_status)) {
    $query .= " AND sm.id_status_murid = '$filter_status'";
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Master Murid</title>
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
    <link href="assets/vendor/DataTables/datatables.min.css" rel="stylesheet">

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
            <h1>Tabel Murid</h1>
        </div>

        <!-- Filter Status -->
        <form method="GET" action="master_murid.php" class="mb-3">
            <label for="filter_status">Filter Status:</label>
            <select name="filter_status" id="filter_status" onchange="this.form.submit()">
                <option value="">Semua</option>
                <?php
                foreach ($statusOptions as $id => $status) {
                    $selected = ($filter_status == $id) ? "selected" : "";
                    echo "<option value='$id' $selected>$status</option>";
                }
                ?>
            </select>
        </form>

        <!-- Begin Page Content -->
        <div class="container-fluid">
            <table id="viewTable" class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Murid</th>
                        <th>Nama</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>No Telepon</th>
                        <th>Kelas</th>
                        <th>Asal Sekolah</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id_murid']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                            echo "<td>" . htmlspecialchars(date('d F Y', strtotime($row['tanggal_lahir']))) . "</td>";
                            echo "<td>" . htmlspecialchars($row['alamat']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['no_telp']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['kelas']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['asal_sekolah']) . "</td>";
                            echo "<td>
                            <form method='POST' action='update_status_murid.php'>
                                <select name='status_murid' onchange='this.form.submit()'>
                                    <option value='Aktif'" . ($row['status_murid'] == 'Aktif' ? ' selected' : '') . ">Aktif</option>
                                    <option value='Tidak Aktif'" . ($row['status_murid'] == 'Tidak Aktif' ? ' selected' : '') . ">Tidak Aktif</option>
                                </select>
                                <input type='hidden' name='id_murid' value='" . $row['id_murid'] . "' />
                            </form>
                        </td>";
                            echo "<td>
                                <a href='edit_murid.php?id_murid=" . $row['id_murid'] . "' class='btn btn-sm btn-warning'>Edit</a>
                            </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11' class='text-center'>Data tidak ditemukan</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
    <?= require('layouts/footer.php') ?>
    <script>
            let table = new DataTable('#viewTable', {
            // options
            // lengthMenu: [
            //     [20, 30, 40, -1],
            //     [20, 30, 40, 'All']
            // ]
        });

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

        // Add a function to handle the update when a new status or package is selected
        function handleUpdate(select, id_murid, type) {
            let value = select.value;
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = 'master_murid.php'; // Make sure the correct file is used

            let hiddenInput1 = document.createElement('input');
            hiddenInput1.type = 'hidden';
            hiddenInput1.name = 'id_murid';
            hiddenInput1.value = id_murid;
            form.appendChild(hiddenInput1);

            if (type == "status") {
                let hiddenInput2 = document.createElement('input');
                hiddenInput2.type = 'hidden';
                hiddenInput2.name = 'status_murid';
                hiddenInput2.value = value;
                form.appendChild(hiddenInput2);
            }

            document.body.appendChild(form);
            form.submit();
        }
    </script>

</body>

</html>