-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 10:17 AM
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
  `status` enum('pending','picked_up') NOT NULL DEFAULT 'pending',
  `category` enum('Produce','Protein','Dairy & Bakery','Grains & Pantry','Snacks & Beverages') DEFAULT NULL,
  `pickup_location` varchar(60) DEFAULT NULL,
  `description` varchar(80) DEFAULT NULL,
  `donation_date` date DEFAULT current_timestamp(),
  `donor_user_id` int(20) UNSIGNED NOT NULL,
  `claimant_user_id` int(20) UNSIGNED DEFAULT NULL,
  `availability` varchar(255) DEFAULT NULL,
  `contact` varchar(255) DEFAULT NULL,
  `created_at` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation`
--

INSERT INTO `donation` (`donation_id`, `status`, `category`, `pickup_location`, `description`, `donation_date`, `donor_user_id`, `claimant_user_id`, `availability`, `contact`, `created_at`) VALUES
(47, 'pending', 'Produce', '', NULL, '2025-10-15', 10, NULL, '', '', '2025-11-12'),
(48, 'pending', 'Produce', '', NULL, '2025-10-15', 10, NULL, '', '', '2025-11-12'),
(49, 'pending', NULL, 'ddd', NULL, '2025-10-15', 10, NULL, NULL, NULL, '2025-11-12'),
(51, 'pending', NULL, 'midvalley', NULL, '2025-10-22', 11, NULL, NULL, NULL, '2025-11-12'),
(52, 'pending', NULL, 'midvalley', NULL, '2025-10-30', 11, NULL, NULL, NULL, '2025-11-12'),
(54, 'pending', NULL, 'city kepong', NULL, '2025-11-01', 11, NULL, NULL, NULL, '2025-11-12'),
(57, 'picked_up', 'Produce', 'park', '11', '2025-12-12', 8, NULL, 'yes', '60 1919191919', '2025-11-12'),
(62, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2025-11-12'),
(63, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-11-12'),
(64, 'pending', 'Dairy & Bakery', '', NULL, NULL, 12, NULL, '', '', '2025-11-16');

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
(47, 43, 1),
(48, 44, 5),
(49, 43, 1),
(52, 48, 5),
(57, 81, 1),
(58, 87, 2),
(59, 45, 5),
(60, 43, 1),
(61, 19, 4),
(62, 86, 20),
(63, 44, 3),
(64, 91, 5);

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
  `status` enum('available','used','expired','donation','reserved') NOT NULL,
  `user_id` int(20) UNSIGNED NOT NULL,
  `created_at` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fooditem`
--

