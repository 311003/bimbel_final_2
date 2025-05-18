<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link" href="dashboard_guru.php">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <!-- Menu Guru -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#menu-guru" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Menu Guru</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="menu-guru" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li><a href="view_registrasi_guru.php"><i class="bi bi-circle"></i><span>Hasil Data Registrasi</span></a></li>
        <li><a href="view_data_guru.php"><i class="bi bi-table"></i><span>Hasil Data Guru</span></a></li>
      </ul>
    </li><!-- End Menu Guru -->

    <!-- Presensi -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#presensi-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i>
        <span>Presensi</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="presensi-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li>
          <a href="input_presensi_guru.php">
            <i class="bi bi-circle"></i>
            <span>Input</span>
          </a>
        </li>
        <li>
          <a href="hasil_presensi_guru.php">
            <i class="bi bi-circle"></i>
            <span>Hasil Data</span>
          </a>
        </li>
      </ul>
    </li><!-- End Presensi -->

</aside><!-- End Sidebar -->