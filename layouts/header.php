<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'connection.php';

$user = $_SESSION['username'] ?? 'Guest';
$role = $_SESSION['role'] ?? 0;
$id_user = $_SESSION['id_user'] ?? 0;
?>

<header id="header" class="header fixed-top d-flex align-items-center">
  <div class="d-flex align-items-center justify-content-between">
    <img src="assets/img/logo_bimbel_rsdc.png" alt="Logo Bimbel RSDC" style="height: 60px; width: auto;">
    <span class="d-none d-lg-block ms-3 fs-4">Bimbel RSDC</span>
    <i class="bi bi-list toggle-sidebar-btn ms-3"></i>
  </div>

  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">

      <!-- Notifikasi -->
      <li class="nav-item dropdown">
        <a class="nav-link nav-icon" href="#" id="notifToggle" data-bs-toggle="dropdown">
          <i class="bi bi-bell"></i>
          <span id="notifCount" class="badge bg-danger badge-number" style="display:none;">0</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications" id="notifList">
          <li class="dropdown-header">Memuat notifikasi...</li>
        </ul>
      </li>

      <!-- Profil -->
      <li class="nav-item dropdown pe-3">
        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <img src="assets/img/no-profile-img.png" alt="Profile" class="rounded-circle">
          <span class="d-none d-md-block dropdown-toggle ps-2"><?= htmlspecialchars($user) ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6><?= htmlspecialchars($user) ?></h6>
            <span><?php
              switch ($role) {
                case 1: echo "Owner"; break;
                case 2: echo "Guru"; break;
                case 3: echo "Murid"; break;
                default: echo "Tamu"; break;
              }
            ?></span>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="update_account.php">
              <i class="bi bi-gear"></i><span>Pengaturan Akun</span>
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <a class="dropdown-item d-flex align-items-center" href="login.php">
              <i class="bi bi-box-arrow-right"></i><span>Keluar</span>
            </a>
          </li>
        </ul>
      </li>
    </ul>
  </nav>
</header>

<script>
function loadNotifikasi() {
  fetch('ajax/notifikasi.php')
    .then(res => res.json())
    .then(data => {
      const list = document.getElementById('notifList');
      const badge = document.getElementById('notifCount');

      list.innerHTML = '';
      let totalBaru = 0;
      if (data.length === 0) {
        list.innerHTML = '<li class="dropdown-header">Tidak ada notifikasi</li>';
        return;
      }

      list.innerHTML += `<li class="dropdown-header">Anda memiliki ${data.filter(n => n.status == 1).length} notifikasi baru</li><li><hr class="dropdown-divider"></li>`;

      data.forEach(item => {
        if (item.status == 1) totalBaru++;
        list.innerHTML += `
          <li class="notification-item">
            <i class="bi bi-info-circle text-primary"></i>
            <div>
              <h4>${item.judul}</h4>
              <p>${item.keterangan}</p>
              <p><small class="text-muted">${item.tanggal}</small></p>
              ${item.url ? `<a href="${item.url}" class="btn btn-sm btn-outline-primary">Lihat</a>` : ''}
            </div>
          </li>
          <li><hr class="dropdown-divider"></li>`;
      });

      list.innerHTML += '<li class="dropdown-footer"><a href="view_notifikasi.php">Lihat semua notifikasi</a></li>';

      badge.style.display = totalBaru > 0 ? 'inline-block' : 'none';
      badge.textContent = totalBaru;
    });
}

// load saat dropdown dibuka
let notifDropdown = document.getElementById('notifToggle');
notifDropdown.addEventListener('click', function() {
  fetch('ajax/notifikasi_update_status.php'); // tandai semua sebagai dibaca
  loadNotifikasi();
});

setInterval(loadNotifikasi, 15000); // auto-refresh setiap 15 detik
loadNotifikasi();
</script>