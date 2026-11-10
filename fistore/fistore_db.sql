-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2026 at 02:36 AM
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
-- Database: `fistore_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `author` varchar(100) NOT NULL,
  `category` varchar(60) NOT NULL,
  `price` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `cover_color` varchar(20) DEFAULT '#f7a1b5',
  `cover_image` varchar(255) DEFAULT NULL,
  `is_new` tinyint(1) DEFAULT 0,
  `is_bestseller` tinyint(1) DEFAULT 0,
  `is_trending` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `category`, `price`, `stock`, `description`, `cover_color`, `cover_image`, `is_new`, `is_bestseller`, `is_trending`, `created_at`) VALUES
(1, 'Justice', 'Michael J. Sandel', 'Filsafat', 75000, 16, 'Sebuah eksplorasi mendalam tentang keadilan, moralitas, dan bagaimana kita seharusnya hidup bersama.', '#4a4a68', 'cover_20260923130141_5736fe.jpg', 1, 0, 0, '2026-09-23 02:35:45'),
(2, 'Sapiens', 'Yuval Noah Harari', 'Sejarah', 120000, 15, 'Riwayat singkat umat manusia dari zaman batu hingga era modern.', '#e8dcc8', 'cover_20260923130222_e317f8.jpg', 1, 0, 0, '2026-09-23 02:35:45'),
(4, 'Ikigai', 'Hector Garcia', 'Pengembangan Diri', 67000, 25, 'Rahasia panjang umur dan kebahagiaan ala orang Jepang.', '#e8b04b', 'cover_20260923130257_17282d.jpg', 1, 0, 0, '2026-09-23 02:35:45'),
(5, 'Logicomix', 'Apostolos Doxiadis', 'Graphic Novel', 85000, 7, 'Novel grafis epik tentang pencarian kebenaran logika.', '#2b2b2b', 'cover_20260923130316_42512c.jpg', 1, 0, 0, '2026-09-23 02:35:45'),
(7, 'Atomic Habits', 'James Clear', 'Pengembangan Diri', 112000, 28, 'Perubahan kecil yang memberi hasil luar biasa lewat kebiasaan.', '#f4a300', 'cover_20260923130401_1aac52.jpg', 0, 1, 1, '2026-09-23 02:35:45'),
(10, 'The Little Prince', 'Antoine de Saint-Exupery', 'Fiksi', 47000, 21, 'Kisah klasik tentang persahabatan dan makna kehidupan.', '#fdfaf5', 'cover_20260923130440_8515ad.jpg', 0, 1, 0, '2026-09-23 02:35:45'),
(11, 'Elmet', 'Fiona Mozley', 'Fiksi', 65000, 9, 'Novel sastra tentang keluarga dan tanah kelahiran.', '#c0392b', 'cover_20260923130511_773af6.jpg', 0, 1, 0, '2026-09-23 02:35:45'),
(12, 'Psychology of Money', 'Morgan Housel', 'Keuangan', 65000, 26, 'Pelajaran abadi tentang kekayaan, keserakahan, dan kebahagiaan.', '#dfe6e9', 'cover_20260923130533_da61d2.jpg', 0, 1, 1, '2026-09-23 02:35:45'),
(18, 'Yellowface', 'R. F. Kuang', 'Fiksi', 99000, 13, 'Satir tajam tentang dunia penerbitan dan identitas.', '#f1c40f', 'cover_20260923130656_0b2d1c.jpg', 0, 0, 1, '2026-09-23 02:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `book_id`, `quantity`, `created_at`) VALUES
(9, 3, 5, 1, '2026-10-22 01:16:01'),
(10, 4, 1, 1, '2026-11-06 01:34:12');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `invoice_code` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Transfer Bank',
  `buyer_name` varchar(100) DEFAULT NULL,
  `buyer_address` text DEFAULT NULL,
  `status` varchar(30) DEFAULT 'Selesai',
  `payment_status` varchar(30) DEFAULT NULL,
  `payment_sender` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `payment_proof_at` timestamp NULL DEFAULT NULL,
  `payment_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `invoice_code`, `user_id`, `total_amount`, `payment_method`, `buyer_name`, `buyer_address`, `status`, `created_at`) VALUES
