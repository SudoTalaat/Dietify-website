-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2026 at 10:49 PM
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
(13, 4, '$2y$10$J3pr15tTnn1hcwyCM7Zrz.WE3X.sAqwtYNKs5oeHktiYesEFg4PKy', 'password_reset', '2026-03-17 14:34:43', NULL, '2026-03-17 14:24:43'),
(15, 2, '$2y$10$C3UH4ufa/XSmjEY2NwnAEOdiKW.PHdnRwgKQ3hfm2Bl7U9p6Eyt3e', 'twofa', '2026-04-15 05:13:24', NULL, '2026-04-15 05:03:24');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `message`, `created_at`) VALUES
(117, '2', 'sup man', '2026-04-16 20:54:08'),
(118, '2', 'What\'s up? 😊 You\'re looking to eat healthier in general, right? I\'d be happy to help you with some balanced meal ideas. How about some grilled chicken with roasted veggies and quinoa? Or a hearty bowl of lentil soup with whole grain bread? Which one sounds good to you?', '2026-04-16 20:54:09'),
(119, '2', 'make dite plan for me ', '2026-04-16 20:54:39'),
(120, '2', 'You want a structured meal plan, huh? Before I can do that, I need to know how many days you\'d like the plan to cover. Do you want a 3-day meal plan or a 7-day meal plan?\n\nAlso, let me know if you have any dietary restrictions or preferences (e.g. vegetarian, gluten-free, etc.) that I should keep in mind while creating the plan.', '2026-04-16 20:54:39'),
(121, '2', '3-day', '2026-04-16 20:54:50'),
(122, '2', 'I\'ll create a 3-day structured meal plan for you. Before I begin, I just need to confirm a few details.\n\nSince you didn\'t mention any dietary restrictions, I\'ll assume you\'re looking for a general meal plan. If you\'re vegetarian, gluten-free, or have any other specific needs, please let me know.\n\nAlso, would you like your meals to be relatively light and easy to prepare, or can you spend a bit more time in the kitchen cooking?\n\n### Meal Plan Options:\n\nA) Quick & Easy\nB) Cooking Time Doesn\'t Matter\n\nPlease respond with the letter of your preferred meal plan option!', '2026-04-16 20:54:50'),
(123, '2', '3-day', '2026-04-16 20:54:53'),
(124, '2', 'You didn\'t answer the question. Please respond with \"A)\" for Quick & Easy or \"B)\" for Cooking Time Doesn\'t Matter.', '2026-04-16 20:54:53'),
(125, '2', 'A', '2026-04-16 20:55:12'),
(126, '2', 'You\'ve chosen the Quick & Easy option.\n\nHere\'s a 3-day structured meal plan for you:\n\n**Day 1**\n\n* Breakfast: Overnight oats with banana and almond milk 🥗\n	+ 1/2 cup rolled oats, 1/2 cup almond milk, 1 mashed banana, 1 tablespoon honey\n* Lunch: Grilled chicken breast with mixed greens salad 🥦\n	+ 4 oz grilled chicken, 2 cups mixed greens, 1 cup cherry tomatoes, 1 tablespoon vinaigrette\n* Dinner: One-pot pasta with marinara sauce and vegetables 🍝\n	+ 1 cup pasta, 1 cup marinara sauce, 1 cup mixed vegetables (bell peppers, onions, mushrooms)\n\n**Day 2**\n\n* Breakfast: Scrambled eggs with whole wheat toast and avocado 🥚\n	+ 2 scrambled eggs, 1 slice whole wheat toast, 1/2 avocado\n* Lunch: Turkey and cheese sandwich on whole wheat bread with carrot sticks 🥗\n	+ 2 slices whole wheat bread, 2 oz turkey breast, 1 oz cheese, 1 cup carrot sticks\n* Dinner: Baked salmon with roasted broccoli and quinoa 🍗', '2026-04-16 20:55:13'),
(127, '2', '3-day', '2026-04-16 20:55:16'),
(128, '2', ' Meal Plan (Continued)\n\n**Day 3**\n\n* Breakfast: Yogurt parfait with granola and mixed berries 🍓\n	+ 6 oz yogurt, 2 tablespoons granola, 1 cup mixed berries\n* Lunch: Chicken Caesar salad 🥗\n	+ 4 oz grilled chicken, 2 cups romaine lettuce, 1 cup croutons, 1 tablespoon Caesar dressing\n* Dinner: Black bean and corn tacos with whole wheat tortillas 🌯\n	+ 1 cup cooked black beans, 1 cup frozen corn, 2 whole wheat tortillas, 1 cup shredded cheese\n\n### Nutrition Tip\nRemember to stay hydrated by drinking plenty of water throughout the day. Aim for at least 8 cups (64 oz) of water daily.\n\nPlease let me know if you have any questions or if you\'d like to make any changes to this meal plan!', '2026-04-16 20:55:17'),
(129, '2', 'what is your name', '2026-04-16 20:58:41'),
(130, '2', '?', '2026-04-16 20:58:41'),
(131, '2', 'you namme ?', '2026-04-16 20:58:47'),
(132, '2', ' I\'m Mr. Hamboula, your personalized food guru! I\'m here to help you with all your culinary needs and make healthy eating a breeze.', '2026-04-16 20:58:47'),
(133, '2', 'your name', '2026-04-20 19:40:17'),
(134, '2', ' is not Mr. Hamboula', '2026-04-20 19:40:18'),
(135, '2', 'did i ask you about your name ?', '2026-04-20 19:40:47'),
(136, '2', 'did i ask you about your name ?', '2026-04-20 19:40:50'),
(137, '2', 'name ?', '2026-04-20 19:40:54'),
(138, '2', 'hi', '2026-04-20 19:40:57'),
(139, '2', ', i\'m your friendly Healthy Food Assistant! 👋', '2026-04-20 19:40:57'),
(140, '2', 'name ?', '2026-04-20 19:41:00'),
(141, '2', 'name ?', '2026-04-20 19:41:03'),
(142, '2', ' it\'s a pleasure to meet you, but I don\'t have a personal name. I\'m here to help you with healthy food suggestions!', '2026-04-20 19:41:04'),
(143, '2', 'did i ask you about your name ?', '2026-04-20 19:41:06'),
(144, '2', 'did i ask you about your name ?', '2026-04-20 19:41:07'),
(145, '2', 'name', '2026-04-20 19:41:26'),
(146, '2', ' is still \'Healthy Food Assistant\'. I\'m here to help!', '2026-04-20 19:41:26');

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
(20, 2, 200.00, 'cancelled', 'No Address Provided', '0000000000', '2026-03-10 11:25:12'),
(21, 2, 340.00, 'cancelled', 'No Address Provided', '0000000000', '2026-04-16 18:51:20');

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
(20, 20, 3, 'Crazy Chicken Sandwich', 200.00, 1),
(21, 21, 5, 'Tikka Masala Chicken', 340.00, 1);

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
(3, 'Crazy Chicken Sandwich', '4 pieces, cream cheese, chicken, jalapeno, crushed bread, tortilla, eggs and black. Calories 316, fat 6, protein 40, carbs 22.4', 200.00, 97, 'assets/images/1772645704_e5045101d028d974.webp', 'food', 'active', '2026-03-04 17:35:04', '2026-03-04 17:35:04'),
(5, 'Tikka Masala Chicken', 'Grilled chicken breasts with Healthy tikka masala sauce. Served With White Rice & Veggies Kcals : 575 protein 62 carb 60. Fat 10', 340.00, 10, 'assets/images/1776364889_2f0e6c94a2c46e82.webp', 'food', 'active', '2026-04-16 18:41:29', '2026-04-16 18:41:29'),
(6, 'Chicken Curry sauce', 'Chicken with Curry Sauce Calories 167, Fat 4, Protein 25, Carb 7 -100 g', 225.00, 55, 'assets/images/1776365214_75275a266146e67c.webp', 'food', 'active', '2026-04-16 18:46:54', '2026-04-16 18:46:54'),
(7, 'Chicken with Buffalo sauce Burger', 'Breaded Chicken, Buffalo Sauce, Pickled and Cheese Calories 429, Protein 49.5, Fat 19.9 and Carb 13.7', 150.00, 69, 'assets/images/1776368179_787ab29e9e7497da.webp', 'food', 'active', '2026-04-16 19:36:19', '2026-04-16 19:36:19'),
(8, 'Banana walnut power shake', 'Calories: 625 kcal\r\nProtein: 29 g\r\nCarbohydrates: 63 g\r\nSugars: 37 g\r\n Fibers: 7g \r\nFat: 31 g', 100.00, 33, 'assets/images/1776714402_5102915060da6432.webp', 'drink', 'active', '2026-04-20 19:46:42', '2026-04-20 19:46:42'),
(9, 'Banana with Milk', '327 cal, with non dairy almond milk.', 85.00, 222, 'assets/images/1776717664_5a44a2d1361b5609.webp', 'drink', 'active', '2026-04-20 20:41:04', '2026-04-20 20:41:04');

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
  `is_verified` tinyint(1) DEFAULT 0,
  `avatar` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `twofa_method`, `created_at`, `updated_at`, `is_verified`, `avatar`, `age`, `weight`, `height`, `gender`, `phone`) VALUES
