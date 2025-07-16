-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 15, 2025 at 05:18 PM
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
-- Database: `rental_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'أحمد محمد', 'ahmed@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-07-07 15:50:49'),
(2, 'فاطمة علي', 'fatima@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-07-07 15:50:49'),
(3, 'Mohammad Elcadi', 'elcadim5@gmail.com', '$2y$10$BAxkF5tX.ILZ1v.8Tgdxme9VmP3XbbevnqUAnMiJS/E1Od0.3yyTC', '2025-07-07 19:26:01'),
(4, 'Mohammad Elcadi', 'elcadimm@gmail.com', '$2y$10$ZOCr4XtaVzpKFZTdaBMKY.9HP69BjscPDbTv39cGwB4MhULEAjz0W', '2025-07-10 12:23:31'),
(5, 'mohammad elcadi', 'cadi@gmail.com', '$2y$10$JuJAmGIgfCyZgtKE1Q6HeuvdbcOlRfS6/deA5xeML0TazdSj1eENS', '2025-07-15 14:00:20');

-- --------------------------------------------------------

--
-- Table structure for table `housing_types`
--

CREATE TABLE `housing_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `housing_types`
--

INSERT INTO `housing_types` (`id`, `name`, `user_id`, `created_at`) VALUES
(7, 'bit', 4, '2025-07-10 12:25:54'),
(8, 'kozina', 4, '2025-07-10 12:25:59'),
(9, 'kjlh', 5, '2025-07-15 14:07:29');

-- --------------------------------------------------------

-- Table structure for table `tenants`

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100),
  `cin` varchar(20) NOT NULL,
  `house_type` varchar(100) NOT NULL,
  `marital_status` ENUM('Single', 'Married', 'Family') NOT NULL DEFAULT 'Single',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `price_per_day` decimal(10,2) DEFAULT NULL,
  `admin_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `housing_types`
--
ALTER TABLE `housing_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `housing_types`
--
ALTER TABLE `housing_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `housing_types`
--
ALTER TABLE `housing_types`
  ADD CONSTRAINT `housing_types_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

ALTER TABLE `tenants`
  ADD COLUMN `total_rent` decimal(12,2) DEFAULT NULL AFTER `price_per_day`;

-- Migration: Allow registration with email or phone for admins
ALTER TABLE admins
  ADD COLUMN phone VARCHAR(20) UNIQUE DEFAULT NULL,
  MODIFY email VARCHAR(100) UNIQUE DEFAULT NULL;
-- End migration

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