(1, 'FST-20260923-7F02D6', 3, 90000, 'Transfer Bank', 'fifi', 'Jalan Amal | HP: 082246375734', 'Selesai', '2026-09-23 11:08:23'),
(2, 'FST-20260923-17A7E9', 3, 90000, 'E-Wallet', 'aulia', 'Jln.tanah 80 | HP: 125667889933', 'Selesai', '2026-09-23 11:10:09'),
(3, 'FST-20260924-EE1B03', 3, 90000, 'E-Wallet', 'fifi', 'jl amal | HP: 082246375734', 'Selesai', '2026-09-24 00:14:22'),
(4, 'FST-20260924-BE2F2F', 3, 90000, 'Transfer Bank', 'fifi', 'jl amal | HP: 082246375734', 'Diproses', '2026-09-24 00:22:03'),
(5, 'FST-20261022-E5DB4B', 3, 239000, 'Transfer Bank - BCA', 'fifi', 'jln.cipinang jaya | HP: 082246375734', 'Menunggu Konfirmasi', '2026-10-22 00:35:26'),
(6, 'FST-20261022-71B325', 3, 62000, 'COD', 'fifi', 'jln.tnh 70 | HP: 082246375734', 'Menunggu Konfirmasi', '2026-10-22 00:39:03'),
(7, 'FST-20261022-0D131D', 3, 100000, 'Transfer Bank - BRI', 'fifi', 'jln.cipinang | HP: 082246375734', 'Menunggu Konfirmasi', '2026-10-22 01:00:00');

UPDATE `orders` SET `payment_status` = 'Terverifikasi' WHERE (`payment_method` LIKE 'Transfer Bank%' OR `payment_method` LIKE 'E-Wallet%') AND `status` IN ('Diproses','Selesai');
UPDATE `orders` SET `payment_status` = 'Belum Dibayar' WHERE (`payment_method` LIKE 'Transfer Bank%' OR `payment_method` LIKE 'E-Wallet%') AND `status` = 'Menunggu Konfirmasi';

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `book_id`, `title`, `price`, `quantity`) VALUES
(1, 1, 1, 'Justice', 75000, 1),
(2, 2, 1, 'Justice', 75000, 1),
(3, 3, 1, 'Justice', 75000, 1),
(4, 4, 1, 'Justice', 75000, 1),
(5, 5, 7, 'Atomic Habits', 112000, 2),
(6, 6, 10, 'The Little Prince', 47000, 1),
(7, 7, 5, 'Logicomix', 85000, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin FISTORE', 'admin@fistore.com', '$2y$10$BxiKT3aYD.k1.TW/P8UvvONMgBpabCWwAXEYP3AHow.azY3bmZ78a', 'admin', '2026-09-23 09:17:16'),
(2, 'Budi Santoso', 'user@fistore.com', '$2y$10$OFvznZSGKHsH5FOXoQYnYuRweoTr9koOtbyol2TVRRVZ65BagqlTm', 'user', '2026-09-23 09:17:16'),
(3, 'fifi', 'auliafifi938@gmail.com', '$2y$10$dx101Y5vQaU5Qwag8eJAKeoqX.nWDteskeNVfKDJvscU75US48lw.', 'user', '2026-09-23 10:13:05'),
(4, 'fifi', 'phran413@gmail.com', '$2y$10$oM8jLI6ssJM4KKuru68wJO/F36d9oMbgn5BqmKvzsinlJ.W0leiza', 'user', '2026-11-06 01:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `book_id`, `created_at`) VALUES
(1, 3, 2, '2026-09-23 11:11:22'),
(2, 3, 10, '2026-10-22 00:57:43'),
(3, 3, 1, '2026-10-22 01:15:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_code` (`invoice_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wish` (`user_id`,`book_id`),
  ADD KEY `book_id` (`book_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
