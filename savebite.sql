-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2025 at 11:51 AM
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
-- Database: `savebite`
--

-- --------------------------------------------------------

--
-- Table structure for table `donation`
--

CREATE TABLE `donation` (
  `donation_id` int(20) NOT NULL,
  `status` enum('pending','picked_up') NOT NULL,
  `pickup_location` varchar(60) NOT NULL,
  `description` varchar(80) DEFAULT NULL,
  `donation_date` date NOT NULL,
  `donor_user_id` int(20) UNSIGNED NOT NULL,
  `claimant_user_id` int(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation`
--

INSERT INTO `donation` (`donation_id`, `status`, `pickup_location`, `description`, `donation_date`, `donor_user_id`, `claimant_user_id`) VALUES
(1, 'pending', 'midvalley', NULL, '2025-10-17', 15, NULL),
(2, 'pending', '1 utama', NULL, '2025-10-14', 15, NULL),
(3, 'pending', 'times square', NULL, '2025-10-10', 15, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `donation_fooditem`
--

CREATE TABLE `donation_fooditem` (
  `donation_id` int(20) NOT NULL,
  `fooditem_id` int(20) NOT NULL,
  `quantity` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation_fooditem`
--

INSERT INTO `donation_fooditem` (`donation_id`, `fooditem_id`, `quantity`) VALUES
(0, 9, 5),
(2, 16, 6),
(3, 18, 10);

-- --------------------------------------------------------

--
-- Table structure for table `fooditem`
--

CREATE TABLE `fooditem` (
  `foodItem_id` int(20) NOT NULL,
  `user_id` int(20) UNSIGNED NOT NULL,
  `category` varchar(40) NOT NULL,
  `food_name` varchar(20) NOT NULL,
  `quantity` int(10) NOT NULL,
  `expiry_date` date NOT NULL,
  `storage_location` varchar(80) NOT NULL,
  `description` varchar(80) DEFAULT NULL,
  `status` enum('used','donation','reserved','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fooditem`
--

INSERT INTO `fooditem` (`foodItem_id`, `user_id`, `category`, `food_name`, `quantity`, `expiry_date`, `storage_location`, `description`, `status`) VALUES
(1, 15, 'Produce', 'Broccoli', 3, '2025-10-16', 'Fridge', 'Fresh green broccoli', 'reserved'),
(2, 15, 'Produce', 'Banana', 6, '2025-10-12', 'Countertop', 'Ripe bananas', 'used'),
(3, 15, 'Protein', 'Salmon Fillet', 2, '2025-10-09', 'Freezer', 'Frozen Norwegian salmon', ''),
(4, 15, 'Protein', 'Tofu Block', 4, '2025-10-18', 'Fridge', 'Organic firm tofu', ''),
(5, 15, 'Dairy & Bakery', 'Butter', 1, '2026-02-01', 'Fridge', 'Salted butter block', 'reserved'),
(6, 15, 'Dairy & Bakery', 'Cheese Slice', 10, '2025-10-25', 'Fridge', 'Cheddar cheese slices', ''),
(7, 15, 'Grains & Pantry', 'Spaghetti', 2, '2026-06-30', 'Pantry', 'Dry spaghetti pack', 'reserved'),
(8, 15, 'Grains & Pantry', 'Canned Beans', 4, '2027-01-15', 'Pantry', 'Baked beans in tomato sauce', ''),
(9, 15, 'Snacks & Beverages', 'Chocolate Bar', 5, '2026-03-10', 'Pantry', 'Dark chocolate 70%', 'donation'),
(10, 15, 'Snacks & Beverages', 'Green Tea', 1, '2027-04-20', 'Pantry', 'Green tea bags (20pcs)', 'reserved'),
(11, 15, 'Produce', 'Spinach', 2, '2025-10-18', 'Fridge', 'Fresh baby spinach leaves', 'reserved'),
(12, 15, 'Produce', 'Tomato', 8, '2025-10-13', 'Fridge', 'Ripe red tomatoes', 'used'),
(13, 15, 'Protein', 'Minced Beef', 3, '2025-10-09', 'Freezer', 'Frozen minced beef pack', ''),
(14, 15, 'Protein', 'Fish Fillet', 4, '2025-10-11', 'Freezer', 'White fish fillets', 'used'),
(15, 15, 'Dairy & Bakery', 'Yogurt', 5, '2025-10-20', 'Fridge', 'Plain low-fat yogurt cups', 'reserved'),
(16, 15, 'Dairy & Bakery', 'Croissant', 6, '2025-10-10', 'Countertop', 'Buttery croissants', 'donation'),
(17, 15, 'Grains & Pantry', 'Cooking Oil', 1, '2027-05-15', 'Pantry', 'Sunflower cooking oil bottle', 'reserved'),
(18, 15, 'Grains & Pantry', 'Instant Noodles', 10, '2026-11-30', 'Pantry', 'Spicy chicken flavor instant noodles', 'donation'),
(19, 15, 'Snacks & Beverages', 'Cookies', 3, '2026-02-25', 'Pantry', 'Chocolate chip cookies', 'used'),
(20, 15, 'Snacks & Beverages', 'Coffee Powder', 1, '2027-07-01', 'Pantry', 'Instant coffee powder jar', 'reserved');

-- --------------------------------------------------------

--
-- Table structure for table `mealplan`
--

CREATE TABLE `mealplan` (
  `mealplan_id` int(20) NOT NULL,
  `meal_name` varchar(50) NOT NULL,
  `mealplan_date` date NOT NULL,
  `meal_type` enum('breakfast','lunch','dinner','snack') NOT NULL,
  `description` varchar(80) DEFAULT NULL,
  `user_id` int(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mealplan_fooditem`
--

CREATE TABLE `mealplan_fooditem` (
  `mealplan_id` int(20) NOT NULL,
  `fooditem_id` int(20) NOT NULL,
  `quantity` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(20) NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` varchar(80) NOT NULL,
  `notification_date` date NOT NULL,
  `status` enum('new','seen','','') NOT NULL,
  `target_type` enum('fooditem','donation','meal_plan','') NOT NULL,
  `target_id` int(20) NOT NULL,
  `user_id` int(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(20) UNSIGNED NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(80) NOT NULL,
  `household_number` int(20) NOT NULL,
  `isAuthActive` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `email`, `password`, `household_number`, `isAuthActive`) VALUES
(15, 'Aaron', 'kianpoh0219@gmail.com', '$2y$10$EzKq7lw/fhdlTzg0FEDfneh4v/c6hODZaniQgFN55YNRy0CRWtRgG', 69, 1);

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

CREATE TABLE `verification_codes` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_codes`
--

INSERT INTO `verification_codes` (`id`, `email`, `code`, `expires_at`, `used`, `created_at`) VALUES
(17, 'aaron@gmail.com', '694929', '2025-10-10 11:16:09', 0, '2025-10-10 09:15:09'),
(18, 'kianpoh0219@gmaill.com', '475127', '2025-10-10 11:17:00', 0, '2025-10-10 09:16:00'),
(19, 'kianpoh0219@gmail.com', '955104', '2025-10-10 11:26:31', 1, '2025-10-10 09:25:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `donation`
--
ALTER TABLE `donation`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `donorUser_user_id_fk` (`donor_user_id`),
  ADD KEY `claimantUser_user_id_fk` (`claimant_user_id`);

--
-- Indexes for table `donation_fooditem`
--
ALTER TABLE `donation_fooditem`
  ADD KEY `donation_fooditem_fk` (`donation_id`),
  ADD KEY `fooditem_donation_fk` (`fooditem_id`);

--
-- Indexes for table `fooditem`
--
ALTER TABLE `fooditem`
  ADD PRIMARY KEY (`foodItem_id`),
  ADD KEY `fooditem_user_id_fk` (`user_id`);

--
-- Indexes for table `mealplan`
--
ALTER TABLE `mealplan`
  ADD PRIMARY KEY (`mealplan_id`),
  ADD KEY `mealplan_user_id_fk` (`user_id`);

--
-- Indexes for table `mealplan_fooditem`
--
ALTER TABLE `mealplan_fooditem`
  ADD KEY `mealplan_fooditem_fk` (`mealplan_id`),
  ADD KEY `fooditem_mealplan_fk` (`fooditem_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `notification_user_id_fk` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email_unique` (`email`);

--
-- Indexes for table `verification_codes`
--
ALTER TABLE `verification_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `donation`
--
ALTER TABLE `donation`
  MODIFY `donation_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fooditem`
--
ALTER TABLE `fooditem`
  MODIFY `foodItem_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `mealplan`
--
ALTER TABLE `mealplan`
  MODIFY `mealplan_id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donation`
--
ALTER TABLE `donation`
  ADD CONSTRAINT `claimantUser_user_id_fk` FOREIGN KEY (`claimant_user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `donorUser_user_id_fk` FOREIGN KEY (`donor_user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `donation_fooditem`
--
ALTER TABLE `donation_fooditem`
  ADD CONSTRAINT `donation_fooditem_fk` FOREIGN KEY (`donation_id`) REFERENCES `donation` (`donation_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fooditem_donation_fk` FOREIGN KEY (`fooditem_id`) REFERENCES `fooditem` (`foodItem_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `fooditem`
--
ALTER TABLE `fooditem`
  ADD CONSTRAINT `fooditem_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mealplan`
--
ALTER TABLE `mealplan`
  ADD CONSTRAINT `mealplan_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mealplan_fooditem`
--
ALTER TABLE `mealplan_fooditem`
  ADD CONSTRAINT `fooditem_mealplan_fk` FOREIGN KEY (`fooditem_id`) REFERENCES `fooditem` (`foodItem_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `mealplan_fooditem_id_fk` FOREIGN KEY (`mealplan_id`) REFERENCES `mealplan` (`mealplan_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
