
<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link" href="dashboard_owner.php">
        <i class="bi bi-grid"></i>
        <span>Beranda</span>
      </a>
    </li><!-- End Dashboard Nav -->

    <!-- Registrasi Murid -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#registrasi-nav" href="#">
        <i class="bi bi-person-plus"></i><span>Registrasi Murid</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="registrasi-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li><a href="input_registrasi.php"><i class="bi bi-circle"></i><span>Tambah Data</span></a></li>
        <li><a href="konfirmasi_registrasi.php"><i class="bi bi-circle"></i><span>Konfirmasi Registrasi</span></a></li>
        <li><a href="view_konfirmasi_registrasi.php"><i class="bi bi-circle"></i><span>Lihat Registrasi</span></a></li>
      </ul>
    </li>

    <!-- Jadwal -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#jadwal-nav" href="#">
        <i class="bi bi-calendar"></i><span>Jadwal</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="jadwal-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li><a href="input_jadwal.php"><i class="bi bi-circle"></i><span>Tambah Data</span></a></li>
        <li><a href="hasil_data_jadwal.php"><i class="bi bi-circle"></i><span>Hasil Data</span></a></li>
      </ul>
    </li>

    <!-- Pembayaran -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#pembayaran-nav" href="#">
        <i class="bi bi-cash-stack"></i><span>Pembayaran</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="pembayaran-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
      <li><a href="hasil_data_pembayaran.php"><i class="bi bi-circle"></i><span>Hasil Data Murid</span></a></li>
      <li><a href="hasil_data_pembayaran_guru.php"><i class="bi bi-circle"></i><span>Gaji Guru</span></a></li>
      </ul>
    </li>

    <!-- Master -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#master-nav" href="#">
        <i class="bi bi-database"></i><span>Master</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="master-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li><a href="master_murid.php"><i class="bi bi-circle"></i><span>Murid</span></a></li>
        <li><a href="master_guru.php"><i class="bi bi-circle"></i><span>Guru</span></a></li>
        <li><a href="master_paket.php"><i class="bi bi-circle"></i><span>Paket</span></a></li>
        <li><a href="master_user.php"><i class="bi bi-circle"></i><span>Akun Pengguna</span></a></li>
      </ul>
    </li>

    <!-- Laporan -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#laporannav" href="#">
        <i class="bi bi-report"></i><span>Laporan</span>
        <i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="laporannav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
        <li><a href="cashflow_view.php"><i class="bi bi-circle"></i><span>Cashflow</span></a></li>
        <li><a href="master_guru.php"><i class="bi bi-circle"></i><span>Gaji Guru</span></a></li>
        <li><a href="master_paket.php"><i class="bi bi-circle"></i><span>Murid</span></a></li>
      </ul>
    </li>

  </ul>
</aside><!-- End Sidebar -->