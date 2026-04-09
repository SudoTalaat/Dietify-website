-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 09, 2026 at 09:59 PM
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
-- Table structure for table `backup_codes`
--

CREATE TABLE `backup_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 2, '2026-02-27 22:28:12', '2026-02-27 22:28:12'),
(2, 6, '2026-03-17 14:49:08', '2026-03-17 14:49:08');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_otps`
--

CREATE TABLE `email_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `purpose` enum('twofa','password_reset','email_verify') NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_otps`
--

INSERT INTO `email_otps` (`id`, `user_id`, `otp_hash`, `purpose`, `expires_at`, `used_at`, `created_at`) VALUES
(4, 2, '$2y$10$uPCD95rAbzevvOnL7VRS5O9TcU61oHZJkV14.mFCBaX5B9GRSyrgu', 'password_reset', '2026-03-04 15:33:41', '2026-03-04 15:24:31', '2026-03-04 15:23:41'),
(8, 2, '$2y$10$N4QgTYwvWVQtx4kvR71GV.Eu6crMvSbkNyybeK0.7vmic6RovYYHq', 'twofa', '2026-03-04 15:46:53', '2026-03-04 15:37:03', '2026-03-04 15:36:53'),
(9, 2, '$2y$10$LV9DaMpYdQfEdp70DnrgguNpQHDm.cdTGj/HpREl/6SFSjtTwNsA6', 'twofa', '2026-03-04 15:50:07', '2026-03-04 15:40:30', '2026-03-04 15:40:07'),
(10, 2, '$2y$10$Z1y5x0oANalXRRJMKab8au3jVYEm7kgdT1afUCG5wI4rJkqVWuMR6', 'password_reset', '2026-03-04 15:52:30', '2026-03-04 15:43:03', '2026-03-04 15:42:30'),
(11, 2, '$2y$10$sGmvQf6rZKQIHohOxpYqx.qUY1D2ycUT0e0xab8tD5zDAPbWTvdr.', 'password_reset', '2026-03-04 17:37:28', '2026-03-04 17:28:05', '2026-03-04 17:27:28'),
(12, 2, '$2y$10$lioz.0Decdofj2itWuKZSOQYsAGnHJNu3DLaG04CHQqBe2/si1qbO', 'twofa', '2026-03-04 17:38:34', '2026-03-04 17:28:46', '2026-03-04 17:28:34'),
(13, 4, '$2y$10$J3pr15tTnn1hcwyCM7Zrz.WE3X.sAqwtYNKs5oeHktiYesEFg4PKy', 'password_reset', '2026-03-17 14:34:43', NULL, '2026-03-17 14:24:43');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `role`, `message`, `created_at`) VALUES
(1, '2', 'user', 'how can i cut my kcals', '2026-04-09 17:38:25'),
(2, '2', 'assistant', 'Great goal! 🥗 Cutting calories doesn\'t have to mean cutting flavor. Try these tips:\n- Eat more protein-rich foods like eggs, chicken, and legumes.\n- Focus on whole grains like brown rice and whole wheat bread.\n- Include lots of veggies like leafy greens, broccoli, and bell peppers.\n- Drink plenty of water to stay hydrated and reduce cravings.\n\nLet\'s start with a healthy breakfast idea: Scrambled eggs with spinach and whole wheat toast. How does that sound?', '2026-04-09 17:38:26'),
(3, '2', 'user', 'is pizza bad for wight loss ?', '2026-04-09 17:39:12'),
(4, '2', 'assistant', 'Pizza can be a calorie bomb! 🍕 Especially if loaded with cheese, meat, and sugar. A slice of pizza can range from 200-500 calories. If you\'re trying to lose weight, it\'s best to limit or avoid pizza.\n\nInstead, try making a healthier homemade pizza with a whole wheat crust, veggies like bell peppers, and lean protein like chicken. This way, you can control the ingredients and keep it balanced.', '2026-04-09 17:39:12'),
(5, '2', 'user', 'can we talk about sex ?', '2026-04-09 17:56:32'),
(6, '2', 'assistant', 'Please consult a doctor for medical advice. I can only help with general food suggestions! 🩺', '2026-04-09 17:56:32'),
(7, '2', 'user', 'why not', '2026-04-09 17:56:41'),
(8, '2', 'assistant', 'I can provide general advice on nutrition and healthy eating, but when it comes to personal topics like sex, I\'m not the right assistant for that. If you\'re looking for information on health or wellness, I\'d be happy to help with topics like nutrition, exercise, or stress management. How about we focus on a healthy recipe idea instead? 🥗', '2026-04-09 17:56:41'),
(9, '2', 'user', 'how much kcal in tuna', '2026-04-09 19:41:10'),
(10, '2', 'user', 'how much kcal in tuna', '2026-04-09 19:41:37'),
(11, '2', 'user', 'how much kcal in tuna', '2026-04-09 19:42:13'),
(12, '2', 'user', 'how much kcal in tuna', '2026-04-09 19:48:33'),
(13, '2', 'user', 'how much kcal in tuna', '2026-04-09 19:48:50'),
(14, '2', 'assistant', 'A can of tuna (drained and flaked) typically has around 150-200 kcal.', '2026-04-09 19:48:51'),
(15, '2', 'user', 'how much kcal in tuna', '2026-04-09 19:49:50'),
(16, '2', 'user', 'how many kcals i need', '2026-04-09 19:51:18'),
(17, '2', 'assistant', 'A person\'s daily energy needs depend on their age, sex, weight, and activity level. Here\'s a rough estimate:\n\n- Sedentary (little or no exercise): 1,600-2,000 kcal/day\n- Lightly active (light exercise/sports 1-3 days/week): 1,900-2,400 kcal/day\n- Moderately active (moderate exercise/sports 3-5 days/week): 2,000-2,800 kcal/day\n- Very active (hard exercise/sports 6-7 days a week): 2,400-3,000 kcal/day\n- Extremely active (very hard exercise/sports & physical job or 2x training): 3,000-4,000 kcal/day\n\nPlease note: This is a general guideline. Consult a doctor or a registered dietitian for your specific needs.', '2026-04-09 19:51:19'),
(18, '2', 'user', 'how mant kcal my kid will need', '2026-04-09 19:56:00'),
(19, '2', 'user', 'how mant kcal my kid will need', '2026-04-09 19:56:17'),
(20, '2', 'user', 'how mant kcal my kid will need', '2026-04-09 19:57:04'),
(21, '2', 'assistant', 'Kid\'s energy needs depend on age and activity level. Here\'s a rough estimate:\n\n- Infants (1-2 years): 1,000-1,400 kcal/day\n- Toddlers (2-3 years): 1,200-1,800 kcal/day\n- Preschoolers (4-5 years): 1,400-2,000 kcal/day\n- School-age kids (6-12 years): 1,600-2,200 kcal/day\n\nFor a specific age, please let me know!', '2026-04-09 19:57:05'),
(22, '2', 'user', 'HOW MANY TIME i shopuld workout', '2026-04-09 19:57:35'),
(23, '2', 'assistant', 'The American Heart Association recommends at least 150 minutes of moderate-intensity aerobic activity or 75 minutes of vigorous-intensity aerobic activity per week. You can break it down into:\n\n- 30 minutes, 5 days a week (moderate intensity)\n- 25 minutes, 3 days a week (vigorous intensity)\n\nAlso, aim for 2-3 strength training sessions per week to build muscle and support overall health. 🏋️‍♀️', '2026-04-09 19:57:40');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `location_description` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `location_description`, `phone`, `created_at`) VALUES
(1, 2, 30.00, 'paid', 'No Address Provided', '0000000000', '2026-02-28 09:24:56'),
(2, 2, 60.00, 'cancelled', 'No Address Provided', '0000000000', '2026-02-28 10:01:24'),
(12, 2, 210.00, 'paid', 'No Address Provided', '0000000000', '2026-03-04 17:08:58'),
(13, 2, 300.00, 'paid', 'No Address Provided', '0000000000', '2026-03-04 17:32:29'),
(16, 2, 200.00, 'paid', 'No Address Provided', '0000000000', '2026-03-06 09:02:40'),
(17, 2, 200.00, 'paid', 'No Address Provided', '0000000000', '2026-03-06 09:07:02'),
(18, 2, 200.00, 'paid', 'No Address Provided', '0000000000', '2026-03-07 20:18:14'),
(20, 2, 200.00, 'cancelled', 'No Address Provided', '0000000000', '2026-03-10 11:25:12');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`) VALUES
(1, 1, 1, 'salad ', 30.00, 1),
(2, 2, 1, 'salad ', 30.00, 2),
(12, 12, 1, 'salad ', 30.00, 7),
(13, 13, 1, 'salad ', 30.00, 10),
(16, 16, 3, 'Crazy Chicken Sandwich', 200.00, 1),
(17, 17, 3, 'Crazy Chicken Sandwich', 200.00, 1),
(18, 18, 3, 'Crazy Chicken Sandwich', 200.00, 1),
(20, 20, 3, 'Crazy Chicken Sandwich', 200.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `method` enum('stripe','cod') NOT NULL,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(150) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `method`, `status`, `transaction_id`, `paid_at`, `created_at`) VALUES
(1, 1, 'stripe', 'completed', 'cs_test_a1KRsOJ4oV1dMapSo026HH5YCMpBa3Qb8lDzkpWkiKODc3XrjqryD9WlIJ', '2026-02-28 09:24:56', '2026-02-28 09:24:56'),
(2, 2, 'stripe', 'failed', 'cs_test_a1hsmqNdGF6KFra1cZpn18yGT6bRZ3iO2XedwHIzPqCHqkl3iLqXmh4U4C', NULL, '2026-02-28 10:03:01'),
(3, 12, 'stripe', 'completed', 'cs_test_a1GB314UOXzo3VvFUrnNykyaKP6pmezyQkj59hkvyU6u9oye1jpmcr5w5S', '2026-03-04 17:09:23', '2026-03-04 17:09:23'),
(4, 13, 'stripe', 'completed', 'cs_test_a1KQk7cwK2sAaLHtqig5YAB0aSovLSPkvUSD8VZQ3kJBTzMjru26X16XG7', '2026-03-04 17:32:57', '2026-03-04 17:32:57'),
(5, 16, 'stripe', 'completed', 'cs_test_a19ZrtPKIQujE6BAfo8bt8wlscLA0s0olNdk0ItHGT0XPDW1kpEaoiTEJE', '2026-03-06 09:04:22', '2026-03-06 09:04:22'),
(6, 17, 'stripe', 'completed', 'cs_test_a12OEn8IbHlTM8J7NxLiTbZQ2L2nIM4WmsEFnguStIAj2GEhlXrtAm74EN', '2026-03-06 09:07:26', '2026-03-06 09:07:26'),
(7, 18, 'stripe', 'completed', 'cs_test_a1gJLlly2xP247NCU1ALSd4WR46Z8sM9aXMIB6mcVvNsvn85mHMFQkmjJm', '2026-03-07 20:18:49', '2026-03-07 20:18:49');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `type` enum('food','drink') NOT NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image_path`, `type`, `status`, `created_at`, `updated_at`) VALUES
(1, 'salad ', 'salad low kcal', 35.00, 55, 'assets/images/841d05fa5ee60b73637cccdc1373760a.jpg', 'food', 'active', '2026-02-27 22:25:49', '2026-02-27 22:25:49'),
(3, 'Crazy Chicken Sandwich', '4 pieces, cream cheese, chicken, jalapeno, crushed bread, tortilla, eggs and black. Calories 316, fat 6, protein 40, carbs 22.4', 200.00, 97, 'assets/images/1772645704_e5045101d028d974.webp', 'food', 'active', '2026-03-04 17:35:04', '2026-03-04 17:35:04');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`) VALUES
(1, 2, 1, 5, 'good food', '2026-03-04 17:32:10'),
(2, 2, 3, 5, 'very good meal i love it is batter then kfc', '2026-03-17 13:29:38');

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

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `location_description` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `is_default` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_totp`
--

CREATE TABLE `user_totp` (
  `user_id` int(11) NOT NULL,
  `totp_secret` varchar(64) NOT NULL,
  `confirmed_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `backup_codes`
--
ALTER TABLE `backup_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `email_otps`
--
ALTER TABLE `email_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_totp`
--
ALTER TABLE `user_totp`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `backup_codes`
--
ALTER TABLE `backup_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `email_otps`
--
ALTER TABLE `email_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `backup_codes`
--
ALTER TABLE `backup_codes`
  ADD CONSTRAINT `backup_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `email_otps`
--
ALTER TABLE `email_otps`
  ADD CONSTRAINT `email_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_totp`
--
ALTER TABLE `user_totp`
  ADD CONSTRAINT `user_totp_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