INSERT INTO `fooditem` (`foodItem_id`, `category`, `food_name`, `quantity`, `expiry_date`, `storage_location`, `description`, `status`, `user_id`, `created_at`) VALUES
(1, 'Produce', 'Apples', 10, '2023-02-10', 'Fridge', 'Fresh red apples', 'used', 12, '2023-01-15'),
(2, 'Protein', 'Chicken Breast', 6, '2023-03-05', 'Freezer', 'Boneless chicken breast 500g', 'used', 12, '2023-02-15'),
(3, 'Dairy & Bakery', 'Milk', 3, '2023-03-25', 'Fridge', 'Low-fat milk 1L', 'expired', 12, '2023-03-01'),
(4, 'Grains & Pantry', 'Rice', 5, '2023-09-10', 'Pantry', 'White jasmine rice 5kg', 'available', 12, '2023-06-15'),
(5, 'Snacks & Beverages', 'Cookies', 8, '2023-07-15', 'Pantry', 'Chocolate chip cookies', 'available', 12, '2023-07-01'),
(6, 'Produce', 'Carrots', 12, '2023-10-12', 'Fridge', 'Organic carrots', 'available', 12, '2023-09-25'),
(7, 'Protein', 'Eggs', 12, '2023-05-20', 'Fridge', 'Free-range eggs', 'used', 12, '2023-05-01'),
(8, 'Dairy & Bakery', 'Cheese', 2, '2023-11-10', 'Fridge', 'Cheddar cheese block', 'available', 12, '2023-10-15'),
(9, 'Grains & Pantry', 'Oats', 3, '2023-12-30', 'Pantry', 'Instant oats 1kg', 'available', 12, '2023-12-01'),
(10, 'Snacks & Beverages', 'Juice', 5, '2023-08-15', 'Fridge', 'Orange juice 1L', 'used', 12, '2023-08-01'),
(11, 'Produce', 'Lettuce', 2, '2023-11-22', 'Fridge', 'Fresh lettuce leaves', 'available', 12, '2023-11-10'),
(12, 'Protein', 'Fish Fillet', 3, '2023-09-05', 'Freezer', 'Salmon fillet 200g', 'used', 12, '2023-08-20'),
(13, 'Dairy & Bakery', 'Butter', 2, '2023-12-20', 'Fridge', 'Salted butter 250g', 'available', 12, '2023-12-01'),
(14, 'Grains & Pantry', 'Flour', 4, '2023-10-10', 'Pantry', 'All-purpose flour 2kg', 'available', 12, '2023-09-30'),
(15, 'Snacks & Beverages', 'Chocolate Bar', 6, '2023-12-28', 'Pantry', 'Dark chocolate 70%', 'available', 12, '2023-12-10'),
(16, 'Produce', 'Bananas', 8, '2024-02-03', 'Countertop', 'Ripe yellow bananas', 'used', 12, '2024-01-30'),
(17, 'Protein', 'Tofu', 3, '2024-04-12', 'Fridge', 'Soft soybean tofu', 'available', 12, '2024-03-20'),
(18, 'Dairy & Bakery', 'Yogurt', 5, '2024-05-05', 'Fridge', 'Greek yogurt cup', 'used', 12, '2024-04-25'),
(19, 'Grains & Pantry', 'Pasta', 4, '2024-06-15', 'Pantry', 'Spaghetti pasta 500g', 'available', 12, '2024-06-01'),
(20, 'Snacks & Beverages', 'Chips', 7, '2024-06-20', 'Pantry', 'Potato chips family pack', 'used', 12, '2024-06-10'),
(21, 'Produce', 'Tomatoes', 6, '2024-07-10', 'Fridge', 'Cherry tomatoes pack', 'available', 12, '2024-07-01'),
(22, 'Protein', 'Beef', 4, '2024-08-20', 'Freezer', 'Lean ground beef 500g', 'available', 12, '2024-08-01'),
(23, 'Dairy & Bakery', 'Bread', 2, '2024-09-10', 'Countertop', 'Wholemeal bread loaf', 'used', 12, '2024-09-05'),
(24, 'Grains & Pantry', 'Cereal', 3, '2024-10-05', 'Pantry', 'Honey oat cereal', 'available', 12, '2024-09-20'),
(25, 'Snacks & Beverages', 'Soda', 6, '2024-10-25', 'Pantry', 'Canned soda drinks', 'available', 12, '2024-10-10'),
(26, 'Produce', 'Spinach', 3, '2024-11-12', 'Fridge', 'Baby spinach leaves', 'used', 12, '2024-11-01'),
(27, 'Protein', 'Sausages', 5, '2024-12-20', 'Freezer', 'Chicken sausages pack', 'available', 12, '2024-12-01'),
(28, 'Dairy & Bakery', 'Croissant', 4, '2024-12-15', 'Countertop', 'Buttery croissant', 'used', 12, '2024-12-05'),
(29, 'Grains & Pantry', 'Sugar', 4, '2024-11-30', 'Pantry', 'Brown sugar 1kg', 'available', 12, '2024-11-15'),
(30, 'Snacks & Beverages', 'Energy Drink', 8, '2024-12-22', 'Pantry', 'Canned energy drink', 'available', 12, '2024-12-10'),
(31, 'Produce', 'Mangoes', 5, '2025-03-01', 'Countertop', 'Sweet ripe mangoes', 'used', 12, '2025-02-20'),
(32, 'Protein', 'Shrimp', 4, '2025-03-15', 'Freezer', 'Frozen shrimp 300g', 'available', 12, '2025-03-01'),
(33, 'Dairy & Bakery', 'Cream Cheese', 2, '2025-04-05', 'Fridge', 'Cream cheese tub', 'available', 12, '2025-03-15'),
(34, 'Grains & Pantry', 'Lentils', 3, '2025-05-25', 'Pantry', 'Red lentils 1kg', 'available', 12, '2025-05-10'),
(35, 'Snacks & Beverages', 'Tea', 2, '2025-06-15', 'Pantry', 'Green tea bags 20pcs', 'available', 12, '2025-06-01'),
(36, 'Produce', 'Oranges', 6, '2025-07-05', 'Countertop', 'Sweet oranges', 'used', 12, '2025-06-20'),
(37, 'Protein', 'Pork Chop', 3, '2025-07-25', 'Freezer', 'Marinated pork chops', 'available', 12, '2025-07-10'),
(38, 'Dairy & Bakery', 'Cream', 2, '2025-08-18', 'Fridge', 'Whipping cream 200ml', 'used', 12, '2025-08-10'),
(39, 'Grains & Pantry', 'Beans', 5, '2025-09-10', 'Pantry', 'Canned baked beans', 'available', 12, '2025-08-30'),
(40, 'Snacks & Beverages', 'Instant Coffee', 3, '2025-09-25', 'Pantry', 'Instant coffee jar', 'available', 12, '2025-09-10'),
(41, 'Produce', 'Strawberries', 4, '2025-10-05', 'Fridge', 'Fresh strawberries pack', 'available', 12, '2025-09-28'),
(42, 'Protein', 'Duck Meat', 2, '2025-11-05', 'Freezer', 'Frozen duck breast', 'reserved', 12, '2025-10-25'),
(43, 'Dairy & Bakery', 'Whipped Cream', 1, '2025-11-12', 'Fridge', 'Spray whipped cream', 'available', 12, '2025-11-01'),
(44, 'Grains & Pantry', 'Bread Crumbs', 3, '2025-12-10', 'Pantry', 'Seasoned bread crumbs', 'donation', 12, '2025-11-25'),
(45, 'Snacks & Beverages', 'Biscuits', 5, '2025-12-20', 'Pantry', 'Butter biscuits tin', 'available', 12, '2025-12-01'),
(86, 'Protein', 'Coconuts', 20, '2025-12-26', 'Fridge', '', 'donation', 12, '2025-11-12'),
(88, 'Protein', 'Sprite', 10, '2026-01-01', 'Fridge', '', 'available', 12, '2025-11-16'),
(89, 'Snacks & Beverages', 'Donuts', 5, '2025-11-19', 'Freezer', '', 'available', 12, '2025-11-16'),
(90, 'Grains & Pantry', 'Pineapples', 10, '2026-03-05', 'Pantry', '', 'used', 12, '2025-11-16'),
(91, 'Dairy & Bakery', 'Watermelon', 5, '2026-01-23', 'Fridge', '', 'donation', 12, '2025-11-16'),
(94, 'Produce', 'Broccoli', 3, '2025-11-25', 'Fridge', 'Fresh broccoli head', 'available', 12, '2025-11-18'),
(95, 'Protein', 'Salmon', 2, '2025-11-28', 'Freezer', 'Atlantic salmon fillet', 'available', 12, '2025-11-18'),
(96, 'Grains & Pantry', 'Quinoa', 1, '2026-01-15', 'Pantry', 'Organic white quinoa', 'available', 12, '2025-11-18'),
(97, 'Dairy & Bakery', 'Yogurt Drink', 4, '2025-11-23', 'Fridge', 'Low sugar yogurt drink', 'available', 12, '2025-11-18'),
(98, 'Snacks & Beverages', 'Granola Bar', 6, '2026-02-01', 'Pantry', 'Healthy granola bars', 'available', 12, '2025-11-18'),
(99, 'Produce', 'Broccoli', 3, '2025-11-25', 'Fridge', 'Fresh broccoli head', 'reserved', 12, '2025-11-18'),
(100, 'Protein', 'Salmon', 2, '2025-11-28', 'Freezer', 'Atlantic salmon fillet', 'reserved', 12, '2025-11-18'),
(101, 'Grains & Pantry', 'Quinoa', 1, '2026-01-15', 'Pantry', 'Organic white quinoa', 'reserved', 12, '2025-11-18'),
(102, 'Dairy & Bakery', 'Yogurt Drink', 4, '2025-11-23', 'Fridge', 'Low sugar yogurt drink', 'reserved', 12, '2025-11-18'),
(103, 'Snacks & Beverages', 'Granola Bar', 6, '2026-02-01', 'Pantry', 'Healthy granola bars', 'reserved', 12, '2025-11-18'),
(104, 'Produce', 'Apple', 4, '2025-11-29', 'Fridge', '', 'available', 12, '2025-11-18'),
(105, 'Produce', 'Banana', 4, '2025-11-30', 'Countertop', '', 'available', 12, '2025-11-18'),
(107, 'Produce', 'Apple', 1, '2025-11-29', 'Fridge', '', 'reserved', 12, '2025-11-18'),
(108, 'Produce', 'Banana', 1, '2025-11-30', 'Countertop', '', 'reserved', 12, '2025-11-18'),
(110, 'Produce', 'Chicken Breast', 2, '2025-11-23', 'Fridge', '', 'available', 12, '2025-11-18'),
(111, 'Produce', 'Mayonnaise', 2, '2025-11-25', 'Fridge', '', 'available', 12, '2025-11-18'),
(112, 'Produce', 'Bread', 5, '2025-11-26', 'Countertop', '', 'available', 12, '2025-11-18');

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
,`status` enum('available','used','expired','donation','reserved')
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

