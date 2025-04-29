-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 07, 2025 at 11:24 AM
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
-- Database: `wedding_planning`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `full_name`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$qmuHvnh4QJ9VPiEpeDLfUOd41FPEzfsVbq3vKPllL3uHGwBdTsBLm', 'admin@wedding.com', 'System Administrator', 1, '2025-04-07 14:52:46', '2025-04-06 23:56:58', '2025-04-07 09:22:46');

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `service_id` int(11) DEFAULT NULL,
  `booking_date` date NOT NULL,
  `event_date` date NOT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `service_ids` text DEFAULT NULL,
  `service_quantities` text DEFAULT NULL,
  `service_prices` text DEFAULT NULL,
  `special_requests` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `service_id`, `booking_date`, `event_date`, `guest_count`, `status`, `total_amount`, `created_at`, `service_ids`, `service_quantities`, `service_prices`, `special_requests`) VALUES
(1, 2, 1, '2025-04-07', '2025-04-20', 225, 'confirmed', 5000.00, '2025-04-07 00:13:45', '1', '1', '5000.00', ''),
(2, 2, 1, '2025-04-07', '2025-04-18', 123, 'cancelled', 5000.00, '2025-04-07 05:13:17', '1', '1', '5000.00', ''),
(3, 2, 1, '2025-04-07', '2025-04-17', 21, 'cancelled', 5000.00, '2025-04-07 05:25:03', '1', '1', '5000.00', ''),
(4, 2, 1, '2025-04-07', '2025-04-24', 23, 'confirmed', 500.00, '2025-04-07 06:49:54', '1', '1', '500.00', ''),
(5, 2, 1, '2025-04-07', '2025-04-18', 123, 'confirmed', 500.00, '2025-04-07 07:23:33', '1', '1', '500.00', ''),
(6, 2, 1, '2025-04-07', '2025-04-19', 47, 'confirmed', 500.00, '2025-04-07 07:27:03', '1', '1', '500.00', ''),
(7, 2, 1, '2025-04-07', '2025-04-24', 123, 'confirmed', 500.00, '2025-04-07 07:36:10', '1', '1', '500.00', ''),
(8, 2, 1, '2025-04-07', '2025-04-21', 25, 'confirmed', 500.00, '2025-04-07 07:51:18', '1', '1', '500.00', ''),
(9, 2, 1, '2025-04-07', '2025-04-23', 111, 'confirmed', 500.00, '2025-04-07 08:49:07', '1', '1', '500.00', ''),
(10, 2, 1, '2025-04-07', '2025-04-22', 222, 'confirmed', 500.00, '2025-04-07 08:51:11', '1', '1', '500.00', ''),
(11, 2, 1, '2025-04-07', '2025-04-27', 123, 'confirmed', 500.00, '2025-04-07 09:06:30', '1', '1', '500.00', ''),
(12, 2, 1, '2025-04-07', '2025-04-29', 122, 'confirmed', 500.00, '2025-04-07 09:14:29', '1', '1', '500.00', '');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `special_requests` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `image`, `description`, `category`, `status`, `created_at`) VALUES
(1, 'Wedding', 'uploads/gallery/67f319943b48a.png', '', 'Wedding', 'active', '2025-04-07 00:17:24');

-- --------------------------------------------------------

--
-- Table structure for table `guest_lists`
--

