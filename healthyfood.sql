-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 06, 2026 at 09:32 PM
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
-- Database: `healthyfood`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `twofa_method` enum('none','totp','email') NOT NULL DEFAULT 'none',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `twofa_method`, `created_at`, `updated_at`, `is_verified`) VALUES
(2, '224021', '224021@eru.edu.eg', '$2y$10$nGoaQauVmcWHOlFzdgEEZ.kaqnFRxFjsBsRAUT0cQMxzlEJE9Sx06', 'admin', 'none', '2026-02-27 18:05:06', '2026-02-27 18:05:06', 1),
(3, 'test1', 'xedave6583@paylaar.com', '$2y$10$Bd.Ee0grhbpiiT21Zu.cwem5aJuU6y7zqaEF8ON8odntPaGRng6L.', 'customer', 'none', '2026-03-17 14:16:07', '2026-03-17 14:16:07', 0),
(4, 'testuser2', 'cicalopy@denipl.com', '$2y$10$sfZ4eiqZIa1WuaqyVYNhHeXCxFd8My.xNf1FSzWD6KZju9xaJBON6', 'customer', 'none', '2026-03-17 14:22:50', '2026-03-17 14:22:50', 0),
(5, 'korzegelti@necub.com', 'korzegelti@necub.com', '$2y$10$EyYD.pTAZJL3fk3wXIL.5ej/DWb76/A0DUKjqrnyqs6mZ7eO74.zi', 'customer', 'none', '2026-03-17 14:25:41', '2026-03-17 14:25:41', 0),
(6, 'wyyig29694@minitts.net', 'wyyig29694@minitts.net', '$2y$10$4NokIwkRtvluBiLkIKp8l.WlFfbFBO674xRrdVKJcN2g7ljKLvoxi', 'customer', 'none', '2026-03-17 14:34:21', '2026-03-17 14:34:21', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