(2, 'talaat', '224021@eru.edu.eg', '$2y$10$fthxAMPPg4kb9dJ3K6cgnueHdKc2wzET2x62kVSmQOK4iylfEDMvK', 'admin', 'none', '2026-02-27 18:05:06', '2026-02-27 18:05:06', 1, 'assets/images/avatar_2_1776340436.jpg', 21, 90.00, 170.00, 'male', '0106634324234'),
(4, 'testuser2', 'cicalopy@denipl.com', '$2y$10$sfZ4eiqZIa1WuaqyVYNhHeXCxFd8My.xNf1FSzWD6KZju9xaJBON6', 'customer', 'none', '2026-03-17 14:22:50', '2026-03-17 14:22:50', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'korzegelti@necub.com', 'korzegelti@necub.com', '$2y$10$EyYD.pTAZJL3fk3wXIL.5ej/DWb76/A0DUKjqrnyqs6mZ7eO74.zi', 'customer', 'none', '2026-03-17 14:25:41', '2026-03-17 14:25:41', 0, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'wyyig29694@minitts.net', 'wyyig29694@minitts.net', '$2y$10$4NokIwkRtvluBiLkIKp8l.WlFfbFBO674xRrdVKJcN2g7ljKLvoxi', 'customer', 'none', '2026-03-17 14:34:21', '2026-03-17 14:34:21', 1, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'abdo', 'abodymody65@gmail.com', '$2y$10$zGd2z8imPaKbuYV0mPKJ1eXcFnbc4xGOycs69RVhPJajJONJEnV9C', 'customer', 'none', '2026-04-12 09:29:56', '2026-04-12 09:29:56', 1, 'assets/images/default_avatar.png', 27, 60.00, 185.00, 'male', '01056988987');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `email_otps`
--
ALTER TABLE `email_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
