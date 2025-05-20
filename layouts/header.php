<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include 'connection.php';

$user = $_SESSION['username'] ?? 'Guest';
$role = $_SESSION['role'] ?? 0;
?>

<header id="header" class="header fixed-top d-flex align-items-center">

  <!-- Logo -->
  <div class="d-flex align-items-center justify-content-between">
    <img src="assets/img/logo_bimbel_rsdc.png" alt="Logo Bimbel RSDC" style="height: 60px; width: auto;">
    <span class="d-none d-lg-block ms-3 fs-4">Bimbel RSDC</span>
    <i class="bi bi-list toggle-sidebar-btn ms-3"></i>
  </div>

  <!-- Profile -->
  <nav class="header-nav ms-auto">
    <ul class="d-flex align-items-center">
      <li class="nav-item dropdown pe-3">
        <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
          <img src="assets/img/no-profile-img.png" alt="Profile" class="rounded-circle">
          <span class="d-none d-md-block dropdown-toggle ps-2"><?= htmlspecialchars($user) ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
          <li class="dropdown-header">
            <h6><?= htmlspecialchars($user) ?></h6>
            <span>
              <?php
              switch ($role) {
                case 1: echo "Owner"; break;
                case 2: echo "Guru"; break;
                case 3: echo "Murid"; break;
                default: echo "Tamu"; break;
              }
              ?>
            </span>
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
