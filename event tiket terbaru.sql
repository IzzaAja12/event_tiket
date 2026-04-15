-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2026 at 09:33 AM
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
-- Database: `event_tiket`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendee`
--

CREATE TABLE `attendee` (
  `id_attendee` int(11) NOT NULL,
  `id_detail` int(11) DEFAULT NULL,
  `kode_tiket` varchar(50) DEFAULT NULL,
  `status_checkin` enum('belum','sudah') DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `waktu_checkin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendee`
--

INSERT INTO `attendee` (`id_attendee`, `id_detail`, `kode_tiket`, `status_checkin`, `created_at`, `waktu_checkin`) VALUES
(8, 9, 'TKT-20260415-0265B8-01', 'sudah', '2026-04-14 21:10:24', '2026-04-14 23:51:54'),
(9, 9, 'TKT-20260415-027FDB-02', 'sudah', '2026-04-14 21:10:24', '2026-04-14 23:52:06'),
(10, 10, 'TKT-20260415-6662D0-01', 'sudah', '2026-04-14 22:00:54', '2026-04-14 23:52:11'),
(11, 11, 'TKT-20260415-A95BBB-01', 'sudah', '2026-04-14 23:33:46', '2026-04-14 23:52:59'),
(12, 12, 'TKT-20260415-6D6614-01', 'sudah', '2026-04-14 23:54:30', '2026-04-14 23:55:22'),
(13, 12, 'TKT-20260415-6D7891-02', 'sudah', '2026-04-14 23:54:30', '2026-04-14 23:55:55'),
(14, 13, 'TKT-20260415-F2E09F-01', 'sudah', '2026-04-15 00:02:23', '2026-04-15 00:02:46'),
(15, 14, 'TKT-20260415-6AED83-01', 'belum', '2026-04-15 00:13:26', NULL),
(16, 14, 'TKT-20260415-6AFF9D-02', 'belum', '2026-04-15 00:13:26', NULL),
(17, 14, 'TKT-20260415-6B2AA4-03', 'sudah', '2026-04-15 00:13:26', '2026-04-15 00:14:19'),
(18, 14, 'TKT-20260415-6B3B39-04', 'sudah', '2026-04-15 00:13:26', '2026-04-15 00:14:35');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `id_event` int(11) NOT NULL,
  `nama_event` varchar(150) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `id_venue` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`id_event`, `nama_event`, `tanggal`, `id_venue`, `deskripsi`, `foto`) VALUES
(4, 'Konser Musik Nadin Amizah', '2026-04-18', 3, 'konser musik bersama nadin amizah', '1776228098_69df1702c223f.jpg'),
(5, 'Orkestra Sastra Jawa', '2026-04-25', 4, 'larik sastra jawa dalam balutan simfoni nada', '1776228159_69df173f67e12.jpg'),
(6, 'konser pamungkas', '2026-04-15', 5, 'dfgdfgdfgdfgdfgdfgd', '1776234422_69df2fb69c34e.jpg'),
(7, 'pesta bakso', '2026-04-15', 6, 'ggsdgdgfgdgfd', '1776237101_69df3a2da73f2.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `no_order` varchar(50) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_event` int(11) DEFAULT NULL,
  `tanggal_order` datetime DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL,
  `potongan` int(11) DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `status` enum('pending','paid','cancel') DEFAULT NULL,
  `id_voucher` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id_order`, `no_order`, `id_user`, `id_event`, `tanggal_order`, `subtotal`, `potongan`, `total`, `status`, `id_voucher`) VALUES
(10, 'ORD-20260415-69DF0FB022447', 2, 4, '2026-04-15 06:10:24', 600000, 150000, 450000, 'pending', 3),
(11, 'ORD-20260415-69DF1B865E342', 2, 5, '2026-04-15 07:00:54', 50000, 12500, 37500, 'pending', 3),
(12, 'ORD-20260415-69DF314A91066', 2, 6, '2026-04-15 08:33:46', 120000, 0, 120000, 'pending', NULL),
(13, 'ORD-20260415-69DF3626CE151', 2, 6, '2026-04-15 08:54:30', 240000, 36000, 204000, 'pending', 10),
(14, 'ORD-20260415-69DF37FF29DB6', 2, 4, '2026-04-15 09:02:23', 300000, 0, 300000, 'pending', NULL),
(15, 'ORD-20260415-69DF3A96A300A', 2, 7, '2026-04-15 09:13:26', 200000, 10000, 190000, 'pending', 11);

-- --------------------------------------------------------

--
-- Table structure for table `order_detail`
--

CREATE TABLE `order_detail` (
  `id_detail` int(11) NOT NULL,
  `id_order` int(11) DEFAULT NULL,
  `id_tiket` int(11) DEFAULT NULL,
  `nama_tiket` varchar(100) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_detail`
--