CREATE TABLE `guest_lists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('invited','confirmed','declined') DEFAULT 'invited'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `card_last_four` varchar(4) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `paypal_order_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount`, `payment_date`, `payment_method`, `card_last_four`, `status`, `transaction_id`, `paypal_order_id`) VALUES
(1, 1, 5000.00, '2025-04-07 00:14:48', 'credit_card', '1234', 'completed', 'TXN_67f318f8e898eNIDA7E', NULL),
(2, 4, 500.00, '2025-04-07 02:35:15', 'paypal', NULL, 'completed', '78J50825X7857825T', '3EC93945PF675135H'),
(3, 5, 500.00, '2025-04-07 03:56:42', 'paypal', NULL, 'completed', '78J50825X7857825T', '90C36602XM692401V'),
(4, 6, 500.00, '2025-04-07 04:03:55', 'paypal', NULL, 'completed', '78J50825X7857825T', '4BN85743UU651684S'),
(5, 7, 500.00, '2025-04-07 04:18:28', 'paypal', NULL, 'completed', '78J50825X7857825T', '0H8747496X743234R'),
(6, 8, 500.00, '2025-04-07 07:52:48', 'paypal', NULL, 'completed', 'FP17440123682702', '2K067581W36212605'),
(7, 9, 500.00, '2025-04-07 08:50:40', 'paypal', NULL, 'completed', 'FP17440158401176', '2VE887022X574424Y'),
(8, 10, 500.00, '2025-04-07 08:52:20', 'paypal', NULL, 'completed', 'FP17440159402134', '0UJ05101JT0977427'),
(9, 11, 500.00, '2025-04-07 09:08:02', 'paypal', NULL, 'completed', 'FP17440168826370', '59J137556S908002W'),
(10, 11, 500.00, '2025-04-07 09:13:47', 'paypal', NULL, 'completed', 'FP17440172277718', '7VL79336E1804390P'),
(11, 12, 500.00, '2025-04-07 09:15:16', 'paypal', NULL, 'completed', 'FP17440173161118', '9RT49232814220733');

-- --------------------------------------------------------

--
-- Table structure for table `payment_errors`
--

CREATE TABLE `payment_errors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `error_message` text DEFAULT NULL,
  `booking_ids` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_errors`
--

INSERT INTO `payment_errors` (`id`, `user_id`, `order_id`, `error_message`, `booking_ids`, `created_at`) VALUES
(1, 2, '18068823KV811645B', 'PayPal Verification Error: Failed to capture PayPal payment: HTTP 201', '[7]', '2025-04-07 13:10:01'),
(2, 2, '18068823KV811645B', 'Payment Error Logged', '[7]', '2025-04-07 13:10:01'),
(3, 2, '18068823KV811645B', 'There was an error confirming your payment. Please contact support with reference ID: 18068823KV811645B', '[7]', '2025-04-07 13:10:01'),
(4, 2, '9YY86139UC904632S', 'PayPal Verification Error: Failed to capture PayPal payment: HTTP 201', '[7]', '2025-04-07 13:12:53'),
(5, 2, '9YY86139UC904632S', 'Payment Error Logged', '[7]', '2025-04-07 13:12:53'),
(6, 2, '9YY86139UC904632S', 'There was an error confirming your payment. Please contact support with reference ID: 9YY86139UC904632S', '[7]', '2025-04-07 13:12:53'),
(7, 2, '0H8747496X743234R', 'PayPal Verification Error: Failed to capture PayPal payment: HTTP 201', '[7]', '2025-04-07 13:17:18'),
(8, 2, '0H8747496X743234R', 'Payment Error Logged', '[7]', '2025-04-07 13:17:18'),
(9, 2, '0H8747496X743234R', 'There was an error confirming your payment. Please contact support with reference ID: 0H8747496X743234R', '[7]', '2025-04-07 13:17:18'),
(10, 2, '2K067581W36212605', 'PayPal Verification Error: Failed to capture PayPal payment: Connection timed out after 30004 milliseconds', '[8]', '2025-04-07 13:22:48'),
(11, 2, '2K067581W36212605', 'Payment forced to success', '[8]', '2025-04-07 13:22:48'),
(12, 2, '2VE887022X574424Y', 'PayPal Verification Error: Failed to capture PayPal payment: HTTP 201', '[9]', '2025-04-07 14:20:39'),
(13, 2, '2VE887022X574424Y', 'Payment forced to success', '[9]', '2025-04-07 14:20:40'),
(14, 2, '0UJ05101JT0977427', 'PayPal Verification Error: Failed to capture PayPal payment: HTTP 201', '[10]', '2025-04-07 14:22:20'),
(15, 2, '0UJ05101JT0977427', 'Payment forced to success', '[10]', '2025-04-07 14:22:20'),
(16, 2, '59J137556S908002W', 'PayPal Verification Error: Failed to capture PayPal payment: HTTP 201', '[11]', '2025-04-07 14:38:02'),
(17, 2, '59J137556S908002W', 'Payment forced to success', '[11]', '2025-04-07 14:38:02'),
(18, 2, '7VL79336E1804390P', 'PayPal Verification Error: Failed to capture PayPal payment: HTTP 201', '[11]', '2025-04-07 14:43:47'),
(19, 2, '7VL79336E1804390P', 'Payment forced to success', '[11]', '2025-04-07 14:43:47'),
(20, 2, '9RT49232814220733', 'PayPal Verification Error: Failed to capture PayPal payment: HTTP 201', '[12]', '2025-04-07 14:45:16'),
(21, 2, '9RT49232814220733', 'Payment forced to success', '[12]', '2025-04-07 14:45:16');

-- --------------------------------------------------------

--
-- Table structure for table `paypal_processing`
--

CREATE TABLE `paypal_processing` (
  `id` int(11) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `booking_ids` text DEFAULT NULL,
  `status` enum('processing','completed','failed') DEFAULT 'processing',
  `attempts` int(11) DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `paypal_processing`
--

INSERT INTO `paypal_processing` (`id`, `order_id`, `user_id`, `booking_ids`, `status`, `attempts`, `last_attempt`, `created_at`, `completed_at`, `transaction_id`, `error_message`) VALUES
(1, '9YY86139UC904632S', 2, '[7]', 'failed', 1, '2025-04-07 07:42:38', '2025-04-07 07:42:38', NULL, NULL, 'Failed to capture PayPal payment: HTTP 201'),
(2, '0H8747496X743234R', 2, '[7]', 'failed', 1, '2025-04-07 07:47:06', '2025-04-07 07:47:06', NULL, NULL, 'Failed to capture PayPal payment: HTTP 201'),
(3, '2K067581W36212605', 2, '[8]', 'completed', 1, '2025-04-07 07:51:34', '2025-04-07 07:51:34', '2025-04-07 07:52:48', 'FP17440123682702', NULL),
(4, '2VE887022X574424Y', 2, '[9]', 'completed', 1, '2025-04-07 08:50:17', '2025-04-07 08:50:17', '2025-04-07 08:50:40', 'FP17440158401176', NULL),
(5, '0UJ05101JT0977427', 2, '[10]', 'completed', 1, '2025-04-07 08:51:48', '2025-04-07 08:51:48', '2025-04-07 08:52:20', 'FP17440159402134', NULL),
(6, '59J137556S908002W', 2, '[11]', 'completed', 1, '2025-04-07 09:07:28', '2025-04-07 09:07:28', '2025-04-07 09:08:02', 'FP17440168826370', NULL),
(7, '7VL79336E1804390P', 2, '[11]', 'completed', 1, '2025-04-07 09:13:33', '2025-04-07 09:13:33', '2025-04-07 09:13:47', 'FP17440172277718', NULL),
(8, '9RT49232814220733', 2, '[12]', 'completed', 1, '2025-04-07 09:14:46', '2025-04-07 09:14:46', '2025-04-07 09:15:16', 'FP17440173161118', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `planner_assignments`
--

CREATE TABLE `planner_assignments` (
  `id` int(11) NOT NULL,
  `planner_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_cards`
--

CREATE TABLE `saved_cards` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_holder` varchar(100) NOT NULL,
  `card_last_four` varchar(4) NOT NULL,
  `expiry_month` varchar(2) NOT NULL,
  `expiry_year` varchar(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `category_id`, `name`, `description`, `price`, `image`, `status`) VALUES
