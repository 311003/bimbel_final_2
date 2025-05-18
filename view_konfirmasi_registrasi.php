<?php
include 'connection.php'; // Pastikan file koneksi database sudah di-include
session_start();
echo "ROLE: " . ($_SESSION['role'] ?? 'NOT SET'); // Debug sementara

// Fetch the validated students' data
$query_valid = "SELECT r.*, p.paket 
                FROM registrasi_murid r 
                LEFT JOIN paket_bimbel p ON r.id_paket = p.id_paket 
                WHERE r.konfirmasi_registrasi = 'Divalidasi'";  // Ensure only validated students are selected
$result_valid = $conn->query($query_valid);

// Check if the query executed correctly and contains results
if (!$result_valid) {
    echo "<script>alert('Error fetching validated students: " . $conn->error . "');</script>";
    exit();  // Exit the script if the query fails
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>View Konfirmasi Registrasi</title>
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
        <div class="container mt-4">
            <h2 class="text-center">View Konfirmasi Data Registrasi Murid</h2>

            <!-- ✅ Tabel Murid yang Jadi Ikut Bimbel -->
            <h4 class="mt-4 text-success">✅ Murid yang Divalidasi</h4>
            <table id="tableView" class="table table-bordered">
                <thead class="table-success">
                    <tr>
                        <th>No Registrasi</th>
                        <th>Nama</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Kelas</th>
                        <th>Asal Sekolah</th>
                        <th>Jenis Kelamin</th>
                        <th>No Telepon</th>
                        <th>Paket</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if results exist and then render them
                    if ($result_valid->num_rows > 0) {
                        // Loop through the results
                        while ($row = $result_valid->fetch_assoc()) {
                    ?>
                            <tr>
                                <td><?= htmlspecialchars($row['no_reg']) ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['tanggal_lahir']) ?></td>
                                <td><?= htmlspecialchars($row['alamat']) ?></td>
                                <td><?= htmlspecialchars($row['kelas']) ?></td>
                                <td><?= htmlspecialchars($row['asal_sekolah']) ?></td>
                                <td><?= ($row['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan' ?></td>
                                <td><?= htmlspecialchars($row['no_telp']) ?></td>
                                <td><?= !empty($row['paket']) ? htmlspecialchars($row['paket']) : '<i>Tidak Ditemukan</i>' ?></td>
                            </tr>
                    <?php
                        }
                    } else {
                        // If no validated students, show a message
                        echo "<tr><td colspan='9' class='text-center'>No validated students found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
    <?= require('layouts/footer.php') ?>
    <script type="text/javascript">
        let table = new DataTable('#tableView', {
            // options
            // lengthMenu: [
            //     [20, 30, 40, -1],
            //     [20, 30, 40, 'All']
            // ]
        });

    </script>
</body>

</html>