INSERT INTO `order_detail` (`id_detail`, `id_order`, `id_tiket`, `nama_tiket`, `harga`, `qty`, `subtotal`) VALUES
(9, 10, 3, 'NADINAMZ', 300000, 2, 600000),
(10, 11, 4, 'JAWASASTRA', 50000, 1, 50000),
(11, 12, 5, 'PAMNG', 120000, 1, 120000),
(12, 13, 5, 'PAMNG', 120000, 2, 240000),
(13, 14, 3, 'NADINAMZ', 300000, 1, 300000),
(14, 15, 6, 'bakso enak', 50000, 4, 200000);

-- --------------------------------------------------------

--
-- Table structure for table `tiket`
--

CREATE TABLE `tiket` (
  `id_tiket` int(11) NOT NULL,
  `id_event` int(11) DEFAULT NULL,
  `nama_tiket` varchar(50) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `kuota` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tiket`
--

INSERT INTO `tiket` (`id_tiket`, `id_event`, `nama_tiket`, `harga`, `kuota`) VALUES
(3, 4, 'NADINAMZ', 300000, 87),
(4, 5, 'JAWASASTRA', 50000, 49),
(5, 6, 'PAMNG', 120000, 47),
(6, 7, 'bakso enak', 50000, 26);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','petugas','admin') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`) VALUES
(1, 'Admin', 'admin@gmail.com', '123', 'admin'),
(2, 'Izza', 'izza@gmail.com', '123', 'user'),
(3, 'Petugas', 'petugas@gmail.com', '123', 'petugas');

-- --------------------------------------------------------

--
-- Table structure for table `venue`
--

CREATE TABLE `venue` (
  `id_venue` int(11) NOT NULL,
  `nama_venue` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `kapasitas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venue`
--

INSERT INTO `venue` (`id_venue`, `nama_venue`, `alamat`, `kapasitas`) VALUES
(3, 'Stadion Utama', 'Magelang, Jawa Tengah', 90),
(4, 'Dalem Keraton Solo', 'Solo, Jawa Tengah', 50),
(5, 'Alun alun ', 'magelang', 50),
(6, 'lapangan', 'grabag', 30);

-- --------------------------------------------------------

--
-- Table structure for table `voucher`
--

CREATE TABLE `voucher` (
  `id_voucher` int(11) NOT NULL,
  `kode_voucher` varchar(20) DEFAULT NULL,
  `potongan` int(11) DEFAULT NULL,
  `id_event` int(11) DEFAULT NULL,
  `id_venue` int(11) DEFAULT NULL,
  `kuota` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `voucher`
--

INSERT INTO `voucher` (`id_voucher`, `kode_voucher`, `potongan`, `id_event`, `id_venue`, `kuota`, `status`) VALUES
(3, 'SORAI', 25, NULL, NULL, 11, 'nonaktif'),
(4, 'ORJAW', 10, NULL, NULL, 4, 'aktif'),
(10, 'BAMBINA', 15, 6, NULL, 7, 'aktif'),
(11, 'BAKSOENAK', 5, 7, NULL, 9, 'aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendee`
--
ALTER TABLE `attendee`
  ADD PRIMARY KEY (`id_attendee`),
  ADD KEY `id_detail` (`id_detail`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id_event`),
  ADD KEY `id_venue` (`id_venue`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_voucher` (`id_voucher`);

--
-- Indexes for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_tiket` (`id_tiket`);

--
-- Indexes for table `tiket`
--
ALTER TABLE `tiket`
  ADD PRIMARY KEY (`id_tiket`),
  ADD KEY `id_event` (`id_event`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `venue`
--
ALTER TABLE `venue`
  ADD PRIMARY KEY (`id_venue`);

--
-- Indexes for table `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id_voucher`),
  ADD KEY `fk_voucher_to_event` (`id_event`),
  ADD KEY `fk_voucher_to_venue` (`id_venue`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendee`
--
ALTER TABLE `attendee`
  MODIFY `id_attendee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id_event` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tiket`
--
ALTER TABLE `tiket`
  MODIFY `id_tiket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `venue`
--
ALTER TABLE `venue`
  MODIFY `id_venue` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id_voucher` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendee`
--
ALTER TABLE `attendee`
  ADD CONSTRAINT `attendee_ibfk_1` FOREIGN KEY (`id_detail`) REFERENCES `order_detail` (`id_detail`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`id_venue`) REFERENCES `venue` (`id_venue`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_voucher`) REFERENCES `voucher` (`id_voucher`);

--
-- Constraints for table `order_detail`
--
ALTER TABLE `order_detail`
  ADD CONSTRAINT `order_detail_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`),
  ADD CONSTRAINT `order_detail_ibfk_2` FOREIGN KEY (`id_tiket`) REFERENCES `tiket` (`id_tiket`);

--
-- Constraints for table `tiket`
--
ALTER TABLE `tiket`
  ADD CONSTRAINT `tiket_ibfk_1` FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`);

--
-- Constraints for table `voucher`
--
ALTER TABLE `voucher`
  ADD CONSTRAINT `fk_voucher_to_event` FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_voucher_to_venue` FOREIGN KEY (`id_venue`) REFERENCES `venue` (`id_venue`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
