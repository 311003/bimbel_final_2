-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 14, 2025 at 09:13 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bimbel`
--

-- --------------------------------------------------------

--
-- Table structure for table `registrasi_murid`
--

CREATE TABLE `registrasi_murid` (
  `id_murid` varchar(10) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `kelas` varchar(20) NOT NULL,
  `asal_sekolah` varchar(100) NOT NULL,
  `paket_bimbel` varchar(255) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrasi_murid`
--

INSERT INTO `registrasi_murid` (`id_murid`, `nama`, `tanggal_lahir`, `alamat`, `kelas`, `asal_sekolah`, `paket_bimbel`, `jenis_kelamin`) VALUES
('1', 'Santi', '2025-02-04', 'Malang', '3 SMA', 'SMA 1 Malang', 'Semua Mapel Sekolah', 'P'),
('2', 'Adin', '2003-10-14', 'Tunggulwulung', 'TK A', 'TK Al-Ikhlas', 'Matematika', 'P'),
('3', 'Andy', '2025-02-14', 'Malang', '1 SD', 'SDN 1 Malang', 'Bahasa Inggris', 'L'),
('4', 'Bambang', '2025-02-18', 'Tunggulwulung', '3', 'TK Al-Ikhlas', 'Calistung', 'L'),
('5', 'Nana', '2025-02-03', 'Tunggulwulung', '11', 'SMA 1 Malang', 'Calistung', 'L'),
('6', 'AA', '2025-02-18', 'Tunggulwulung', '1 SD', 'SDN 1 Malang', 'Bahasa Inggris', 'L');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `registrasi_murid`
--
ALTER TABLE `registrasi_murid`
  ADD PRIMARY KEY (`id_murid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
