-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 05:40 AM
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
-- Database: `savebite`
--

-- --------------------------------------------------------

--
-- Table structure for table `donation`
--

CREATE TABLE `donation` (
  `donation_id` int(20) NOT NULL,
  `status` enum('pending','picked_up') NOT NULL DEFAULT 'pending',
  `category` enum('Produce','Protein','Dairy & Bakery','Grains & Pantry','Snacks & Beverages') DEFAULT NULL,
  `pickup_location` varchar(60) DEFAULT NULL,
  `description` varchar(80) DEFAULT NULL,
  `donation_date` date NOT NULL,
  `donor_user_id` int(20) UNSIGNED NOT NULL,
  `claimant_user_id` int(20) UNSIGNED DEFAULT NULL,
  `availability` varchar(255) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation`
--

INSERT INTO `donation` (`donation_id`, `status`, `category`, `pickup_location`, `description`, `donation_date`, `donor_user_id`, `claimant_user_id`, `availability`, `contact`) VALUES
(42, 'pending', 'Produce', '111', '111', '2025-10-15', 8, NULL, '111', '111'),
(45, 'pending', 'Produce', '', NULL, '2025-10-15', 8, NULL, '', '');

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
(42, 38, 1),
(45, 40, 1);

-- --------------------------------------------------------

--
-- Table structure for table `fooditem`
--

CREATE TABLE `fooditem` (
  `foodItem_id` int(20) NOT NULL,
  `category` enum('Produce','Protein','Dairy & Bakery','Grains & Pantry','Snacks & Beverages') NOT NULL,
  `food_name` varchar(20) NOT NULL,
  `quantity` int(10) NOT NULL,
  `expiry_date` date NOT NULL DEFAULT current_timestamp(),
  `storage_location` enum('Fridge','Freezer','Pantry','Countertop') NOT NULL,
  `description` varchar(80) DEFAULT NULL,
  `status` enum('available','used','expired') NOT NULL,
  `user_id` int(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fooditem`
--

INSERT INTO `fooditem` (`foodItem_id`, `category`, `food_name`, `quantity`, `expiry_date`, `storage_location`, `description`, `status`, `user_id`) VALUES
(38, 'Protein', 'apple', 0, '2025-12-12', 'Pantry', '111', 'used', 8),
(40, 'Produce', '1', 1, '1111-11-11', 'Fridge', '1', 'available', 8);

-- --------------------------------------------------------

--
-- Stand-in structure for view `food_items`
-- (See below for the actual view)
--
CREATE TABLE `food_items` (
`fooditem_id` int(20)
,`name` varchar(20)
,`quantity` int(10)
,`category` enum('Produce','Protein','Dairy & Bakery','Grains & Pantry','Snacks & Beverages')
,`expiry_date` date
,`status` enum('available','used','expired')
,`description` varchar(80)
,`created_by` int(20) unsigned
,`storage_location` enum('Fridge','Freezer','Pantry','Countertop')
,`created_at` datetime
);

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
  `household_number` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `email`, `password`, `household_number`) VALUES
(7, 'Aaron', 'brabrab@gmail.com', '$2y$10$ONo/hJPFgUFKsiIbGvSEg.QFuOagb0J7nwppCxN2FGiepRtzLLjEK', 3),
(8, 'QuJiaWei', 'b2400595@helplive.edu.my', '$2y$10$oXBwXpnGZXsGyVOcTaS35uZn1H.51V31s17NwJ9ahs9bt.b49lIhq', 0);

-- --------------------------------------------------------

--
-- Structure for view `food_items`
--
DROP TABLE IF EXISTS `food_items`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `food_items`  AS SELECT `fooditem`.`foodItem_id` AS `fooditem_id`, `fooditem`.`food_name` AS `name`, `fooditem`.`quantity` AS `quantity`, `fooditem`.`category` AS `category`, `fooditem`.`expiry_date` AS `expiry_date`, `fooditem`.`status` AS `status`, `fooditem`.`description` AS `description`, `fooditem`.`user_id` AS `created_by`, `fooditem`.`storage_location` AS `storage_location`, current_timestamp() AS `created_at` FROM `fooditem` ;

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `donation`
--
ALTER TABLE `donation`
  MODIFY `donation_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `fooditem`
--
ALTER TABLE `fooditem`
  MODIFY `foodItem_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

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
  MODIFY `user_id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  ADD CONSTRAINT `fooditem_mealplan_fk` FOREIGN KEY (`fooditem_id`) REFERENCES `fooditem` (`foodItem_id`),
  ADD CONSTRAINT `mealplan_fooditem_fk` FOREIGN KEY (`mealplan_id`) REFERENCES `mealplan` (`mealplan_id`);

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
