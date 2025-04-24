<?php
include 'connection.php';
include 'db_connection.php';

$id_ref = ($role == 2) ? $_POST['id_ref_guru'] : $_POST['id_ref_murid'];

// Ambil data guru
$query_guru = "SELECT id_guru, nama_guru FROM guru";
$result_guru = $conn->query($query_guru);

// Ambil data murid
$query_murid = "SELECT id_murid, nama FROM master_murid";
$result_murid = $conn->query($query_murid);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Akun User</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2 class="text-center mb-4">Tambah Akun Pengguna</h2>
  <form action="tambah_akun_process.php" method="POST" class="card p-4 shadow">

    <div class="mb-3">
      <label for="username" class="form-label">Username</label>
      <input type="text" name="username" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="role" class="form-label">Role</label>
      <select name="role" id="role" class="form-select" onchange="toggleRef()" required>
        <option value="">-- Pilih Role --</option>
        <option value="2">Guru</option>
        <option value="3">Murid</option>
      </select>
    </div>

    <div id="guruRef" class="mb-3" style="display: none;">
      <label class="form-label">Pilih Guru</label>
      <select name="id_ref_guru" class="form-select">
        <?php while ($guru = $result_guru->fetch_assoc()): ?>
          <option value="<?= $guru['id_guru'] ?>"><?= $guru['nama_guru'] ?> (<?= $guru['id_guru'] ?>)</option>
        <?php endwhile; ?>
      </select>
    </div>

    <div id="muridRef" class="mb-3" style="display: none;">
      <label class="form-label">Pilih Murid</label>
      <select name="id_ref_murid" class="form-select">
        <?php while ($murid = $result_murid->fetch_assoc()): ?>
          <option value="<?= $murid['id_murid'] ?>"><?= $murid['nama'] ?> (<?= $murid['id_murid'] ?>)</option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="d-grid">
      <button type="submit" class="btn btn-primary">Simpan Akun</button>
    </div>
  </form>
</div>

<script>
function toggleRef() {
  const role = document.getElementById("role").value;
  document.getElementById("guruRef").style.display = (role == 2) ? "block" : "none";
  document.getElementById("muridRef").style.display = (role == 3) ? "block" : "none";
}
</script>
</body>
</html>