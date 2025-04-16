-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 16 Apr 2025 pada 01.46
-- Versi server: 10.4.22-MariaDB
-- Versi PHP: 8.1.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kasir_reddra`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detailpenjualan`
--

CREATE TABLE `detailpenjualan` (
  `DetailID` int(10) NOT NULL,
  `PenjualanID` int(10) DEFAULT NULL,
  `ProdukID` varchar(50) NOT NULL,
  `user_id` int(10) DEFAULT NULL,
  `JumlahProduk` int(10) DEFAULT NULL,
  `Subtotal` int(10) DEFAULT NULL,
  `kode_pembayaran` int(10) NOT NULL,
  `total_harga` int(11) NOT NULL,
  `kembalian` double DEFAULT NULL,
  `barang_dibeli` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `detailpenjualan`
--

INSERT INTO `detailpenjualan` (`DetailID`, `PenjualanID`, `ProdukID`, `user_id`, `JumlahProduk`, `Subtotal`, `kode_pembayaran`, `total_harga`, `kembalian`, `barang_dibeli`) VALUES
(189, 158, '76,75', 5, 2, 200000, 1744687562, 140000, 60000, 'Motor,laptop'),
(191, 160, '76,77', 5, 2, 150000, 1744700552, 150000, 0, 'Motor,rendang domba'),
(194, 163, '78', 5, 1, 10000, 0, 10000, 0, 'Pangsit Goreng'),
(196, 165, '79', 5, 1, 10000, 0, 10000, 0, 'Pangsit Rebus'),
(197, 166, '79', 5, 3, 30000, 0, 30000, 0, 'Pangsit Rebus'),
(199, 168, '79', 5, 1, 10000, 0, 10000, 0, 'Pangsit Rebus'),
(200, 169, '79', 5, 1, 10000, 0, 10000, 0, 'Pangsit Rebus'),
(202, 171, '79', 5, 1, 10000, 0, 10000, 0, 'Pangsit Rebus'),
(204, 173, '79', 5, 1, 10000, 0, 10000, 0, 'Pangsit Rebus'),
(206, 175, '78', 5, 1, 10000, 0, 10800, 0, 'Pangsit Goreng'),
(207, 176, '81', 5, 1, 10000, 0, 10000, 0, 'tv'),
(208, 177, '81', 5, 1, 10000, 0, 10000, 0, 'tv'),
(209, 178, '81', 5, 1, 10000, 0, 10000, 0, 'tv'),
(210, 179, '81', 5, 1, 10000, 0, 10000, 0, 'tv'),
(211, 180, '81', 5, 1, 10000, 0, 10000, 0, 'tv'),
(213, 182, '79', 5, 1, 10000, 0, 10000, 0, 'Pangsit Rebus'),
(214, 183, '79', 5, 1, 10000, 0, 10000, 0, 'Pangsit Rebus'),
(215, 184, '79', 5, 1, 10000, 0, 10000, 0, 'Pangsit Rebus'),
(216, 185, '79', 5, 1, 10000, 0, 10000, 0, 'Pangsit Rebus'),
(217, 186, '79,83', 5, 9, 80001, 0, 80001, 0, 'Pangsit Rebus,wew');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `kategori_id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`kategori_id`, `nama_kategori`, `deskripsi`) VALUES
(27, 'Pangsit Chili Oil', 'Pangsit Chili Oil adalah pangsit di taburi minyak yang sangat pedas dan enak'),
(29, 'Elektronik', 'Elektronik itu seperti \"ilmu tentang bagaimana caranya listrik bisa dipakai untuk menghidupkan dan mengendalikan alat-alat\".');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `pelanggan_id` int(11) DEFAULT NULL,
  `tanggal_order` datetime DEFAULT current_timestamp(),
  `status_order` enum('pending','diproses','dikirim','selesai','batal') DEFAULT 'pending',
  `metode_pembayaran` varchar(50) DEFAULT NULL,
  `total_order` decimal(12,2) DEFAULT NULL,
  `alamat_pengiriman` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_detail`
--

CREATE TABLE `order_detail` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `harga` decimal(12,2) DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `PelangganID` int(10) NOT NULL,
  `NamaPelanggan` varchar(255) DEFAULT NULL,
  `Alamat` text DEFAULT NULL,
  `NomorTelepon` varchar(20) DEFAULT NULL,
  `jk` enum('Laki_Laki','Perempuan') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`PelangganID`, `NamaPelanggan`, `Alamat`, `NomorTelepon`, `jk`) VALUES
