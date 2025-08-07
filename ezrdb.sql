-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2024 at 02:09 AM
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
-- Database: `ezrdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `name`) VALUES
(1, 'kvill', 'gatas123456', 'Karl');

-- --------------------------------------------------------

--
-- Table structure for table `esports_events`
--

CREATE TABLE `esports_events` (
  `id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `hover_text` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `esports_events`
--

INSERT INTO `esports_events` (`id`, `event_date`, `event_title`, `event_description`, `hover_text`, `image_url`) VALUES
(1, '2024-10-25', 'eminem day', 'eminem visits manila', 'Eminem day', '../uploads/1900x1900-000000-80-0-0.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `social_media` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `in_stock` int(255) NOT NULL,
  `out_stock` int(255) NOT NULL,
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `price`, `in_stock`, `out_stock`, `status`) VALUES
(6, 'VS Collection EZR Bomber Jacket', 1499.00, 36, 3, 'In Stock'),
(7, 'EZR Black Dri-FIT Varsity Shirt', 699.00, 68, 4, 'In Stock'),
(9, 'VS6 EZR Bomber Jacket', 1599.00, 45, 2, 'In Stock'),
(10, 'EZR swimwear bundle 3', 1500.00, 56, 1, 'In Stock'),
(11, 'Piso Gcash', 1.00, 8, 0, 'In Stock');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `order_date` date NOT NULL,
  `phonenum` varchar(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `status` enum('Pending','Paid') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `email`, `product_name`, `quantity`, `price`, `order_date`, `phonenum`, `address`, `status`) VALUES
(42, 'mictest@gmail.com', 'EZR Black Dri-FIT Varsity Shirt', 1, 699.00, '2024-10-14', '09271249790', 'F. Parcon St., Pototan, Iloilo City', 'Paid'),
(43, 'gerbyombina21@gmail.com', 'EZR swimwear bundle 3', 1, 1500.00, '2024-10-14', '09271249790', 'F. Parcon St., Pototan, Iloilo City', 'Paid'),
(44, 'gerbyombina21@gmail.com', 'VS Collection EZR Bomber Jacket', 1, 1499.00, '2024-10-15', '09271249790', 'F. Parcon St., Pototan, Iloilo City', 'Paid'),
(49, 'gerbyombina21@gmail.com', 'VS Collection EZR Bomber Jacket', 1, 1499.00, '2024-10-15', '09271249790', 'F. Parcon St., Pototan, Iloilo City', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(255) NOT NULL,
  `imagePath` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `weight` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `price`, `quantity`, `imagePath`, `description`, `weight`) VALUES
(6, 'VS Collection EZR Bomber Jacket', 1499.00, 36, 'prodimage/product1.jpg', 'The 1st edition of the VS EZR Bomber Jacket.', 700),
(7, 'EZR Black Dri-FIT Varsity Shirt', 699.00, 67, 'prodimage/product2.jpg', 'A black dri-fit shirt that is good to use for any activity.', 150),
(9, 'VS6 EZR Bomber Jacket', 1599.00, 44, 'prodimage/product4.jpg', 'The 6th Edition of the VS EZR Bomber Jacket series.', 750),
(10, 'EZR swimwear bundle 3', 1500.00, 54, 'prodimage/product6.jpg', '3rd edition swimwear bundle with rash-guard shirt, shorts, and lanyard.', 280),
(11, 'Piso Gcash', 1.00, 8, 'prodimage/piso candy.jpg', 'This should be an error since gcash minimum is 100 pesos for purchase', 10);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fname` text NOT NULL,
  `lname` text NOT NULL,
  `phonenum` varchar(11) NOT NULL,
  `email` varchar(30) NOT NULL,
  `userpass` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fname`, `lname`, `phonenum`, `email`, `userpass`) VALUES
(12, 'testing', 'tester', '09123123123', 'mictest@gmail.com', 'mictest123'),
(13, 'jykazi', 'testing', '2147483647', 'jykazi@gmail.com', 'testing123'),
(14, 'kaourbeth', 'Villabina', '2147483647', 'mountaincobra@gmail.com', 'mountaincobra123'),
(15, 'mike', 'testing', '2147483647', 'mikejordan@gmail.com', 'mike123'),
(16, 'Jybeth', 'Ombina', '2147483647', 'jj@gmail.com', 'jjo123'),
(17, 'Jybeth Jhan', 'Ombina', '09271249790', 'gerbyombina21@gmail.com', 'Froosy21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `esports_events`
--
ALTER TABLE `esports_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `esports_events`
--
ALTER TABLE `esports_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `esports_events` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