--
-- Dumping data for table `mealplan`
--

INSERT INTO `mealplan` (`mealplan_id`, `meal_name`, `mealplan_date`, `meal_type`, `description`, `user_id`) VALUES
(12, 'Burger MealPlan', '2025-11-15', 'lunch', 'Delicious tasting Burger, with amazing and fresh ingredients!', 11),
(15, 'Fruit Salad', '2025-11-15', 'breakfast', 'Delicious tasting Burger, with amazing and fresh ingredients!', 11),
(23, 'Cookies Fruit Salad', '2025-11-12', 'breakfast', '', 11),
(24, 'Healthy Broccoli Lunch', '2025-11-20', 'lunch', 'Steamed broccoli with light spices and olive oil.', 12),
(25, 'Salmon Dinner Bowl', '2025-11-20', 'dinner', 'Grilled salmon paired with vegetables and light soy glaze.', 12),
(26, 'Morning Yogurt Cup', '2025-11-21', 'breakfast', 'Refreshing probiotic yogurt with granola.', 12),
(27, 'Quinoa Power Dinner', '2025-11-22', 'dinner', 'High-energy quinoa bowl with balanced macros.', 12),
(28, 'Fruit Salad', '2025-11-20', 'lunch', '', 12);

-- --------------------------------------------------------