(33, 'Aang', 'Cibodas', '99887', 'Laki_Laki'),
(34, 'Rahmita', 'Cikakak', '9978767', 'Perempuan'),
(35, 'Azril', 'Cibodas', '99887', 'Laki_Laki'),
(38, 'Rucita', '0000', '989779', 'Perempuan'),
(39, 'Adam', 'Cihaur', '9098', 'Laki_Laki'),
(41, 'wahid', 'cikakak', '9088', 'Laki_Laki'),
(42, 'reddrat', 'ckk', '0303093', 'Laki_Laki'),
(43, 'reddra master', 'sukabumi', '000000', 'Laki_Laki'),
(44, 'Harumi', '77', '77', 'Perempuan');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengiriman`
--

CREATE TABLE `pengiriman` (
  `pengiriman_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `kurir` varchar(50) DEFAULT NULL,
  `layanan` varchar(50) DEFAULT NULL,
  `estimasi_waktu` varchar(50) DEFAULT NULL,
  `biaya_pengiriman` decimal(12,2) DEFAULT NULL,
  `status_pengiriman` enum('diproses','dalam perjalanan','terkirim') DEFAULT 'diproses'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `penjualan`
--

CREATE TABLE `penjualan` (
  `PenjualanID` int(10) NOT NULL,
  `TanggalPenjualan` datetime DEFAULT NULL,
  `tgl_bayar` datetime DEFAULT NULL,
  `PelangganID` int(10) DEFAULT NULL,
  `status_bayar` enum('belum dibayar','dibayar') NOT NULL DEFAULT 'belum dibayar',
  `invoice` varchar(50) NOT NULL,
  `diskon` decimal(5,2) DEFAULT NULL,
  `pajak` decimal(5,2) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `penjualan`
--

INSERT INTO `penjualan` (`PenjualanID`, `TanggalPenjualan`, `tgl_bayar`, `PelangganID`, `status_bayar`, `invoice`, `diskon`, `pajak`, `updated_at`) VALUES
(158, '2025-04-15 10:25:35', '2025-04-15 10:26:02', 34, 'dibayar', 'INV1744687535', '50.00', '40.00', '2025-04-15 10:26:02'),
(160, '2025-04-15 14:02:09', '2025-04-15 14:02:32', 34, 'dibayar', 'INV1744700529', '0.00', '0.00', '2025-04-15 14:02:32'),
(163, '2025-04-15 15:01:04', NULL, 33, 'belum dibayar', 'INV1744704064', '0.00', '0.00', '2025-04-15 15:01:04'),
(165, '2025-04-16 00:39:43', NULL, 33, 'belum dibayar', 'INV1744738783', '0.00', '0.00', '2025-04-16 00:39:43'),
(166, '2025-04-16 00:41:01', NULL, 34, 'belum dibayar', 'INV1744738861', '0.00', '0.00', '2025-04-16 00:41:01'),
(168, '2025-04-16 00:45:35', NULL, 33, 'belum dibayar', 'INV1744739135', '0.00', '0.00', '2025-04-16 00:45:35'),
(169, '2025-04-16 00:46:17', NULL, 33, 'belum dibayar', 'INV1744739177', '0.00', '0.00', '2025-04-16 00:46:17'),
(171, '2025-04-16 01:01:23', NULL, 33, 'belum dibayar', 'INV1744740083', '0.00', '0.00', '2025-04-16 01:01:23'),
(173, '2025-04-16 01:05:41', NULL, 33, 'belum dibayar', 'INV1744740341', '0.00', '0.00', '2025-04-16 01:05:41'),
(175, '2025-04-16 01:27:04', NULL, 43, 'belum dibayar', 'INV1744741624', '10.00', '20.00', '2025-04-16 01:27:04'),
(176, '2025-04-16 02:22:11', NULL, 44, 'belum dibayar', 'INV1744744931', '0.00', '0.00', '2025-04-16 02:22:11'),
(177, '2025-04-16 02:23:16', NULL, 43, 'belum dibayar', 'INV1744744996', '0.00', '0.00', '2025-04-16 02:23:16'),
(178, '2025-04-16 02:23:43', NULL, 44, 'belum dibayar', 'INV1744745023', '0.00', '0.00', '2025-04-16 02:23:43'),
(179, '2025-04-16 02:24:19', NULL, 43, 'belum dibayar', 'INV1744745059', '0.00', '0.00', '2025-04-16 02:24:19'),
(180, '2025-04-16 02:24:45', NULL, 34, 'belum dibayar', 'INV1744745085', '0.00', '0.00', '2025-04-16 02:24:45'),
(182, '2025-04-16 02:29:01', NULL, 44, 'belum dibayar', 'INV1744745341', '0.00', '0.00', '2025-04-16 02:29:01'),
(183, '2025-04-16 02:29:20', NULL, 41, 'belum dibayar', 'INV1744745360', '0.00', '0.00', '2025-04-16 02:29:20'),
(184, '2025-04-16 02:33:49', NULL, 35, 'belum dibayar', 'INV1744745629', '0.00', '0.00', '2025-04-16 02:33:49'),
(185, '2025-04-16 02:35:08', NULL, 44, 'belum dibayar', 'INV1744745708', '0.00', '0.00', '2025-04-16 02:35:08'),
(186, '2025-04-16 04:01:48', NULL, 33, 'belum dibayar', 'INV1744750908', '0.00', '0.00', '2025-04-16 04:01:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `ProdukID` int(10) NOT NULL,
  `NamaProduk` varchar(255) DEFAULT NULL,
  `Harga` decimal(12,2) DEFAULT NULL,
  `Stok` int(255) DEFAULT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`ProdukID`, `NamaProduk`, `Harga`, `Stok`, `kategori_id`, `deskripsi`, `gambar`) VALUES
(79, 'Pangsit Rebus', '10000.00', 76, 27, 'Pangsit ini enak', 'uploads/1744703955_pexels-mikhail-nilov-8093850.jpg'),
(83, 'wew', '1.00', 0, 27, '', 'uploads/1744745876_pexels-mikhail-nilov-8093850.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `cart_id` int(11) NOT NULL,
  `pelanggan_id` int(11) DEFAULT NULL,
  `produk_id` int(11) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `UserID` int(10) NOT NULL,
  `nama_user` varchar(255) DEFAULT NULL,
  `remember_token` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','kasir','owner') DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`UserID`, `nama_user`, `remember_token`, `username`, `password`, `role`, `foto`) VALUES
(2, 'kasir', '', 'kasir', '$2y$10$IsLYLosfrTn.lLQt92YGPehbrNtBhe2YwMq/LY9tMh5d5Z/KT7s/q', 'kasir', 'uploads/1744604926_2.png'),
(5, 'Reddra', 'f5f5679df7693aab240f1a801fdba71c', 'admin', '$2y$10$BgJG6wJ9QT6OpP.55bksduGzOuRzO9gyz/Kmlkd/lg0AM.4QXKR6q', 'admin', 'uploads/1744701007_fotoreddra.jpg'),
(17, 'jir', 'adb648301b4654e78feadc325de1ab17', 'owner', '$2y$10$ZZXPKBfanXe/R4zGKw7l9uuEumJ2wuWO0/xLUIjeeUh.S6FZUYuJ2', 'owner', '');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD PRIMARY KEY (`DetailID`),
  ADD UNIQUE KEY `unique_penjualan_produk` (`PenjualanID`,`ProdukID`),
  ADD KEY `PenjualanID` (`PenjualanID`,`ProdukID`,`user_id`),
  ADD KEY `UserID` (`user_id`),
  ADD KEY `ProdukID` (`ProdukID`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`kategori_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `pelanggan_id` (`pelanggan_id`);

--
-- Indeks untuk tabel `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`PelangganID`);

--
-- Indeks untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD PRIMARY KEY (`pengiriman_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`PenjualanID`),
  ADD KEY `PelangganID` (`PelangganID`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`ProdukID`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indeks untuk tabel `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `pelanggan_id` (`pelanggan_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `unique_username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  MODIFY `DetailID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `kategori_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `PelangganID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  MODIFY `pengiriman_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `PenjualanID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=187;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `ProdukID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT untuk tabel `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD CONSTRAINT `detailpenjualan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detailpenjualan_ibfk_4` FOREIGN KEY (`PenjualanID`) REFERENCES `penjualan` (`PenjualanID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`PelangganID`);

--
-- Ketidakleluasaan untuk tabel `order_detail`
--
ALTER TABLE `order_detail`
  ADD CONSTRAINT `order_detail_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_detail_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`ProdukID`);

--
-- Ketidakleluasaan untuk tabel `pengiriman`
--
ALTER TABLE `pengiriman`
  ADD CONSTRAINT `pengiriman_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Ketidakleluasaan untuk tabel `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`PelangganID`) REFERENCES `pelanggan` (`PelangganID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`kategori_id`) ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggan` (`PelangganID`),
  ADD CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`ProdukID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
