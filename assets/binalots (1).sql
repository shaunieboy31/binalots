-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 03:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `binalots`
--

-- --------------------------------------------------------

--
-- Table structure for table `archived_orders`
--

CREATE TABLE `archived_orders` (
  `order_id` int(11) NOT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `operator` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `payment_method` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archived_orders`
--

INSERT INTO `archived_orders` (`order_id`, `receipt_no`, `operator`, `created_at`, `payment_method`) VALUES
(1, '20250613060840', 'Developer', '2025-06-09 12:45:55', 'Cash');

-- --------------------------------------------------------

--
-- Table structure for table `archived_order_details`
--

CREATE TABLE `archived_order_details` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `item` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archived_order_details`
--

INSERT INTO `archived_order_details` (`detail_id`, `order_id`, `item`, `quantity`, `price`) VALUES
(1, 1, 'Tapsilog', 3, 300.00),
(2, 1, 'Tosilog', 2, 180.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `operator` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `payment_method` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `receipt_no`, `operator`, `created_at`, `payment_method`) VALUES
(2, '20250613060914', 'Developer', '2025-06-13 06:09:14', 'Cash'),
(3, '20250613061420', 'Developer', '2025-06-13 06:14:20', 'Cash'),
(4, '20250613064244', 'Developer', '2025-06-13 06:42:44', 'Cash'),
(5, '20250613123834', 'Developer', '2025-06-13 12:38:34', 'Cash'),
(6, '20250613135654', 'Developer', '2025-06-13 13:56:54', 'Cash'),
(7, '20250613141616', 'Developer', '2025-06-13 14:16:16', 'Cash'),
(8, '20250613144810', 'Developer', '2025-06-13 14:48:10', 'Cash');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `item` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`detail_id`, `order_id`, `item`, `quantity`, `price`) VALUES
(3, 2, 'Tosilog', 4, 360.00),
(4, 3, 'Tapsilog', 2, 200.00),
(5, 3, 'Longsilog', 2, 160.00),
(6, 4, 'Tosilog', 26, 2340.00),
(7, 5, 'Egg', 2, 40.00),
(8, 6, 'Balot', 5, 75.00),
(9, 7, 'Itlog na pula', 1, 13.00),
(10, 7, 'Chicken Hotdog', 1, 12.00),
(11, 7, 'Pork Chop', 1, 70.00),
(12, 7, 'Balot', 1, 15.00),
(13, 7, 'Sinigang na baboy', 1, 50.00),
(14, 8, 'ikaw', 1, 13.00),
(15, 8, 'ako', 1, 124.00),
(16, 8, 'saan', 1, 70.00),
(17, 8, 'bago', 1, 50.00),
(18, 8, 'promo', 1, 15.00),
(19, 8, 'wag', 1, 50.00),
(20, 8, 'Shaun', 1, 5000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `category`, `price`, `description`) VALUES
(1, 'Tapsilog', 'silog', 100.00, 'Classic beef tapa with egg and rice'),
(2, 'Tosilog', 'silog', 90.00, 'Tocino with egg and rice'),
(3, 'Longsilog', 'silog', 80.00, 'Longganisa with egg and rice'),
(4, 'Bangsilog', 'silog', 110.00, 'Bangus with egg and rice'),
(5, 'Family Feast 1', 'family', 500.00, 'Good for 4-5 persons'),
(6, 'Family Feast 2', 'family', 600.00, 'Good for 6-7 persons'),
(7, 'Family Feast 3', 'family', 550.00, 'Good for 5-6 persons'),
(8, 'Family Feast 4', 'family', 650.00, 'Good for 7-8 persons'),
(9, 'Sizzling Pork', 'sizzling', 150.00, 'Sizzling pork plate'),
(10, 'Sizzling Beef', 'sizzling', 200.00, 'Sizzling beef plate'),
(11, 'Sizzling Chicken', 'sizzling', 180.00, 'Sizzling chicken plate'),
(12, 'Sizzling Fish', 'sizzling', 160.00, 'Sizzling fish plate'),
(13, 'Iced Tea', 'beverages', 40.00, 'Refreshing iced tea'),
(14, 'Soft Drink', 'beverages', 30.00, 'Assorted soft drinks'),
(15, 'Fruit Juice', 'beverages', 50.00, 'Assorted fruit juices'),
(16, 'Water', 'beverages', 20.00, 'Bottled water'),
(18, 'Rice', 'addons', 10.00, 'Extra rice'),
(22, 'Shaun', 'beverages', 20.00, '152131');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `otp` varchar(10) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `otp`, `is_verified`, `created_at`) VALUES
(5, 'Developer', 'shaunieboy573@gmail.com', '$2y$10$Gk.nmwu664wvCQNZtHAYLu/aD6U8IeyaDIhQscmKqsaUwC/LWQPuO', NULL, 1, '2025-06-13 04:03:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archived_orders`
--
ALTER TABLE `archived_orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `archived_order_details`
--
ALTER TABLE `archived_order_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archived_orders`
--
ALTER TABLE `archived_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `archived_order_details`
--
ALTER TABLE `archived_order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
