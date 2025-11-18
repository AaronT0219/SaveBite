-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 02:15 PM
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
(62, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-01-12'),
(63, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-01-15'),
(64, 'pending', 'Dairy & Bakery', '', NULL, NULL, 12, NULL, '', '', '2025-02-03'),
(65, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-02-10'),
(66, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-02-11'),
(67, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2025-03-01'),
(68, 'pending', 'Dairy & Bakery', '', NULL, NULL, 12, NULL, '', '', '2025-03-05'),
(69, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-03-20'),
(70, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2025-04-02'),
(71, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2024-04-10'),
(72, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2025-04-18'),
(73, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-05-01'),
(74, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-05-12'),
(75, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2025-05-28'),
(76, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-06-02'),
(77, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-06-10'),
(78, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-06-19'),
(79, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2025-07-04'),
(80, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-07-10'),
(81, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2025-07-21'),
(82, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-08-01'),
(83, 'pending', 'Produce', '', NULL, NULL, 12, NULL, '', '', '2024-08-10'),
(84, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-08-25'),
(85, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-09-03'),
(86, 'pending', 'Dairy & Bakery', '', NULL, NULL, 12, NULL, '', '', '2025-09-12'),
(87, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-09-24'),
(88, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-10-01'),
(89, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2025-10-05'),
(90, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2025-10-14'),
(91, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2024-10-22'),
(92, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2025-11-02'),
(93, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-11-08'),
(94, 'pending', 'Dairy & Bakery', '', NULL, NULL, 12, NULL, '', '', '2025-11-11'),
(95, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2025-11-15'),
(96, 'pending', 'Dairy & Bakery', '', NULL, NULL, 12, NULL, '', '', '2025-11-16'),
(97, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-11-17'),
(98, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2024-11-18'),
(99, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-11-20'),
(100, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2025-11-22'),
(101, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2025-11-23'),
(102, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-11-25'),
(103, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2024-11-18'),
(104, 'pending', 'Grains & Pantry', '', NULL, NULL, 12, NULL, '', '', '2025-11-18'),
(105, 'pending', 'Produce', '', NULL, NULL, 12, NULL, '', '', '2024-11-18'),
(106, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2023-11-18'),
(107, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-11-18'),
(108, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-11-18'),
(109, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2023-11-18'),
(110, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2023-11-18'),
(111, 'pending', 'Dairy & Bakery', '', NULL, NULL, 12, NULL, '', '', '2023-11-18'),
(112, 'pending', 'Dairy & Bakery', '', NULL, NULL, 12, NULL, '', '', '2023-11-18'),
(113, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2024-11-18'),
(114, 'pending', 'Produce', '', NULL, NULL, 12, NULL, '', '', '2025-11-18'),
(115, 'pending', 'Protein', '', NULL, NULL, 12, NULL, '', '', '2024-11-18'),
(116, 'pending', 'Dairy & Bakery', '', NULL, NULL, 12, NULL, '', '', '2025-11-18'),
(117, 'pending', 'Snacks & Beverages', '', NULL, NULL, 12, NULL, '', '', '2023-11-18');

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
(64, 91, 5),
(65, 32, 4),
(66, 27, 5),
(67, 22, 4),
(68, 33, 2),
(69, 39, 5),
(70, 89, 5),
(71, 96, 1),
(72, 95, 2),
(73, 114, 3),
(74, 116, 2),
(75, 117, 2),
(76, 121, 2),
(77, 124, 2),
(78, 136, 4),
(79, 137, 6),
(80, 139, 2),
(81, 142, 2),
(82, 141, 2),
(83, 143, 3),
(84, 146, 2),
(85, 149, 2),
(86, 150, 1),
(87, 151, 1),
(88, 156, 2),
(89, 157, 2),
(90, 159, 2),
(91, 161, 2),
(92, 162, 2),
(93, 166, 10),
(94, 170, 5),
(95, 174, 10),
(96, 175, 5),
(97, 181, 5),
(98, 191, 10),
(99, 196, 12),
(100, 199, 10),
(101, 209, 10),
(102, 211, 20),
(103, 216, 3),
(104, 186, 10),
(105, 153, 5),
(106, 147, 4),
(107, 134, 2),
(108, 129, 2),
(109, 127, 3),
(110, 119, 2),
(111, 120, 1),
(112, 97, 4),
(113, 98, 6),
(114, 94, 3),
(115, 88, 10),
(116, 43, 1),
(117, 40, 3);

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
(6, 'Produce', 'Carrots', 3, '2023-10-12', 'Fridge', 'Organic carrots', 'available', 12, '2023-09-25'),
(7, 'Protein', 'Eggs', 230, '2023-05-20', 'Fridge', 'Free-range eggs', 'used', 12, '2023-05-01'),
(8, 'Dairy & Bakery', 'Cheese', 2, '2023-11-10', 'Fridge', 'Cheddar cheese block', 'available', 12, '2023-10-15'),
(9, 'Grains & Pantry', 'Oats', 3, '2023-12-30', 'Pantry', 'Instant oats 1kg', 'available', 12, '2023-12-01'),
(10, 'Snacks & Beverages', 'Juice', 5, '2023-08-15', 'Fridge', 'Orange juice 1L', 'used', 12, '2023-08-01'),
(11, 'Produce', 'Lettuce', 2, '2023-11-22', 'Fridge', 'Fresh lettuce leaves', 'available', 12, '2023-11-10'),
(12, 'Protein', 'Fish Fillet', 3, '2023-09-05', 'Freezer', 'Salmon fillet 200g', 'used', 12, '2023-08-20'),
(13, 'Dairy & Bakery', 'Butter', 2, '2023-12-20', 'Fridge', 'Salted butter 250g', 'available', 12, '2023-12-01'),
(14, 'Grains & Pantry', 'Flour', 4, '2023-10-10', 'Pantry', 'All-purpose flour 2kg', 'available', 12, '2023-09-30'),
(15, 'Snacks & Beverages', 'Chocolate Bar', 6, '2023-12-28', 'Pantry', 'Dark chocolate 70%', 'available', 12, '2023-12-10'),
(16, 'Produce', 'Bananas', 4, '2024-02-03', 'Countertop', 'Ripe yellow bananas', 'used', 12, '2024-01-30'),
(17, 'Protein', 'Tofu', 3, '2024-04-12', 'Fridge', 'Soft soybean tofu', 'available', 12, '2024-03-20'),
(18, 'Dairy & Bakery', 'Yogurt', 5, '2024-05-05', 'Fridge', 'Greek yogurt cup', 'used', 12, '2024-04-25'),
(19, 'Grains & Pantry', 'Pasta', 4, '2024-06-15', 'Pantry', 'Spaghetti pasta 500g', 'available', 12, '2024-06-01'),
(20, 'Snacks & Beverages', 'Chips', 7, '2024-06-20', 'Pantry', 'Potato chips family pack', 'used', 12, '2024-06-10'),
(21, 'Produce', 'Tomatoes', 6, '2024-07-10', 'Fridge', 'Cherry tomatoes pack', 'available', 12, '2024-07-01'),
(22, 'Protein', 'Beef', 4, '2024-08-20', 'Freezer', 'Lean ground beef 500g', 'donation', 12, '2024-08-01'),
(23, 'Dairy & Bakery', 'Bread', 2, '2024-09-10', 'Countertop', 'Wholemeal bread loaf', 'used', 12, '2024-09-05'),
(24, 'Grains & Pantry', 'Cereal', 3, '2024-10-05', 'Pantry', 'Honey oat cereal', 'available', 12, '2024-09-20'),
(25, 'Snacks & Beverages', 'Soda', 6, '2024-10-25', 'Pantry', 'Canned soda drinks', 'available', 12, '2024-10-10'),
(26, 'Produce', 'Spinach', 3, '2024-11-12', 'Fridge', 'Baby spinach leaves', 'used', 12, '2024-11-01'),
(27, 'Protein', 'Sausages', 5, '2024-12-20', 'Freezer', 'Chicken sausages pack', 'donation', 12, '2024-12-01'),
(28, 'Dairy & Bakery', 'Croissant', 4, '2024-12-15', 'Countertop', 'Buttery croissant', 'used', 12, '2024-12-05'),
(29, 'Grains & Pantry', 'Sugar', 4, '2024-11-30', 'Pantry', 'Brown sugar 1kg', 'available', 12, '2024-11-15'),
(30, 'Snacks & Beverages', 'Energy Drink', 8, '2024-12-22', 'Pantry', 'Canned energy drink', 'available', 12, '2024-12-10'),
(31, 'Produce', 'Mangoes', 5, '2025-03-01', 'Countertop', 'Sweet ripe mangoes', 'used', 12, '2025-02-20'),
(32, 'Protein', 'Shrimp', 4, '2025-03-15', 'Freezer', 'Frozen shrimp 300g', 'donation', 12, '2025-03-01'),
(33, 'Dairy & Bakery', 'Cream Cheese', 2, '2025-04-05', 'Fridge', 'Cream cheese tub', 'donation', 12, '2025-03-15'),
(34, 'Grains & Pantry', 'Lentils', 3, '2025-05-25', 'Pantry', 'Red lentils 1kg', 'available', 12, '2025-05-10'),
(35, 'Snacks & Beverages', 'Tea', 2, '2025-06-15', 'Pantry', 'Green tea bags 20pcs', 'available', 12, '2025-06-01'),
(36, 'Produce', 'Oranges', 6, '2025-07-05', 'Countertop', 'Sweet oranges', 'used', 12, '2025-06-20'),
(37, 'Protein', 'Pork Chop', 3, '2025-07-25', 'Freezer', 'Marinated pork chops', 'available', 12, '2025-07-10'),
(38, 'Dairy & Bakery', 'Cream', 2, '2025-08-18', 'Fridge', 'Whipping cream 200ml', 'used', 12, '2025-08-10'),
(39, 'Grains & Pantry', 'Beans', 5, '2025-09-10', 'Pantry', 'Canned baked beans', 'donation', 12, '2025-08-30'),
(40, 'Snacks & Beverages', 'Instant Coffee', 3, '2025-09-25', 'Pantry', 'Instant coffee jar', 'donation', 12, '2025-09-10'),
(41, 'Produce', 'Strawberries', 4, '2025-10-05', 'Fridge', 'Fresh strawberries pack', 'available', 12, '2025-09-28'),
(42, 'Protein', 'Duck Meat', 2, '2025-11-05', 'Freezer', 'Frozen duck breast', 'reserved', 12, '2025-10-25'),
(43, 'Dairy & Bakery', 'Whipped Cream', 1, '2025-11-12', 'Fridge', 'Spray whipped cream', 'donation', 12, '2025-11-01'),
(44, 'Grains & Pantry', 'Bread Crumbs', 3, '2025-12-10', 'Pantry', 'Seasoned bread crumbs', 'donation', 12, '2025-11-25'),
(86, 'Protein', 'Coconuts', 4, '2025-12-26', 'Fridge', '', 'donation', 12, '2025-11-12'),
(88, 'Protein', 'Sprite', 10, '2026-01-01', 'Fridge', '', 'donation', 12, '2025-11-16'),
(89, 'Snacks & Beverages', 'Donuts', 5, '2025-11-19', 'Freezer', '', 'donation', 12, '2025-11-16'),
(90, 'Grains & Pantry', 'Pineapples', 3, '2026-03-05', 'Pantry', '', 'used', 12, '2025-11-16'),
(91, 'Dairy & Bakery', 'Watermelon', 5, '2026-01-23', 'Fridge', '', 'donation', 12, '2025-11-16'),
(94, 'Produce', 'Broccoli', 3, '2025-11-25', 'Fridge', 'Fresh broccoli head', 'donation', 12, '2025-11-18'),
(95, 'Protein', 'Salmon', 2, '2025-11-28', 'Freezer', 'Atlantic salmon fillet', 'donation', 12, '2025-11-18'),
(96, 'Grains & Pantry', 'Quinoa', 1, '2026-01-15', 'Pantry', 'Organic white quinoa', 'donation', 12, '2025-11-18'),
(97, 'Dairy & Bakery', 'Yogurt Drink', 4, '2025-11-23', 'Fridge', 'Low sugar yogurt drink', 'donation', 12, '2025-11-18'),
(98, 'Snacks & Beverages', 'Granola Bar', 6, '2026-02-01', 'Pantry', 'Healthy granola bars', 'donation', 12, '2025-11-18'),
(99, 'Produce', 'Broccoli', 3, '2025-11-25', 'Fridge', 'Fresh broccoli head', 'reserved', 12, '2025-11-18'),
(100, 'Protein', 'Salmon', 2, '2025-11-28', 'Freezer', 'Atlantic salmon fillet', 'reserved', 12, '2025-11-18'),
(101, 'Grains & Pantry', 'Quinoa', 1, '2026-01-15', 'Pantry', 'Organic white quinoa', 'reserved', 12, '2025-11-18'),
(102, 'Dairy & Bakery', 'Yogurt Drink', 4, '2025-11-23', 'Fridge', 'Low sugar yogurt drink', 'reserved', 12, '2025-11-18'),
(103, 'Snacks & Beverages', 'Granola Bar', 6, '2026-02-01', 'Pantry', 'Healthy granola bars', 'reserved', 12, '2025-11-18'),
(108, 'Produce', 'Banana', 1, '2025-11-30', 'Countertop', '', 'reserved', 12, '2025-11-18'),
(113, 'Produce', 'Blueberries', 2, '2024-02-18', 'Fridge', 'Fresh blueberries pack', 'used', 12, '2024-02-10'),
(114, 'Protein', 'Turkey Slices', 483, '2024-03-08', 'Fridge', 'Smoked turkey slices', 'donation', 12, '2024-03-01'),
(115, 'Dairy & Bakery', 'Cream Cheese', 1, '2024-03-25', 'Fridge', 'Spreadable cream cheese', 'used', 12, '2024-03-10'),
(116, 'Grains & Pantry', 'Macaroni', 2, '2024-04-20', 'Pantry', 'Elbow macaroni 500g', 'donation', 12, '2024-04-01'),
(117, 'Snacks & Beverages', 'Green Tea', 2, '2024-04-30', 'Pantry', 'Green tea 20 bags', 'donation', 12, '2024-04-12'),
(118, 'Produce', 'Cabbage', 1, '2024-05-12', 'Fridge', 'Chinese cabbage', 'used', 12, '2024-05-05'),
(119, 'Protein', 'Lamb Chop', 2, '2024-06-02', 'Freezer', 'Marinated lamb chops', 'donation', 12, '2024-05-25'),
(120, 'Dairy & Bakery', 'Pastry Dough', 1, '2024-06-25', 'Fridge', 'Frozen pastry dough', 'donation', 12, '2024-06-15'),
(121, 'Grains & Pantry', 'Corn Flour', 2, '2024-07-30', 'Pantry', 'Fine corn flour 1kg', 'donation', 12, '2024-07-12'),
(122, 'Snacks & Beverages', 'Mixed Nuts', 3, '2024-08-10', 'Pantry', 'Salted mixed nuts', 'used', 12, '2024-08-01'),
(123, 'Produce', 'Papaya', 1, '2024-09-05', 'Countertop', 'Ripe papaya', 'used', 12, '2024-08-29'),
(124, 'Protein', 'Anchovies', 4, '2024-09-22', 'Pantry', 'Dried anchovies pack', 'donation', 12, '2024-09-10'),
(125, 'Dairy & Bakery', 'Butter Roll', 4, '2024-10-02', 'Countertop', 'Soft butter rolls', 'used', 12, '2024-09-28'),
(126, 'Grains & Pantry', 'Couscous', 2, '2024-10-30', 'Pantry', 'Moroccan couscous', 'available', 12, '2024-10-15'),
(127, 'Snacks & Beverages', 'Chocolate Milk', 3, '2024-12-01', 'Fridge', 'Chocolate milk bottle', 'donation', 12, '2024-11-20'),
(128, 'Produce', 'Grapes', 2, '2025-01-10', 'Fridge', 'Seedless grapes', 'used', 12, '2025-01-03'),
(129, 'Protein', 'Ham Slices', 2, '2025-01-12', 'Fridge', 'Honey ham slices', 'donation', 12, '2025-01-04'),
(130, 'Dairy & Bakery', 'Muffins', 4, '2025-01-08', 'Countertop', 'Chocolate muffins', 'used', 12, '2025-01-05'),
(131, 'Grains & Pantry', 'Spices Mix', 1, '2025-02-01', 'Pantry', 'All purpose spice mix', 'available', 12, '2025-01-06'),
(132, 'Snacks & Beverages', 'Iced Tea', 3, '2025-01-18', 'Fridge', 'Bottled iced tea', 'used', 12, '2025-01-07'),
(133, 'Produce', 'Pineapple', 1, '2025-02-14', 'Countertop', 'Local pineapple', 'used', 12, '2025-02-05'),
(134, 'Protein', 'Fish Balls', 2, '2025-02-20', 'Freezer', 'Frozen fish balls', 'donation', 12, '2025-02-06'),
(135, 'Dairy & Bakery', 'Egg Tarts', 3, '2025-02-13', 'Fridge', 'Mini egg tarts', 'used', 12, '2025-02-07'),
(136, 'Grains & Pantry', 'Noodles', 4, '2025-03-01', 'Pantry', 'Dry yellow noodles', 'donation', 12, '2025-02-08'),
(137, 'Snacks & Beverages', 'Mineral Water', 6, '2025-03-20', 'Pantry', 'Bottled mineral water', 'donation', 12, '2025-02-09'),
(138, 'Produce', 'Avocado', 2, '2025-03-10', 'Countertop', 'Ripe avocado', 'used', 12, '2025-03-01'),
(139, 'Protein', 'Crab Sticks', 2, '2025-03-25', 'Freezer', 'Frozen crab sticks', 'donation', 12, '2025-03-02'),
(140, 'Dairy & Bakery', 'Custard', 1, '2025-03-17', 'Fridge', 'Vanilla custard cup', 'used', 12, '2025-03-03'),
(141, 'Grains & Pantry', 'Basmati Rice', 2, '2025-04-15', 'Pantry', 'Premium basmati rice', 'donation', 12, '2025-03-04'),
(142, 'Snacks & Beverages', 'Popcorn', 2, '2025-04-01', 'Pantry', 'Butter popcorn pack', 'donation', 12, '2025-03-05'),
(143, 'Produce', 'Kiwi', 3, '2025-04-12', 'Fridge', 'Green kiwi', 'donation', 12, '2025-04-01'),
(144, 'Protein', 'Chicken Nuggets', 2, '2025-04-28', 'Freezer', 'Frozen nuggets', 'used', 12, '2025-04-02'),
(145, 'Dairy & Bakery', 'Swiss Roll', 1, '2025-04-10', 'Fridge', 'Chocolate swiss roll', 'used', 12, '2025-04-03'),
(146, 'Grains & Pantry', 'Crackers', 2, '2025-05-12', 'Pantry', 'Salted crackers', 'donation', 12, '2025-04-04'),
(147, 'Snacks & Beverages', 'Canned Tea', 4, '2025-05-01', 'Pantry', 'Ready-to-drink tea', 'donation', 12, '2025-04-05'),
(148, 'Produce', 'Cucumber', 3, '2025-05-14', 'Fridge', 'Japanese cucumber', 'used', 12, '2025-05-05'),
(149, 'Protein', 'Meatballs', 2, '2025-05-25', 'Freezer', 'Beef meatballs', 'donation', 12, '2025-05-06'),
(150, 'Dairy & Bakery', 'Pancake Mix', 1, '2025-06-15', 'Pantry', 'Ready pancake mix', 'donation', 12, '2025-05-07'),
(151, 'Grains & Pantry', 'Salt', 1, '2025-07-01', 'Pantry', 'Iodized salt pack', 'donation', 12, '2025-05-08'),
(152, 'Snacks & Beverages', 'Pretzels', 3, '2025-06-30', 'Pantry', 'Salted pretzels', 'used', 12, '2025-05-09'),
(153, 'Produce', 'Potatoes', 5, '2025-06-20', 'Pantry', 'Local potatoes', 'donation', 12, '2025-06-05'),
(154, 'Protein', 'Sardines', 3, '2025-06-30', 'Pantry', 'Canned sardines', 'used', 12, '2025-06-06'),
(155, 'Dairy & Bakery', 'Cupcakes', 4, '2025-06-14', 'Countertop', 'Mini cupcakes', 'used', 12, '2025-06-07'),
(156, 'Grains & Pantry', 'Bread Flour', 2, '2025-07-20', 'Pantry', 'High protein flour', 'donation', 12, '2025-06-08'),
(157, 'Snacks & Beverages', 'Oreo', 2, '2025-07-15', 'Pantry', 'Oreo cookies', 'donation', 12, '2025-06-09'),
(158, 'Produce', 'Starfruit', 2, '2025-07-12', 'Fridge', 'Sweet starfruit', 'used', 12, '2025-07-01'),
(159, 'Protein', 'Beef Balls', 2, '2025-07-25', 'Freezer', 'Frozen beef balls', 'donation', 12, '2025-07-02'),
(160, 'Dairy & Bakery', 'Cream Bun', 3, '2025-07-10', 'Countertop', 'Cream-filled buns', 'used', 12, '2025-07-03'),
(161, 'Grains & Pantry', 'Canned Corn', 2, '2025-08-28', 'Pantry', 'Sweet corn can', 'donation', 12, '2025-07-04'),
(162, 'Snacks & Beverages', 'Peanut Candy', 2, '2025-08-10', 'Pantry', 'Traditional peanut candy', 'donation', 12, '2025-07-05'),
(163, 'Produce', 'Carrots', 12, '2025-01-12', 'Fridge', 'Fresh carrots', 'used', 12, '2025-01-03'),
(164, 'Protein', 'Chicken Thigh', 15, '2025-01-18', 'Freezer', 'Frozen chicken thigh', 'donation', 12, '2025-01-04'),
(165, 'Dairy & Bakery', 'Yogurt', 8, '2025-01-10', 'Fridge', 'Mixed flavor yogurt', 'used', 12, '2025-01-05'),
(166, 'Grains & Pantry', 'Cereal', 10, '2025-02-15', 'Pantry', 'Honey oat cereal', 'donation', 12, '2025-01-06'),
(167, 'Snacks & Beverages', 'Energy Drink', 6, '2025-02-01', 'Fridge', 'Energy drink cans', 'used', 12, '2025-01-07'),
(168, 'Produce', 'Spinach', 10, '2025-02-10', 'Fridge', 'Fresh spinach leaves', 'used', 12, '2025-02-01'),
(169, 'Protein', 'Tofu', 8, '2025-02-14', 'Fridge', 'Firm tofu block', 'donation', 12, '2025-02-03'),
(170, 'Dairy & Bakery', 'Butter', 5, '2025-03-01', 'Fridge', 'Salted butter', 'donation', 12, '2025-02-05'),
(171, 'Grains & Pantry', 'Oatmeal', 12, '2025-04-10', 'Pantry', 'Whole grain oatmeal', 'used', 12, '2025-02-06'),
(172, 'Snacks & Beverages', 'Milk Tea', 6, '2025-02-28', 'Fridge', 'Bottled milk tea', 'used', 12, '2025-02-07'),
(173, 'Produce', 'Mango', 6, '2025-03-15', 'Countertop', 'Ripe mangoes', 'used', 12, '2025-03-05'),
(174, 'Protein', 'Beef Cut', 10, '2025-03-22', 'Freezer', 'Lean beef cuts', 'donation', 12, '2025-03-06'),
(175, 'Dairy & Bakery', 'Creamer', 5, '2025-04-12', 'Pantry', 'Coffee creamer pack', 'donation', 12, '2025-03-07'),
(176, 'Grains & Pantry', 'Rice Noodles', 14, '2025-05-01', 'Pantry', 'Thin rice noodles', 'used', 12, '2025-03-08'),
(177, 'Snacks & Beverages', 'Soda', 12, '2025-04-01', 'Fridge', 'Soda cans variety', 'used', 12, '2025-03-10'),
(178, 'Produce', 'Tomatoes', 9, '2025-04-12', 'Fridge', 'Fresh tomatoes', 'used', 12, '2025-04-03'),
(179, 'Protein', 'Fish Fillet', 10, '2025-04-25', 'Freezer', 'Tilapia fillet', 'donation', 12, '2025-04-04'),
(180, 'Dairy & Bakery', 'Cheese Slice', 8, '2025-04-30', 'Fridge', 'Cheddar cheese slices', 'used', 12, '2025-04-05'),
(181, 'Grains & Pantry', 'Pasta Sauce', 5, '2025-06-01', 'Pantry', 'Tomato pasta sauce', 'donation', 12, '2025-04-06'),
(182, 'Snacks & Beverages', 'Chips', 14, '2025-06-15', 'Pantry', 'Salted potato chips', 'used', 12, '2025-04-07'),
(183, 'Produce', 'Onions', 10, '2025-05-20', 'Pantry', 'Red onions', 'used', 12, '2025-05-05'),
(184, 'Protein', 'Sausages', 12, '2025-05-28', 'Fridge', 'Chicken sausages', 'available', 12, '2025-05-06'),
(185, 'Dairy & Bakery', 'Bread Loaf', 6, '2025-05-14', 'Countertop', 'Wholemeal bread', 'used', 12, '2025-05-07'),
(186, 'Grains & Pantry', 'Flour', 10, '2025-07-01', 'Pantry', 'All-purpose flour', 'donation', 12, '2025-05-08'),
(187, 'Snacks & Beverages', 'Cookies', 15, '2025-06-01', 'Pantry', 'Chocolate cookies', 'used', 12, '2025-05-09'),
(188, 'Produce', 'Broccoli', 7, '2025-06-12', 'Fridge', 'Fresh broccoli', 'donation', 12, '2025-06-04'),
(189, 'Protein', 'Prawns', 11, '2025-06-28', 'Freezer', 'Frozen prawns', 'used', 12, '2025-06-05'),
(190, 'Dairy & Bakery', 'Cream Cake', 15, '2025-06-15', 'Fridge', 'Mini cream cake', 'used', 12, '2025-06-06'),
(191, 'Grains & Pantry', 'Chickpeas', 10, '2025-08-10', 'Pantry', 'Dry chickpeas', 'donation', 12, '2025-06-07'),
(192, 'Snacks & Beverages', 'Juice', 10, '2025-07-02', 'Fridge', 'Apple juice bottles', 'used', 12, '2025-06-08'),
(193, 'Produce', 'Bananas', 12, '2025-07-10', 'Countertop', 'Ripe bananas', 'used', 12, '2025-07-02'),
(194, 'Protein', 'Crab Sticks', 10, '2025-07-20', 'Freezer', 'Frozen crab sticks', 'donation', 12, '2025-07-03'),
(195, 'Dairy & Bakery', 'Buns', 9, '2025-07-05', 'Countertop', 'Cream buns', 'used', 12, '2025-07-04'),
(196, 'Grains & Pantry', 'Vermicelli', 12, '2025-08-25', 'Pantry', 'Vermicelli noodles', 'donation', 12, '2025-07-05'),
(197, 'Snacks & Beverages', 'Peanuts', 14, '2025-08-10', 'Pantry', 'Salted peanuts', 'used', 12, '2025-07-06'),
(198, 'Produce', 'Papaya', 8, '2025-08-12', 'Countertop', 'Local papaya', 'used', 12, '2025-08-03'),
(199, 'Protein', 'Canned Tuna', 10, '2026-01-01', 'Pantry', 'Canned tuna flakes', 'donation', 12, '2025-08-04'),
(200, 'Dairy & Bakery', 'Cupcakes', 15, '2025-08-05', 'Countertop', 'Chocolate cupcakes', 'used', 12, '2025-08-04'),
(201, 'Grains & Pantry', 'Sugar', 10, '2025-10-01', 'Pantry', 'Premium sugar', 'available', 12, '2025-08-05'),
(202, 'Snacks & Beverages', 'Coke', 18, '2025-09-20', 'Fridge', 'Coke cans', 'used', 12, '2025-08-06'),
(203, 'Produce', 'Lettuce', 8, '2025-09-12', 'Fridge', 'Fresh lettuce', 'used', 12, '2025-09-02'),
(204, 'Protein', 'Chicken Wings', 12, '2025-09-25', 'Freezer', 'Frozen wings', 'donation', 12, '2025-09-03'),
(205, 'Dairy & Bakery', 'Milk', 10, '2025-09-14', 'Fridge', 'Fresh milk', 'used', 12, '2025-09-04'),
(206, 'Grains & Pantry', 'Cornflakes', 10, '2025-10-12', 'Pantry', 'Cereal cornflakes', 'available', 12, '2025-09-05'),
(207, 'Snacks & Beverages', 'Tea Bags', 20, '2025-11-01', 'Pantry', 'Black tea bags', 'used', 12, '2025-09-06'),
(208, 'Produce', 'Cucumber', 9, '2025-10-15', 'Fridge', 'Fresh cucumbers', 'used', 12, '2025-10-03'),
(209, 'Protein', 'Fish Balls', 10, '2025-10-22', 'Freezer', 'Frozen fish balls', 'donation', 12, '2025-10-04'),
(210, 'Dairy & Bakery', 'Cheesecake', 12, '2025-10-12', 'Fridge', 'Mini cheesecake box', 'used', 12, '2025-10-05'),
(211, 'Grains & Pantry', 'Instant Noodles', 20, '2026-02-01', 'Pantry', 'Packet instant noodles', 'donation', 12, '2025-10-06'),
(212, 'Snacks & Beverages', 'Chocolate Bar', 15, '2026-01-01', 'Pantry', 'Milk chocolate bars', 'donation', 12, '2025-10-07'),
(213, 'Produce', 'Grapes', 12, '2025-11-15', 'Fridge', 'Red grapes', 'used', 12, '2025-11-02'),
(214, 'Protein', 'Beef Meatballs', 12, '2025-11-28', 'Freezer', 'Frozen beef balls', 'donation', 12, '2025-11-03'),
(215, 'Dairy & Bakery', 'Butter Cookies', 5, '2025-11-12', 'Countertop', 'Butter cookies tin', 'used', 12, '2025-11-04'),
(216, 'Grains & Pantry', 'Biscuits', 3, '2025-12-01', 'Pantry', 'Cream biscuits', 'donation', 12, '2025-11-05'),
(217, 'Snacks & Beverages', 'Energy Bar', 12, '2025-12-10', 'Pantry', 'Protein energy bars', 'used', 12, '2025-11-06');

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
(78, 'Fruit Salad', '', '2025-11-20 00:00:00', '', 'meal_plan', 28, 12),
(79, 'Donuts will expire soon', 'Item \"Donuts\" expires on 2025-11-19', '2025-11-18 21:14:51', 'unread', 'inventory', 89, 12),
(80, 'Broccoli will expire soon', 'Item \"Broccoli\" expires on 2025-11-25', '2025-11-18 21:14:51', 'unread', 'inventory', 94, 12),
(81, 'Yogurt Drink will expire soon', 'Item \"Yogurt Drink\" expires on 2025-11-23', '2025-11-18 21:14:51', 'unread', 'inventory', 97, 12),
(82, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 47, 10),
(83, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 48, 10),
(84, 'Donation created', 'You donated \"\" at ddd', '2025-11-18 21:14:51', 'unread', 'donation', 49, 10),
(85, 'Donation created', 'You donated \"\" at midvalley', '2025-11-18 21:14:51', 'unread', 'donation', 51, 11),
(86, 'Donation created', 'You donated \"\" at midvalley', '2025-11-18 21:14:51', 'unread', 'donation', 52, 11),
(87, 'Donation created', 'You donated \"\" at city kepong', '2025-11-18 21:14:51', 'unread', 'donation', 54, 11),
(88, 'Donation created', 'You donated \"11\" at park', '2025-11-18 21:14:51', 'unread', 'donation', 57, 8),
(89, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 62, 12),
(90, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 63, 12),
(91, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 64, 12),
(92, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 65, 12),
(93, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 66, 12),
(94, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 67, 12),
(95, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 68, 12),
(96, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 69, 12),
(97, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 70, 12),
(98, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 71, 12),
(99, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 72, 12),
(100, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 73, 12),
(101, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 74, 12),
(102, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 75, 12),
(103, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 76, 12),
(104, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 77, 12),
(105, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 78, 12),
(106, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 79, 12),
(107, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 80, 12),
(108, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 81, 12),
(109, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 82, 12),
(110, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 83, 12),
(111, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 84, 12),
(112, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 85, 12),
(113, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 86, 12),
(114, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 87, 12),
(115, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 88, 12),
(116, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 89, 12),
(117, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 90, 12),
(118, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 91, 12),
(119, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 92, 12),
(120, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 93, 12),
(121, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 94, 12),
(122, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 95, 12),
(123, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 96, 12),
(124, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 97, 12),
(125, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 98, 12),
(126, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 99, 12),
(127, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 100, 12),
(128, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 101, 12),
(129, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 102, 12),
(130, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 103, 12),
(131, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 104, 12),
(132, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 105, 12),
(133, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 106, 12),
(134, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 107, 12),
(135, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 108, 12),
(136, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 109, 12),
(137, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 110, 12),
(138, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 111, 12),
(139, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 112, 12),
(140, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 113, 12),
(141, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 114, 12),
(142, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 115, 12),
(143, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 116, 12),
(144, 'Donation created', 'You donated \"\" at ', '2025-11-18 21:14:51', 'unread', 'donation', 117, 12);

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
  MODIFY `donation_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `fooditem`
--
ALTER TABLE `fooditem`
  MODIFY `foodItem_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT for table `mealplan`
--
ALTER TABLE `mealplan`
  MODIFY `mealplan_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

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
