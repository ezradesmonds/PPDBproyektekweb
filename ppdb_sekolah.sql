-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 05:41 AM
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
-- Database: `ppdb_sekolah`
--

-- --------------------------------------------------------

--
-- Table structure for table `info`
--

CREATE TABLE `info` (
  `id` int(11) NOT NULL,
  `tipe` enum('beasiswa','pendaftaran','pengumuman','faq','profil') NOT NULL,
  `judul` varchar(255) NOT NULL,
  `konten` text NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `info`
--

INSERT INTO `info` (`id`, `tipe`, `judul`, `konten`, `meta`, `created_at`, `updated_at`) VALUES
(1, 'beasiswa', 'Beasiswa Indonesia Maju', 'BEASISWA 100% didanai Pak Dave', NULL, '2025-12-18 04:31:01', '2025-12-18 04:31:01'),
(2, 'pengumuman', 'ZR1E - Nilai Terkecil & Terbesar dalam Data Ujian', 'detfcvygbhj', NULL, '2025-12-18 04:38:15', '2025-12-18 04:38:15'),
(3, 'faq', 'cdftvgbj', 'buhobh', NULL, '2025-12-18 04:38:30', '2025-12-18 04:38:30'),
(4, 'beasiswa', 'upoi', ' j kln.', NULL, '2025-12-18 04:38:38', '2025-12-18 04:38:38');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `tempat_lahir` varchar(100) NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `agama` varchar(50) NOT NULL,
  `alamat` text NOT NULL,
  `nama_ayah` varchar(255) NOT NULL,
  `pekerjaan_ayah` varchar(100) NOT NULL,
  `nama_ibu` varchar(255) NOT NULL,
  `pekerjaan_ibu` varchar(100) NOT NULL,
  `sekolah_asal` varchar(255) NOT NULL,
  `jurusan_pilihan` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `kk` varchar(255) DEFAULT NULL,
  `akta` varchar(255) DEFAULT NULL,
  `sertifikat` varchar(255) DEFAULT NULL,
  `status` enum('menunggu','lolos','ditolak','diterima') NOT NULL DEFAULT 'menunggu',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id`, `user_id`, `nama_lengkap`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `agama`, `alamat`, `nama_ayah`, `pekerjaan_ayah`, `nama_ibu`, `pekerjaan_ibu`, `sekolah_asal`, `jurusan_pilihan`, `foto`, `kk`, `akta`, `sertifikat`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'Dave Marcellino Aliyan', 'Jember', '2006-03-20', 'L', 'Kristen', 'Jl. Siwalankerto 3-21', 'Yohan ', 'Dosen', 'Sebas', 'Ibu Rumah Tangga', 'Santo Clara', 'IPA', 'foto_1_1766029932.png', 'kk_1_1766029932.png', 'akta_1_1766029932.png', NULL, 'diterima', '2025-12-18 03:52:12', '2025-12-18 03:53:56'),
(2, 4, 'tfvgybhu', 'szdfdgf', '2006-06-07', 'L', 'Kasfsdx', 'vgyuihjlk;lk,', 'Yohan ', 'Dosen', 'Sebas', 'Ibu Rumah Tangga', 'Santo Clara', 'IPA', 'foto_2_1766032578.png', 'kk_2_1766032578.png', 'akta_2_1766032578.png', NULL, 'diterima', '2025-12-18 04:36:18', '2025-12-18 04:38:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','siswa') NOT NULL,
  `nisn` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `nisn`, `created_at`) VALUES
(1, 'Admin', 'admin@sekolah.com', '$2a$12$CITpCpvMLQu.bE3HnHNeOOf2lgtcZQl9KyCpXdDvE/t0W0XJ3zlu.', 'admin', NULL, '2025-12-17 05:06:58'),
(2, 'Nama lengkap', 'namalengkap@gmail.com', '$2y$10$jt1bYu.cQEx18Smd.FfGrOh3bJjjzl4E.p/E/OuMrcF19w7xUrz/u', 'siswa', '123123', '2025-12-17 11:31:54'),
(3, 'Dave Marcellino Aliyan', 'dave@gmail.com', '$2y$10$4phAAnslZYJStP.khO8gKeEghUkVkv0NQ3W7mofLkjdlaUuoOT3cq', 'siswa', '098765', '2025-12-18 03:51:08'),
(4, 'DASVID EMMANUEL', 'david@gmail.com', '$2y$10$50GUYaitV/xy30J61hHa9e/loQGgwuHsqmrJ5/qt5YLuEYFOyBHYe', 'siswa', '12345', '2025-12-18 04:34:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `info`
--
ALTER TABLE `info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nisn` (`nisn`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `info`
--
ALTER TABLE `info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