--
-- Table structure for table `mealplan_fooditem`
--

CREATE TABLE `mealplan_fooditem` (
  `mealplan_id` int(20) NOT NULL,
  `fooditem_id` int(20) NOT NULL,
  `quantity` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mealplan_fooditem`
--

INSERT INTO `mealplan_fooditem` (`mealplan_id`, `fooditem_id`, `quantity`) VALUES
(12, 53, 2),
(15, 61, 1),
(15, 62, 1),
(15, 63, 10),
(23, 77, 1),
(23, 78, 1),
(23, 79, 1),
(24, 99, 2),
(25, 100, 1),
(25, 99, 1),
(26, 102, 1),
(26, 103, 1),
(27, 101, 1),
(27, 100, 1),
(28, 107, 1),
(28, 108, 1),
(28, 109, 10);

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(20) NOT NULL,
  `title` varchar(30) NOT NULL,
  `description` varchar(80) NOT NULL,
  `notification_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('unread','seen') NOT NULL,
  `target_type` enum('inventory','donation','meal_plan','') NOT NULL,
  `target_id` int(20) NOT NULL,
  `user_id` int(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `title`, `description`, `notification_date`, `status`, `target_type`, `target_id`, `user_id`) VALUES
(42, 'Apple will expire soon', 'Item \"Apple\" expires on 2025-11-15', '2025-11-12 16:29:11', 'unread', 'inventory', 55, 11),
(43, 'Banana will expire soon', 'Item \"Banana\" expires on 2025-11-15', '2025-11-12 16:29:11', 'unread', 'inventory', 56, 11),
(45, 'Donation created', 'You donated \"\" at ', '2025-11-12 16:29:11', 'unread', 'donation', 47, 10),
(46, 'Donation created', 'You donated \"\" at ', '2025-11-12 16:29:11', 'unread', 'donation', 48, 10),
(47, 'Donation created', 'You donated \"\" at ddd', '2025-11-12 16:29:11', 'unread', 'donation', 49, 10),
(48, 'Donation created', 'You donated \"\" at midvalley', '2025-11-12 16:29:11', 'unread', 'donation', 51, 11),
(49, 'Donation created', 'You donated \"\" at midvalley', '2025-11-12 16:29:11', 'unread', 'donation', 52, 11),
(50, 'Donation created', 'You donated \"\" at city kepong', '2025-11-12 16:29:11', 'unread', 'donation', 54, 11),
(53, 'apple will expire soon', 'Item \"apple\" expires on 2025-11-12', '2025-11-12 16:36:48', 'seen', 'inventory', 81, 8),
(54, 'Donation created', 'You donated \"\" at ', '2025-11-12 16:36:53', 'seen', 'donation', 57, 8),
(55, 'Donation picked up', 'apple picked up (Donated on: 2025-12-12)', '2025-11-12 16:38:07', 'seen', 'donation', 57, 8),
(56, 'Donuts will expire soon', 'Item \"Donuts\" expires on 2025-11-19', '2025-11-16 19:52:05', 'unread', 'inventory', 89, 12),
(59, 'Donation created', 'You donated \"\" at ', '2025-11-16 19:52:05', 'unread', 'donation', 47, 10),
(60, 'Donation created', 'You donated \"\" at ', '2025-11-16 19:52:05', 'unread', 'donation', 48, 10),
(61, 'Donation created', 'You donated \"\" at ddd', '2025-11-16 19:52:05', 'unread', 'donation', 49, 10),
(62, 'Donation created', 'You donated \"\" at midvalley', '2025-11-16 19:52:05', 'unread', 'donation', 51, 11),
(63, 'Donation created', 'You donated \"\" at midvalley', '2025-11-16 19:52:05', 'unread', 'donation', 52, 11),
(64, 'Donation created', 'You donated \"\" at city kepong', '2025-11-16 19:52:05', 'unread', 'donation', 54, 11),
(65, 'Donation created', 'You donated \"11\" at park', '2025-11-16 19:52:05', 'unread', 'donation', 57, 8),
(66, 'Donation created', 'You donated \"\" at ', '2025-11-16 19:52:05', 'unread', 'donation', 62, 12),
(67, 'Donation created', 'You donated \"\" at ', '2025-11-16 19:52:05', 'unread', 'donation', 63, 12),
(68, 'Donation created', 'You donated \"\" at ', '2025-11-16 19:52:05', 'unread', 'donation', 64, 12),
(74, 'Meal Plan Created', 'Your meal plan \"Healthy Broccoli Lunch\" is ready.', '2025-11-18 17:02:33', 'unread', 'meal_plan', 24, 12),
(75, 'Meal Plan Created', 'Your meal plan \"Salmon Dinner Bowl\" is ready.', '2025-11-18 17:02:33', 'unread', 'meal_plan', 25, 12),
(76, 'Meal Plan Created', 'Your meal plan \"Morning Yogurt Cup\" is ready.', '2025-11-18 17:02:33', 'unread', 'meal_plan', 26, 12),
(77, 'Meal Plan Created', 'Your meal plan \"Quinoa Power Dinner\" is ready.', '2025-11-18 17:02:33', 'unread', 'meal_plan', 27, 12),
(78, 'Fruit Salad', '', '2025-11-20 00:00:00', '', 'meal_plan', 28, 12);

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
(8, 'QuJiaWei', 'b2400595@helplive.edu.my', '$2y$10$oXBwXpnGZXsGyVOcTaS35uZn1H.51V31s17NwJ9ahs9bt.b49lIhq', 0, 0),
(9, 'Qu JiaWei', 'b2300733@helplive.edu.my', '$2y$10$K0b7t1yNqheIBfUAcBfXsOF2EAqO5ja2CtowF4/IaVturAdNuG0AC', 0, 0),
(10, 'jia', 'nazermy.qu@gmail.com', '$2y$10$t0kDtd4R8SG8ApmX9S8Bv./YGG7IKbXvvO5cxA45RjS8HSvVBaytK', 0, 1),
(11, 'Aaron', 'kianpoh0219@gmail.com', '$2y$10$SthVD4qsV7Hj9DFszANEoOEQulbwoxfK2QFB3owAjTdTdCMOyh9D.', 69, 0),
(12, 'leejl1652@gmail.com', 'leejl1652@gmail.com', '$2y$10$GPgii4/el9QE6/RvaaAkVe9wBhFBG9iYwkNloCgoRe2j2oy4AeP82', 0, 0);

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
(22, 'b2300733@helplive.edu.my', '790544', '2025-10-15 06:27:25', 0, '2025-10-15 04:26:25'),
(23, 'b2400595@helplive.edu.my', '778304', '2025-10-15 06:28:16', 0, '2025-10-15 04:27:16');

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
  MODIFY `donation_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `fooditem`
--
ALTER TABLE `fooditem`
  MODIFY `foodItem_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `mealplan`
--
ALTER TABLE `mealplan`
  MODIFY `mealplan_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `verification_codes`
--
ALTER TABLE `verification_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
-- Constraints for table `mealplan`
--
ALTER TABLE `mealplan`
  ADD CONSTRAINT `mealplan_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