(1, 1, 'sarees', '', 500.00, 'uploads/services/67f36ea4290bb.png', 'active'),
(2, 2, 'Electric Band', '', 600.00, 'uploads/services/67f399947471b.jpg', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `service_categories`
--

CREATE TABLE `service_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_categories`
--

INSERT INTO `service_categories` (`id`, `name`, `description`, `image`, `status`) VALUES
(1, 'Bridal Accessories', '', 'uploads/categories/67f3790f41fe4.jpg', 'active'),
(2, 'Entertainment', '', 'uploads/categories/67f37905bb0f3.jpg', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `content`, `rating`, `status`, `created_at`) VALUES
(1, 2, 'You did a wonderful job for us and I am very grateful for the expert input you provided in advance of the big day.', 4, 'approved', '2025-04-07 00:21:49');

-- --------------------------------------------------------

--
-- Table structure for table `todo_lists`
--

CREATE TABLE `todo_lists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `phone`, `profile_image`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$qmuHvnh4QJ9VPiEpeDLfUOd41FPEzfsVbq3vKPllL3uHGwBdTsBLm', 'admin@wedding.com', 'System Administrator', NULL, NULL, 'admin', '2025-04-06 23:56:58', '2025-04-06 23:56:58'),
(2, 'visaldenuwan580', '$2y$10$0GzT61mzY8kaHc1WFeCYeO1Mddgiy2fQZguIZ/bQCDFhVJAnHhV5K', 'visalrajapaksha195@gmail.com', 'Visal Denuwan', '0779856452', 'profile_67f31e61d099e.png', 'user', '2025-04-07 00:00:02', '2025-04-07 00:38:02');

-- --------------------------------------------------------

--
-- Table structure for table `wedding_planners`
--

CREATE TABLE `wedding_planners` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `bio` text DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `specialties` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wedding_planners`
--

INSERT INTO `wedding_planners` (`id`, `full_name`, `email`, `phone`, `bio`, `experience_years`, `specialties`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Isuru Dhananjaya', 'dananjaya@gmail.com', '0776523415', '', NULL, NULL, 1, '2025-04-07 00:16:32', '2025-04-07 00:16:32');

-- --------------------------------------------------------

--
-- Table structure for table `wedding_plans`
--

CREATE TABLE `wedding_plans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `bride_name` varchar(100) DEFAULT NULL,
  `groom_name` varchar(100) DEFAULT NULL,
  `bride_age` int(11) DEFAULT NULL,
  `groom_age` int(11) DEFAULT NULL,
  `wedding_date` date DEFAULT NULL,
  `venue_preference` varchar(255) DEFAULT NULL,
  `guest_count` int(11) DEFAULT NULL,
  `guest_list_file` varchar(255) DEFAULT NULL,
  `guest_considerations` text DEFAULT NULL,
  `budget_range` varchar(50) DEFAULT NULL,
  `invitation_files` text DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `planner_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wedding_plans`
--

INSERT INTO `wedding_plans` (`id`, `user_id`, `bride_name`, `groom_name`, `bride_age`, `groom_age`, `wedding_date`, `venue_preference`, `guest_count`, `guest_list_file`, `guest_considerations`, `budget_range`, `invitation_files`, `additional_notes`, `status`, `planner_id`, `created_at`, `updated_at`) VALUES
(1, 2, 'Udeshi', 'Manula', 22, 22, '2025-04-09', 'hotel sasha', 122, '', '', '25000-50000', '[]', '', 'pending', 1, '2025-04-07 00:15:38', '2025-04-07 00:49:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `guest_lists`
--
ALTER TABLE `guest_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `payment_errors`
--
ALTER TABLE `payment_errors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `paypal_processing`
--
ALTER TABLE `paypal_processing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_order_status` (`order_id`,`status`);

--
-- Indexes for table `planner_assignments`
--
ALTER TABLE `planner_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `planner_id` (`planner_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `saved_cards`
--
ALTER TABLE `saved_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_categories`
--
ALTER TABLE `service_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `todo_lists`
--
ALTER TABLE `todo_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wedding_planners`
--
ALTER TABLE `wedding_planners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wedding_plans`
--
ALTER TABLE `wedding_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `guest_lists`
--
ALTER TABLE `guest_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `payment_errors`
--
ALTER TABLE `payment_errors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `paypal_processing`
--
ALTER TABLE `paypal_processing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `planner_assignments`
--
ALTER TABLE `planner_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_cards`
--
ALTER TABLE `saved_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `service_categories`
--
ALTER TABLE `service_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `todo_lists`
--
ALTER TABLE `todo_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `wedding_planners`
--
ALTER TABLE `wedding_planners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wedding_plans`
--
ALTER TABLE `wedding_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `guest_lists`
--
ALTER TABLE `guest_lists`
  ADD CONSTRAINT `guest_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `payment_errors`
--
ALTER TABLE `payment_errors`
  ADD CONSTRAINT `payment_errors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `paypal_processing`
--
ALTER TABLE `paypal_processing`
  ADD CONSTRAINT `paypal_processing_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `planner_assignments`
--
ALTER TABLE `planner_assignments`
  ADD CONSTRAINT `planner_assignments_ibfk_1` FOREIGN KEY (`planner_id`) REFERENCES `wedding_planners` (`id`),
  ADD CONSTRAINT `planner_assignments_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Constraints for table `saved_cards`
--
ALTER TABLE `saved_cards`
  ADD CONSTRAINT `saved_cards_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `todo_lists`
--
ALTER TABLE `todo_lists`
  ADD CONSTRAINT `todo_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `wedding_plans`
--
ALTER TABLE `wedding_plans`
  ADD CONSTRAINT `wedding_plans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
