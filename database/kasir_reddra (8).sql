-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Apr 2025 pada 16.03
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
  `ProdukID` int(10) DEFAULT NULL,
  `user_id` int(10) DEFAULT NULL,
  `JumlahProduk` int(10) DEFAULT NULL,
  `Subtotal` int(10) DEFAULT NULL,
  `kode_pembayaran` int(10) NOT NULL,
  `total_harga` int(11) NOT NULL,
  `kembalian` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `detailpenjualan`
--

INSERT INTO `detailpenjualan` (`DetailID`, `PenjualanID`, `ProdukID`, `user_id`, `JumlahProduk`, `Subtotal`, `kode_pembayaran`, `total_harga`, `kembalian`) VALUES
(141, 132, 70, 5, 2, 4000, 0, 4320, 0);

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
(6, 'robot', 'ee'),
(11, '35tet', 'tt'),
(12, 'trt', 'www'),
(13, 'ucin', 'trtg g'),
(16, 'dwwwwr', 'rwrde'),
(17, 'wder3rw', 'dfwdw'),
(18, 'rwdw', 'dfwdr'),
(22, 'pc', 'ttt'),
(23, 'seafood', 'ffss'),
(24, 'baja', 'dd'),
(25, 'VIRUS', 'gssdgsgd');

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
(31, 'Rendi', 'Cikakak', '90898', 'Laki_Laki'),
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
(132, '2025-04-14 20:58:49', NULL, 33, 'belum dibayar', 'INV1744639129', '10.00', '20.00', '2025-04-14 20:58:49');

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
(69, '11', '11.00', 8, 18, '10', 'uploads/1744624025_dreamy3.png'),
(70, 'tv', '2000.00', 968, 24, 'he;;o', 'uploads/1744537573_dreamy.png'),
(71, 'pc', '11.00', 0, 18, 'ee', 'uploads/1744537627_annisha.jpg'),
(72, 'vga', '100000.00', 4990, 22, 'ajbasjhfhbfHHFB', 'uploads/1744537673_logo pkg.png'),
(73, 'tank', '1000.00', 995, 24, 'ini barang sangat di akui', 'uploads/1744604864_2.png'),
(74, 'virus tnt', '10000.00', 9, 25, 'virus tnt murapakan virus yang sangat berbahaya', 'uploads/1744624277_2.png');

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
(5, 'admin', 'c1c274067990c8a9d6dda3d93a0a1290', 'admin', '$2y$10$BgJG6wJ9QT6OpP.55bksduGzOuRzO9gyz/Kmlkd/lg0AM.4QXKR6q', 'admin', ''),
(17, 'wahid', '', 'owner', '$2y$10$ZZXPKBfanXe/R4zGKw7l9uuEumJ2wuWO0/xLUIjeeUh.S6FZUYuJ2', 'owner', ''),
(24, 'alif', '', 'alif33', '$2y$10$sb8ksSlp3sswRLPXKV8HvuC/nU9Rb24wReim529CnG29AuBAkDydq', 'admin', 'uploads/1744606813_3.jpg');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD PRIMARY KEY (`DetailID`),
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
  MODIFY `DetailID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `kategori_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
  MODIFY `PenjualanID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `ProdukID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT untuk tabel `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD CONSTRAINT `detailpenjualan_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detailpenjualan_ibfk_3` FOREIGN KEY (`ProdukID`) REFERENCES `produk` (`ProdukID`) ON DELETE CASCADE ON UPDATE CASCADE,
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
