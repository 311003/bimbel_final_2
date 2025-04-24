<?php
session_start();
include 'connection.php';

// Hanya untuk owner
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: login.php");
    exit;
}

// Update data jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id_user = $_POST['id_user'];
    $role = $_POST['role'];
    $id_ref = $_POST['id_ref'];
    $is_active = $_POST['is_active'];

    $stmt = $conn->prepare("UPDATE tm_user SET role=?, id_ref=?, is_active=? WHERE id_user=?");
    $stmt->bind_param("isii", $role, $id_ref, $is_active, $id_user);
    $stmt->execute();
}

// Ambil semua user selain owner
$result = $conn->query("SELECT * FROM tm_user WHERE username != 'owner'");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Kelola Akun Pengguna</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h3 class="mb-4">🔧 Kelola Akun Pengguna</h3>
  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>ID Referensi</th>
        <th>Status Aktif</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
          <form method="POST">
            <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">

            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>

            <td>
              <select name="role" class="form-select" required>
                <option value="2" <?= $user['role'] == 2 ? 'selected' : '' ?>>Guru</option>
                <option value="3" <?= $user['role'] == 3 ? 'selected' : '' ?>>Murid</option>
              </select>
            </td>

            <td><input type="text" name="id_ref" class="form-control" value="<?= htmlspecialchars($user['id_ref']) ?>" required></td>

            <td>
              <select name="is_active" class="form-select">
                <option value="1" <?= $user['is_active'] == 1 ? 'selected' : '' ?>>Aktif</option>
                <option value="0" <?= $user['is_active'] == 0 ? 'selected' : '' ?>>Tidak Aktif</option>
              </select>
            </td>

            <td><button type="submit" name="update" class="btn btn-sm btn-primary">Simpan</button></td>
          </form>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
