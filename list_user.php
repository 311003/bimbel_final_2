<?php
include 'connection.php';
session_start();

$result = $conn->query("
    SELECT u.id_user, u.username, u.email, u.role, u.is_active,
           CASE u.role
               WHEN 1 THEN 'Owner'
               WHEN 2 THEN 'Guru'
               WHEN 3 THEN 'Murid'
               ELSE 'Tidak diketahui'
           END AS role_name
    FROM tm_user u
    WHERE u.role != 1;
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Master Gurur</title>
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
<div class="container mt-5">
    <h3 class="mb-3">Daftar User</h3>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <a href="master_user.php" class="btn btn-secondary mb-3">➕ Kembali ke Tambah User</a>

    <table class="table table-bordered" id="tableUser">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Reset Password</th>
                <th>Aktif / Nonaktif</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_user']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['role_name']) ?></td>
                    <td>
                        <?php if ($row['role'] == 3): ?>
                            <?= $row['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="reset_password_user.php?id_user=<?= $row['id_user'] ?>" class="btn btn-sm btn-warning">Reset</a>
                    </td>
                    <td>
                        <?php if ($row['role'] == 3): ?>
                            <a href="toggle_user_status.php?id_user=<?= $row['id_user'] ?>" 
                               class="btn btn-sm <?= $row['is_active'] ? 'btn-success' : 'btn-danger' ?>">
                                <?= $row['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?= require('layouts/footer.php'); ?>

<!-- DataTables -->
<script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
<script>
    const table = new simpleDatatables.DataTable("#tableUser", {
        searchable: true,
        fixedHeight: false,
        perPage: 10,
        perPageSelect: [10, 25, 50, -1]
    });
</script>
</body>
</html>