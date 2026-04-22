-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2026 at 03:05 AM
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
  `waktu_checkin` datetime DEFAULT NULL,
  `cancel_request` enum('pending','approved','rejected') DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `cancel_request_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendee`
--

INSERT INTO `attendee` (`id_attendee`, `id_detail`, `kode_tiket`, `status_checkin`, `created_at`, `waktu_checkin`, `cancel_request`, `cancel_reason`, `cancel_request_date`) VALUES
(21, 17, 'TKT-20260420-DC3EB9-01', 'sudah', '2026-04-19 18:59:09', '2026-04-19 19:14:06', NULL, NULL, NULL),
(22, 17, 'TKT-20260420-DC51D3-02', 'sudah', '2026-04-19 18:59:09', '2026-04-19 19:14:12', NULL, NULL, NULL),
(23, 18, 'TKT-20260420-52E523-01', 'sudah', '2026-04-19 19:00:21', '2026-04-19 19:14:18', NULL, NULL, NULL),
(24, 19, 'TKT-20260420-96D3D2-01', 'belum', '2026-04-19 19:01:29', NULL, NULL, NULL, NULL),
(25, 20, 'TKT-20260420-4A0EB7-01', 'sudah', '2026-04-19 19:01:40', '2026-04-20 17:39:40', NULL, NULL, NULL);

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
(9, 'Gema Suara Nadin Amizah', '2026-04-25', 8, 'Bernyanyi bersama nadin amizah', '1776649582_69e5856e0c741.jpg'),
(10, 'Suara Baskara', '2026-04-26', 9, 'Angkat minumanmu bersedih bersama sama', '1776649761_69e58621311d9.jpg'),
(11, 'Kata Pamungkas', '2026-04-27', 10, 'Thank you fot stopping by, leave the door wide open.', '1776650006_69e587163cf3a.jpg'),
(12, 'Reality Club', '2026-04-28', 11, 'And if i was a fool for youu\r\n', '1776650216_69e587e8ad622.jpg');

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
(18, 'ORD-20260420-69E5886DC00EA', 4, 9, '2026-04-20 03:59:09', 250000, 62500, 187500, 'pending', 13),
(19, 'ORD-20260420-69E588B526AD6', 5, 11, '2026-04-20 04:00:21', 250000, 37500, 212500, 'pending', 15),
(20, 'ORD-20260420-69E588F9688D0', 6, 10, '2026-04-20 04:01:29', 300000, 45000, 255000, 'pending', 14),
(21, 'ORD-20260420-69E589049907A', 6, 12, '2026-04-20 04:01:40', 350000, 87500, 262500, 'pending', 16),
(22, 'ORD-20260420-69E5AB58E96D9', 4, 11, '2026-04-20 06:28:08', 250000, 37500, 212500, 'pending', 15),
(23, 'ORD-20260421-69E6C709AD067', 4, 12, '2026-04-21 02:38:33', 700000, 175000, 525000, 'cancel', 16),
(24, 'ORD-20260421-69E6CF35B9DB7', 6, 11, '2026-04-21 03:13:25', 250000, 37500, 212500, 'cancel', 15);

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
(17, 18, 8, 'Sorai Kita', 125000, 2, 250000),
(18, 19, 10, 'Pamungkas ', 250000, 1, 250000),
(19, 20, 9, 'Baskara Hindia', 300000, 1, 300000),
(20, 21, 11, 'Reality Club', 350000, 1, 350000),
(21, 22, 10, 'Pamungkas ', 250000, 1, 250000),
(22, 23, 11, 'Reality Club', 350000, 2, 700000),
(23, 24, 10, 'Pamungkas ', 250000, 1, 250000);

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
(8, 9, 'Sorai Kita', 125000, 48),
(9, 10, 'Baskara Hindia', 300000, 99),
(10, 11, 'Pamungkas ', 250000, 87),
(11, 12, 'Reality Club', 350000, 47);

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
(3, 'Petugas', 'petugas@gmail.com', '123', 'petugas'),
(4, 'Tasya Husna Kamila', 'tasya@gmail.com', 'tasya123', 'user'),
(5, 'El Syarawi Benedict', 'el@gmail.com', 'el123', 'user'),
(6, 'Abigail Lituhayu', 'abigail@gmail.com', 'abigail123', 'user'),
(7, 'Midas HD', 'hd@gmail.com', 'hd123', 'user');

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
(8, 'Stadion Utama', 'Magelang, Jawa Tengah', 50),
(9, 'Stadion Utara', 'Surabaya, Jawa Timur', 100),
(10, 'Lapangan Selatan', 'Pati, Jawa Tengah', 200),
(11, 'Gedung Jayaraja', 'Solo, Jawa Tengah', 100);

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
(13, 'SORAINA01', 25, 9, NULL, 19, 'aktif'),
(14, 'MEMBASUH02', 15, 10, NULL, 99, 'aktif'),
(15, 'BAMBINA03', 15, 11, NULL, 17, 'aktif'),
(16, 'ALEXANDRA04', 25, 12, NULL, 28, 'aktif');

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
  MODIFY `id_attendee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `id_event` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tiket`
--
ALTER TABLE `tiket`
  MODIFY `id_tiket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `venue`
--
ALTER TABLE `venue`
  MODIFY `id_venue` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id_voucher` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
