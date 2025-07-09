-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 10, 2025 at 12:22 AM
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
-- Database: `health`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `email`, `password`) VALUES
(2, 'Admin1', 'admin@fitlife.com', '$2y$10$h5XEiLdLxUYETvzZ0Z8hVexT0YyHZGiHcwxNfhUKJJWWqntfgb7AK');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `added_at`) VALUES
(21, 11, 14, 2, '2025-05-02 19:33:47'),
(22, 11, 11, 1, '2025-05-02 19:37:34'),
(23, 11, 15, 2, '2025-05-03 10:21:22');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `coach_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_users` int(11) DEFAULT 0,
  `current_users` int(11) DEFAULT 0,
  `schedule` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`, `description`, `coach_id`, `price`, `max_users`, `current_users`, `schedule`, `created_at`) VALUES
(34, 'CrossFit & Functional Training', 'This high-intensity class blends CrossFit methodologies with functional training to build strength, endurance, and mobility that directly improves everyday movement and sports performance.', 60, 30.00, 30, 11, 'Tuesday 17:38 to 19:38', '2025-05-16 19:42:06'),
(36, 'Weight Loss & Women’s Fitness', 'A dynamic fat-burning session focused on toning arms, legs, and core using light weights, resistance bands, and interval cardio. Low-impact options available. Great for beginners and intermediate members.', 61, 29.00, 30, 11, 'Monday 11:00 to 00:00', '2025-05-16 19:55:55'),
(37, 'Weight Loss & Women’s Fitness', 'A dynamic fat-burning session focused on toning arms, legs, and core using light weights, resistance bands, and interval cardio. Low-impact options available. Great for beginners and intermediate members.\r\n\r\n', 61, 29.00, 30, 5, 'Tuesday 14:30 to 15:30', '2025-05-16 21:32:24'),
(38, 'CrossFit & Functional Training', 'A dynamic fat-burning session focused on toning arms, legs, and core using light weights, resistance bands, and interval cardio. Low-impact options available. Great for beginners and intermediate members.', 60, 30.00, 30, 2, 'Friday 08:30 to 09:30', '2025-05-16 21:34:13'),
(40, 'Confidence Building & Goal Achievement', 'Helps individuals break through mental barriers and unlock their potential. Using a mix of positive psychology, NLP techniques, and accountability strategies, she guides clients in overcoming self-doubt, setting actionable goals, and creating lasting personal and professional growth.', 63, 25.00, 30, 3, 'Thursday 20:45 to 20:45', '2025-05-18 11:43:33'),
(41, 'Strength training', 'This high-performance class focuses on developing total body strength, improving endurance, and mastering proper lifting techniques.', 65, 30.00, 30, 0, 'Saturday 10:30 to 11:30', '2025-05-30 18:30:38'),
(42, 'Muscle building', 'A Muscle Building class is designed to help you increase lean muscle mass, strength, and overall body tone through structured resistance training', 66, 35.00, 30, 0, 'Tuesday 06:00 to 07:00', '2025-05-30 18:39:57'),
(43, 'Suspension, and functional fitness', 'A Suspension and Functional Fitness class combines bodyweight resistance training using suspension straps (like TRX) with functional movements that mimic real-life activities.', 67, 30.00, 30, 0, 'Monday 06:30 to 07:30', '2025-05-30 18:50:18'),
(44, 'EMS (Electro Muscle Stimulation)', 'EMS technology provides efficient workouts aimed at improving strength, endurance, and overall health. Her classes also incorporate nutritional guidance to support clients\' fitness goals.', 68, 25.00, 30, 0, 'Wednesday 14:15 to 15:15', '2025-05-30 18:57:08'),
(45, 'Muay Thai', 'Muay Thai sessions enhance cardiovascular endurance, strength, and self-defense skills and are designed to improve overall fitness and flexibility, with a particular focus on achieving splits. ', 69, 40.00, 30, 0, 'Saturday 16:10 to 17:10', '2025-05-30 19:05:10'),
(46, 'Post-Injury Strength Recovery', 'A safe, progressive program to rebuild strength after injuries (shoulders, knees, or back). Focuses on mobility, stability, and pain-free movement patterns. Requires physician clearance.', 72, 30.00, 25, 0, 'Monday 15:20 to 16:40', '2025-05-31 12:38:23'),
(47, 'Post-Injury Strength Recovery', 'A safe, progressive program to rebuild strength after injuries (shoulders, knees, or back). Focuses on mobility, stability, and pain-free movement patterns. Requires physician clearance.', 0, 30.00, 25, 0, 'Tuesday 17:38 to 19:38, Monday 17:20 to 18:22, Wednesday 18:21 to 20:21', '2025-05-31 12:39:01'),
(48, 'Weight Loss & Women’s Fitness', 'A dynamic fat-burning session focused on toning arms, legs, and core using light weights, resistance bands, and interval cardio. Low-impact options available. Great for beginners and intermediate members.\r\n\r\n', 61, 29.00, 30, 1, 'Friday 14:21 to 15:21', '2025-05-31 13:21:31'),
(49, 'Post-Injury Strength Recovery', 'A safe, progressive program to rebuild strength after injuries (shoulders, knees, or back). Focuses on mobility, stability, and pain-free movement patterns. Requires physician clearance.', 74, 30.00, 25, 0, 'Monday 15:20 to 16:40', '2025-06-01 12:12:51'),
(50, 'Post-Injury Strength Recovery', 'A safe, progressive program to rebuild strength after injuries (shoulders, knees, or back). Focuses on mobility, stability, and pain-free movement patterns. Requires physician clearance.', 74, 30.00, 25, 0, 'Monday 15:20 to 16:40', '2025-06-01 12:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `coach`
--

CREATE TABLE `coach` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coach`
--

INSERT INTO `coach` (`id`, `user_id`, `specialization`, `experience`, `picture`) VALUES
(60, 61, 'CrossFit & Functional Training', 4, 'uploads/682a53400025a_c1.jpeg'),
(61, 62, 'Weight Loss & Women’s Fitness', 2, 'uploads/682a551802cb8_c2.jpeg'),
(62, 66, 'General Fitness', 0, 'default.png'),
(63, 67, 'Confidence Building & Goal Achievement', 1, 'uploads/682a5563db98a_c3.jpeg'),
(65, 71, 'Strength training', 6, 'uploads/6839f8acd5d26_c4.jpeg'),
(66, 72, 'Muscle building', 3, 'uploads/6839fbb8900ba_c5.jpeg'),
(67, 73, 'Suspension, and functional fitness', 2, 'uploads/6839fd1924298_c6.jpeg'),
(68, 74, 'EMS (Electro Muscle Stimulation)', 6, 'uploads/6839ffeb487b5_c7.jpeg'),
(69, 75, 'Muay Thai', 10, 'uploads/683a01d01a26a_c8.jpeg'),
(71, 91, 'ads5', 4, 'uploads/683ad4edaee51_coach.png'),
(72, 93, 'I specialize in weight loss transformations and functional fitness for beginners.', 1, 'uploads/683af3b0e25ba_istockphoto-1444520764-612x612.jpg'),
(73, 95, 'My focus is on therapeutic yoga for stress relief and posture correction.', 5, 'uploads/683afd076c63a_download g(2).jpg'),
(74, 96, 'A safe, progressive program to rebuild strength after injuries (shoulders, knees, or back). Focuses on mobility, stability, and pain-free movement patterns. Requires physician clearance.', 1, 'uploads/683c43239f98a_istockphoto-1444520764-612x612.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('coach','nutrition') NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `receiver_id`, `receiver_type`, `rating`, `comment`, `created_at`) VALUES
(8, 86, 67, 'coach', 4, 'Coach Christian’s workouts are intense and well-structured. I’ve gained a lot of core strength and stability since starting his suspension training sessions. Highly recommended for functional fitness goals!', '2025-05-31 13:43:56'),
(11, 87, 24, 'nutrition', 4, 'Amany gave me a realistic, sustainable meal plan that helped me lose weight without feeling hungry. Her tips on balanced eating completely changed my lifestyle!', '2025-05-31 13:56:28'),
(13, 88, 68, 'coach', 3, 'Maria knows EMS inside out. She explained everything clearly and I felt results after just a few sessions. Definitely a cutting-edge approach to fitness', '2025-05-31 13:57:29'),
(14, 89, 69, 'coach', 3, 'Rana is a powerhouse! Her Muay Thai classes are disciplined, high-energy, and empowering. I’ve gained self-defense skills and mental toughness thanks to her coaching.', '2025-05-31 13:58:03'),
(16, 90, 66, 'coach', 4, 'Lili’s sessions are tough but super effective. I’ve seen real gains in my strength and endurance. Great attention to form and technique.', '2025-05-31 14:00:00'),
(19, 87, 63, 'coach', 3, 'Lea brings such positive energy to every session. Her coaching style helped me stay motivated and believe in myself more. Perfect for beginners who need a confidence boost.', '2025-05-31 14:05:29'),
(20, 86, 61, 'coach', 5, 'Lara is amazing! She understands women’s fitness needs and pushes you without overwhelming you. I’ve lost 4 kg and feel stronger and more confident in just a month!', '2025-05-31 14:06:33'),
(21, 92, 67, 'coach', 4, 'Coach Christian’s workouts are intense and well-structured. I’ve gained a lot of core strength and stability since starting his suspension training sessions. Highly recommended for functional fitness goals!', '2025-05-31 16:36:47'),
(22, 92, 24, 'nutrition', 3, 'Amany gave me a realistic, sustainable meal plan that helped me lose weight without feeling hungry. Her tips on balanced eating completely changed my lifestyle!', '2025-05-31 16:37:13');

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

CREATE TABLE `meals` (
  `id` int(11) NOT NULL,
  `nutritionist_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `day_of_week` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `calories` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`id`, `nutritionist_id`, `name`, `type`, `day_of_week`, `description`, `created_at`, `calories`) VALUES
(57, 22, 'Weight Loss Plan', 'Placeholder', NULL, 'Initial placeholder', '2025-05-17 19:25:31', 0),
(59, 22, 'Weight Loss Plan', 'Breakfast', 'Monday', 'Greek yogurt with berries ', '2025-05-17 19:27:35', 300),
(60, 22, 'Weight Loss Plan', 'Lunch', 'Monday', 'Quinoa salad with grilled chicken ', '2025-05-17 19:28:23', 420),
(61, 22, 'Weight Loss Plan', 'Dinner', 'Monday', 'Grilled Fish with Moutabal', '2025-05-17 19:58:03', 400),
(62, 22, 'Weight Loss Plan', 'Snack', 'Monday', 'Spiced Nuts', '2025-05-17 19:58:30', 50),
(64, 21, 'Performance Nutrition for Athletes', 'Placeholder', NULL, 'Initial placeholder', '2025-05-17 20:02:09', 0),
(65, 21, 'Performance Nutrition for Athletes', 'Breakfast', 'Monday', 'Power Bowl', '2025-05-17 20:05:45', 400),
(66, 21, 'Performance Nutrition for Athletes', 'Lunch', 'Monday', 'Champion\'s Mixed Grill', '2025-05-17 20:06:19', 800),
(67, 21, 'Performance Nutrition for Athletes', 'Dinner', 'Monday', 'Recovery Pudding', '2025-05-17 20:06:58', 300),
(68, 21, 'Performance Nutrition for Athletes', 'Snack', 'Monday', 'Energy Bites', '2025-05-17 20:07:32', 300),
(69, 22, 'Weight Loss Plan', 'Breakfast', 'Tuesday', 'Avocado toast with Poached eggs', '2025-05-17 20:13:54', 350),
(70, 22, 'Weight Loss Plan', 'Lunch', 'Tuesday', 'Lentil soup', '2025-05-17 20:14:41', 380),
(71, 22, 'Weight Loss Plan', 'Dinner', 'Tuesday', 'Vegetable stir-fry with Brown rice', '2025-05-17 20:15:13', 380),
(72, 22, 'Weight Loss Plan', 'Snack', 'Tuesday', 'Protein shake', '2025-05-17 20:15:31', 220),
(73, 22, 'Weight Loss Plan', 'Breakfast', 'Wednesday', 'Oatmeal with almond butter', '2025-05-17 20:15:58', 340),
(74, 22, 'Weight Loss Plan', 'Lunch', 'Wednesday', 'Turkey wrap with Side salad', '2025-05-17 20:16:50', 398),
(75, 22, 'Weight Loss Plan', 'Dinner', 'Wednesday', 'Grilled turkey burgers and Sweet potato fries', '2025-05-17 20:17:11', 400),
(76, 22, 'Weight Loss Plan', 'Snack', 'Wednesday', 'Greek yogurt with nuts on top ', '2025-05-17 20:17:55', 210),
(81, 22, 'Weight Loss Plan', 'Breakfast', 'Thursday', 'Smoothie bowl Granola topping', '2025-05-17 20:24:02', 310),
(82, 22, 'Weight Loss Plan', 'Lunch', 'Thursday', 'Grilled salmon with Steamed vegetables', '2025-05-17 20:24:24', 408),
(83, 22, 'Weight Loss Plan', 'Breakfast', 'Friday', 'Scrambled tofu with Whole grain toast', '2025-05-17 20:26:06', 330),
(84, 23, 'Vegan & Whole-Food Nutrition', 'Placeholder', NULL, 'Initial placeholder', '2025-05-18 12:21:30', 0),
(85, 22, 'Weight Loss Plan', 'Dinner', 'Thursday', 'Stuffed peppers Quinoa filling', '2025-05-18 16:11:51', 370),
(86, 22, 'Weight Loss Plan', 'Snack', 'Thursday', 'Hummus with Vegetable sticks', '2025-05-18 16:12:15', 190),
(87, 22, 'Weight Loss Plan', 'Lunch', 'Friday', 'Chickpea salad and Whole grain pita', '2025-05-18 16:13:23', 390),
(88, 22, 'Weight Loss Plan', 'Dinner', 'Friday', 'Baked chicken and Steamed broccoli', '2025-05-18 16:13:52', 360),
(89, 22, 'Weight Loss Plan', 'Snack', 'Friday', 'Cottage cheese with Berries', '2025-05-18 16:14:18', 180),
(90, 22, 'Weight Loss Plan', 'Breakfast', 'Saturday', 'pancakes and Sugar-free syrup on top ', '2025-05-18 16:17:22', 360),
(91, 22, 'Weight Loss Plan', 'Lunch', 'Saturday', 'Shrimp pasta ', '2025-05-18 16:18:07', 360),
(92, 22, 'Weight Loss Plan', 'Dinner', 'Saturday', 'Grilled steak with vegetables aside ', '2025-05-18 16:18:39', 410),
(93, 22, 'Weight Loss Plan', 'Snack', 'Saturday', 'Dark chocolate and Almond butter', '2025-05-18 16:18:57', 210),
(94, 22, 'Weight Loss Plan', 'Breakfast', 'Sunday', 'Vegetable omelet and Mixed greens', '2025-05-18 16:19:56', 300),
(95, 22, 'Weight Loss Plan', 'Lunch', 'Sunday', 'Grilled chicken salad includes balsamic dressing', '2025-05-18 16:20:25', 370),
(96, 22, 'Weight Loss Plan', 'Dinner', 'Sunday', 'Baked salmon with vegetables aside ', '2025-05-18 16:20:54', 390),
(97, 22, 'Weight Loss Plan', 'Snack', 'Sunday', 'Hard-boiled eggs and Celery sticks', '2025-05-18 16:21:13', 200),
(98, 21, 'Performance Nutrition for Athletes', 'Breakfast', 'Tuesday', 'Protein pancakes with Banana & peanut butter', '2025-05-18 16:24:14', 720),
(99, 21, 'Performance Nutrition for Athletes', 'Lunch', 'Tuesday', 'Beef burger and Sweet potato fries', '2025-05-18 16:24:38', 850),
(100, 21, 'Performance Nutrition for Athletes', 'Dinner', 'Tuesday', 'Baked chicken with rise ', '2025-05-18 16:25:08', 800),
(101, 21, 'Performance Nutrition for Athletes', 'Snack', 'Tuesday', 'Cottage cheese with Peanut butter toast', '2025-05-18 16:26:19', 480),
(102, 21, 'Performance Nutrition for Athletes', 'Breakfast', 'Wednesday', 'Steak & eggs and Hash browns', '2025-05-18 16:27:29', 680),
(103, 21, 'Performance Nutrition for Athletes', 'Lunch', 'Wednesday', 'Salmon fillet and Quinoa & greens', '2025-05-18 16:27:45', 780),
(104, 21, 'Performance Nutrition for Athletes', 'Dinner', 'Wednesday', 'Roasted vegetables', '2025-05-18 16:28:03', 880),
(105, 21, 'Performance Nutrition for Athletes', 'Snack', 'Wednesday', 'Protein bars with Banana', '2025-05-18 16:28:22', 420),
(106, 21, 'Performance Nutrition for Athletes', 'Breakfast', 'Thursday', 'Greek yogurt parfait with Granola & berries', '2025-05-18 16:29:59', 600),
(107, 21, 'Performance Nutrition for Athletes', 'Lunch', 'Thursday', 'Pasta with meat sauce', '2025-05-18 16:30:17', 900),
(108, 21, 'Performance Nutrition for Athletes', 'Dinner', 'Thursday', 'Spaghetti with Turkey meatballs\r\n', '2025-05-18 16:30:45', 920),
(109, 21, 'Performance Nutrition for Athletes', 'Snack', 'Thursday', 'Greek yogurt with Granola & honey', '2025-05-18 16:31:05', 460),
(110, 21, 'Performance Nutrition for Athletes', 'Breakfast', 'Friday', 'Breakfast burrito with Avocado salsa', '2025-05-18 16:31:24', 750),
(111, 21, 'Performance Nutrition for Athletes', 'Lunch', 'Friday', 'Chicken stir-fry with Brown rice\r\n', '2025-05-18 16:31:45', 820),
(112, 21, 'Performance Nutrition for Athletes', 'Dinner', 'Friday', 'Grilled salmon\r\n', '2025-05-18 16:32:02', 860),
(113, 21, 'Performance Nutrition for Athletes', 'Snack', 'Friday', 'Tuna salad and Whole grain crackers', '2025-05-18 16:32:35', 440),
(114, 21, 'Performance Nutrition for Athletes', 'Breakfast', 'Saturday', 'French toast and Turkey ', '2025-05-18 16:37:00', 700),
(115, 21, 'Performance Nutrition for Athletes', 'Lunch', 'Saturday', 'Salmon fillet with potato ', '2025-05-18 16:37:33', 880),
(116, 21, 'Performance Nutrition for Athletes', 'Dinner', 'Saturday', 'Roast beef', '2025-05-18 16:37:54', 980),
(117, 21, 'Performance Nutrition for Athletes', 'Snack', 'Saturday', 'Hard-boiled eggs and Avocado toast', '2025-05-18 16:38:12', 470),
(118, 21, 'Performance Nutrition for Athletes', 'Breakfast', 'Sunday', 'Protein smoothie and Bagel with cream cheese', '2025-05-18 16:38:41', 670),
(119, 21, 'Performance Nutrition for Athletes', 'Lunch', 'Sunday', 'Steak salad with Roasted potatoes', '2025-05-18 16:39:14', 830),
(120, 21, 'Performance Nutrition for Athletes', 'Dinner', 'Sunday', 'BBQ ribs and Cornbread', '2025-05-18 16:39:33', 950),
(121, 21, 'Performance Nutrition for Athletes', 'Snack', 'Sunday', 'Chocolate milk shake\r\n', '2025-05-18 16:39:53', 500),
(122, 23, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Monday', 'Overnight oats and  almond butter ', '2025-05-18 16:42:15', 400),
(123, 23, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Monday', 'Quinoa salad with tahini', '2025-05-18 16:46:12', 450),
(124, 23, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Monday', 'Lentil curry  with rice ', '2025-05-18 16:47:31', 550),
(125, 23, 'Vegan & Whole-Food Nutrition', 'Snack', 'Monday', 'Mixed nuts  and apples', '2025-05-18 16:47:51', 230),
(126, 23, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Tuesday', 'Tofu scramble with toast ', '2025-05-18 16:50:03', 350),
(127, 23, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Tuesday', 'Burrito bowl with salsa', '2025-05-18 16:50:29', 520),
(128, 23, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Tuesday', 'Stir-fry with  quinoa', '2025-05-18 16:50:55', 600),
(129, 23, 'Vegan & Whole-Food Nutrition', 'Snack', 'Tuesday', 'Hummus with Veggies ', '2025-05-18 16:51:18', 150),
(130, 23, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Wednesday', 'Smoothie (banana, spinach, protein)', '2025-05-18 16:52:29', 350),
(131, 23, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Wednesday', 'Lentil soup with  bread ', '2025-05-18 16:52:49', 450),
(132, 23, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Wednesday', 'Stuffed peppers', '2025-05-18 16:54:12', 400),
(133, 23, 'Vegan & Whole-Food Nutrition', 'Snack', 'Wednesday', 'Dark chocolate with almonds', '2025-05-18 16:54:35', 200),
(134, 23, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Thursday', 'Chia pudding with mango', '2025-05-18 16:54:54', 350),
(135, 23, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Thursday', 'Buddha bowl ', '2025-05-18 16:55:12', 500),
(136, 23, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Thursday', 'Lentil Bolognese ', '2025-05-18 16:55:28', 450),
(137, 23, 'Vegan & Whole-Food Nutrition', 'Snack', 'Thursday', 'Edamame ', '2025-05-18 16:55:42', 120),
(138, 23, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Friday', 'Avocado toast with hemp seeds', '2025-05-18 16:56:13', 350),
(139, 23, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Friday', 'Falafel wrap', '2025-05-18 16:56:32', 450),
(140, 23, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Friday', 'Shepherd’s pie', '2025-05-18 16:56:47', 500),
(141, 23, 'Vegan & Whole-Food Nutrition', 'Snack', 'Friday', 'Peanut butter with fruits ', '2025-05-18 16:57:18', 180),
(142, 23, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Saturday', 'Buckwheat pancakes (350) + syrup', '2025-05-18 16:57:45', 400),
(143, 23, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Saturday', 'Sushi rolls', '2025-05-18 16:58:16', 400),
(144, 23, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Saturday', 'Chickpea curry ', '2025-05-18 16:58:39', 500),
(145, 23, 'Vegan & Whole-Food Nutrition', 'Snack', 'Saturday', 'Trail mix', '2025-05-18 16:58:55', 200),
(146, 23, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Sunday', 'Smoothie bowl ', '2025-05-18 16:59:12', 430),
(147, 23, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Sunday', 'Stuffed shells', '2025-05-18 16:59:29', 450),
(148, 23, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Sunday', 'Jackfruit tacos', '2025-05-18 16:59:43', 510),
(149, 23, 'Vegan & Whole-Food Nutrition', 'Snack', 'Sunday', 'Coconut yogurt ', '2025-05-18 17:00:05', 180),
(150, 24, 'Weight Loss & Balanced Eating', 'Placeholder', NULL, 'Initial placeholder', '2025-05-30 20:14:03', 0),
(151, 24, 'Weight Loss & Balanced Eating', 'Breakfast', 'Monday', 'Greek yogurt with berries\r\n', '2025-05-30 20:14:32', 320),
(154, 24, 'Weight Loss & Balanced Eating', 'Lunch', 'Monday', 'Quinoa salad with Grilled chicken', '2025-05-30 20:16:17', 400),
(155, 24, 'Weight Loss & Balanced Eating', 'Dinner', 'Monday', 'Baked cod and Roasted asparagus', '2025-05-30 20:16:51', 350),
(156, 24, 'Weight Loss & Balanced Eating', 'Snack', 'Monday', '\r\nApple slices', '2025-05-30 20:18:23', 220),
(157, 24, 'Weight Loss & Balanced Eating', 'Breakfast', 'Tuesday', 'Avocado toast and Poached eggs', '2025-05-30 20:18:45', 350),
(158, 24, 'Weight Loss & Balanced Eating', 'Lunch', 'Tuesday', 'Lentil soup Whole grain roll', '2025-05-30 20:19:02', 380),
(159, 24, 'Weight Loss & Balanced Eating', 'Dinner', 'Tuesday', 'Vegetable stir-fry with Brown rice', '2025-05-30 20:19:40', 400),
(160, 24, 'Weight Loss & Balanced Eating', 'Snack', 'Tuesday', 'Protein shake', '2025-05-30 20:19:57', 200),
(161, 24, 'Weight Loss & Balanced Eating', 'Breakfast', 'Wednesday', 'Oatmeal with almond butter', '2025-05-30 20:22:49', 340),
(162, 24, 'Weight Loss & Balanced Eating', 'Lunch', 'Wednesday', 'Turkey wrap with Side salad', '2025-05-30 20:23:10', 400),
(163, 24, 'Weight Loss & Balanced Eating', 'Dinner', 'Wednesday', 'Grilled turkey burgers ', '2025-05-30 20:23:30', 400),
(164, 24, 'Weight Loss & Balanced Eating', 'Snack', 'Wednesday', 'Greek yogurt with nuts ', '2025-05-30 20:24:15', 210),
(165, 24, 'Weight Loss & Balanced Eating', 'Breakfast', 'Thursday', 'Smoothie bowl Granola topping', '2025-05-30 20:24:41', 310),
(166, 24, 'Weight Loss & Balanced Eating', 'Lunch', 'Thursday', 'Grilled salmon with Steamed vegetables', '2025-05-30 20:25:00', 410),
(167, 24, 'Weight Loss & Balanced Eating', 'Dinner', 'Thursday', 'Stuffed peppers Quinoa filling', '2025-05-30 20:25:28', 370),
(168, 24, 'Weight Loss & Balanced Eating', 'Snack', 'Thursday', 'Hummus with Vegetable sticks', '2025-05-30 20:28:17', 190),
(169, 24, 'Weight Loss & Balanced Eating', 'Breakfast', 'Friday', 'Whole grain toast with labne ', '2025-05-30 20:31:08', 330),
(170, 24, 'Weight Loss & Balanced Eating', 'Lunch', 'Friday', 'Chickpea salad\r\n', '2025-05-30 20:31:26', 390),
(171, 24, 'Weight Loss & Balanced Eating', 'Dinner', 'Friday', 'Baked chicken with veggies ', '2025-05-30 20:31:48', 360),
(172, 24, 'Weight Loss & Balanced Eating', 'Snack', 'Friday', 'Cottage cheese \r\n ', '2025-05-30 20:32:33', 200),
(173, 24, 'Weight Loss & Balanced Eating', 'Breakfast', 'Saturday', 'Protein pancakes with Sugar-free syrup', '2025-05-30 20:32:57', 280),
(174, 24, 'Weight Loss & Balanced Eating', 'Lunch', 'Saturday', 'Chicken noodles', '2025-05-30 20:33:22', 400),
(175, 24, 'Weight Loss & Balanced Eating', 'Dinner', 'Saturday', 'Grilled steak with potato ', '2025-05-30 20:33:41', 420),
(176, 24, 'Weight Loss & Balanced Eating', 'Snack', 'Saturday', 'Dark chocolate', '2025-05-30 20:33:58', 210),
(177, 24, 'Weight Loss & Balanced Eating', 'Breakfast', 'Sunday', 'Vegetable omelet', '2025-05-30 20:34:17', 300),
(178, 24, 'Weight Loss & Balanced Eating', 'Lunch', 'Sunday', 'Grilled chicken salad with Balsamic dressing', '2025-05-30 20:34:50', 370),
(179, 24, 'Weight Loss & Balanced Eating', 'Dinner', 'Sunday', 'Baked salmon with carrots and potato ', '2025-05-30 20:35:21', 390),
(180, 24, 'Weight Loss & Balanced Eating', 'Snack', 'Sunday', 'Hard-boiled eggs', '2025-05-30 20:35:39', 200),
(181, 25, 'Vegan & Whole-Food Nutrition', 'Placeholder', NULL, 'Initial placeholder', '2025-05-30 20:37:58', 0),
(182, 25, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Monday', 'Ful Medames, Whole Wheat Pita, Mint Tea', '2025-05-30 20:39:57', 450),
(186, 25, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Monday', 'Mujadara, Tabbouleh, Steamed Swiss Chard', '2025-05-30 20:42:04', 600),
(187, 25, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Monday', 'Warak Enab, Lentil Soup, Fatoush', '2025-05-30 20:42:17', 550),
(188, 25, 'Vegan & Whole-Food Nutrition', 'Snack', 'Monday', 'Hummus, Carrot & Cucumber Sticks', '2025-05-30 20:42:31', 250),
(189, 25, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Tuesday', 'Oatmeal with Bananas & Walnuts', '2025-05-30 20:42:43', 400),
(190, 25, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Tuesday', 'Bamia (Okra Stew), Brown Rice, Tomato & Parsley Salad', '2025-05-30 20:42:54', 580),
(191, 25, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Tuesday', 'Freekeh Pilaf, Grilled Zucchini, Lemony Cabbage Slaw', '2025-05-30 20:43:09', 560),
(192, 25, 'Vegan & Whole-Food Nutrition', 'Snack', 'Tuesday', 'Apple, Tahini Dip', '2025-05-30 20:43:28', 230),
(193, 25, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Wednesday', 'Labneh (Coconut-based), Cucumber, Olives, Whole Wheat Manoucheh', '2025-05-30 20:43:55', 460),
(194, 25, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Wednesday', 'Lentil Kibbeh, Fattoush, Roasted Eggplant	', '2025-05-30 20:44:14', 610),
(195, 25, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Wednesday', 'Mloukhieh with Lemon & Onion, Quinoa, Tomato Salad', '2025-05-30 20:44:33', 550),
(196, 25, 'Vegan & Whole-Food Nutrition', 'Snack', 'Wednesday', 'Beet Hummus, Celery Sticks', '2025-05-30 20:44:49', 240),
(197, 25, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Thursday', 'Whole Wheat Kaak, Thyme (Zaatar), Olive Oil', '2025-05-30 20:45:06', 440),
(198, 25, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Thursday', 'Fasolia (Green Bean Stew), Burghul, Minty Cabbage Salad', '2025-05-30 20:45:27', 600),
(199, 25, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Thursday', 'Roasted Cauliflower, Tahini Sauce, Beetroot Salad', '2025-05-30 20:45:44', 530),
(200, 25, 'Vegan & Whole-Food Nutrition', 'Snack', 'Thursday', 'Grapes, Pumpkin Seeds', '2025-05-30 20:46:01', 210),
(201, 25, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Friday', 'Date & Almond Smoothie, Chia Pudding	', '2025-05-30 20:46:16', 420),
(202, 25, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Friday', 'Spinach Stew, Brown Rice, Lentil Salad	', '2025-05-30 20:46:29', 620),
(203, 25, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Friday', 'Grilled Eggplant Rolls, Herb Quinoa, Tomato-Cucumber Salad', '2025-05-30 20:46:47', 540),
(204, 25, 'Vegan & Whole-Food Nutrition', 'Snack', 'Friday', 'Orange, Mixed Nuts', '2025-05-30 20:47:04', 250),
(205, 25, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Saturday', 'Hummus Toast, Tomato Slices, Fresh Thyme Leaves', '2025-05-30 20:47:22', 430),
(206, 25, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Saturday', 'Chickpea & Bulgur Pilaf, Roasted Peppers, Olive Salad', '2025-05-30 20:47:37', 590),
(207, 25, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Saturday', 'Lentil Kofta, Roasted Carrots, Fattoush	', '2025-05-30 20:47:52', 550),
(208, 25, 'Vegan & Whole-Food Nutrition', 'Snack', 'Saturday', 'Pomegranate, Pistachios	', '2025-05-30 20:48:05', 230),
(209, 25, 'Vegan & Whole-Food Nutrition', 'Breakfast', 'Sunday', '0', '2025-05-30 20:48:20', 420),
(210, 25, 'Vegan & Whole-Food Nutrition', 'Lunch', 'Sunday', 'Stuffed Eggplant, Bulgur, Parsley-Tomato Salad', '2025-05-30 20:48:32', 620),
(211, 25, 'Vegan & Whole-Food Nutrition', 'Dinner', 'Sunday', 'Grilled Veggie Platter, Freekeh, Tahini Sauce', '2025-05-30 20:48:52', 540),
(212, 25, 'Vegan & Whole-Food Nutrition', 'Snack', 'Sunday', 'Baked Falafel Balls, Lemon Dip	', '2025-05-30 20:49:07', 250);

-- --------------------------------------------------------

--
-- Table structure for table `nutrition`
--

CREATE TABLE `nutrition` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specification` varchar(100) DEFAULT NULL,
  `experience` int(11) DEFAULT NULL,
  `picture` longblob DEFAULT NULL,
  `schedule` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nutrition`
--

INSERT INTO `nutrition` (`id`, `user_id`, `specification`, `experience`, `picture`, `schedule`) VALUES
(21, 64, 'Performance Nutrition for Athletes', 5, 0x75706c6f6164732f70726f66696c655f36345f313734373630333239332e6a706567, '{\"day\":\"Wednesday\",\"start\":\"17:00\",\"end\":\"18:00\"}'),
(24, 65, 'Weight Loss & Balanced Eating', 4, 0x75706c6f6164732f70726f66696c655f36355f313734373630333439312e6a706567, '{\"day\":\"Monday\",\"start\":\"08:00\",\"end\":\"09:00\"}'),
(33, 79, 'Macrobiotic Diet and Alternative Medicine', 5, 0x75706c6f6164732f70726f66696c655f37395f313734383633383632382e6a706567, '{\"day\":\"Friday\",\"start\":\"17:22\",\"end\":\"18:22\"}'),
(34, 78, 'Nutrition and Diet Therapy', 4, 0x75706c6f6164732f70726f66696c655f37385f313734383633383439352e6a706567, '{\"day\":\"Wednesday\",\"start\":\"14:23\",\"end\":\"16:23\"}'),
(36, 64, 'Performance Nutrition for Athletes', 5, 0x75706c6f6164732f70726f66696c655f36345f313734373630333239332e6a706567, '{\"day\":\"Monday\",\"start\":\"20:00\",\"end\":\"21:00\"}'),
(37, 79, 'Macrobiotic Diet and Alternative Medicine', 5, 0x75706c6f6164732f70726f66696c655f37395f313734383633383632382e6a706567, '{\"day\":\"Friday\",\"start\":\"10:00\",\"end\":\"11:00\"}'),
(39, 94, 'I optimize athletic performance with evidence-based fueling strategies for endurance athletes.', 5, 0x75706c6f6164732f363833616636633432363433335f73706f7274792d6769726c2d656e6a6f79696e672d766567657461626c652d73616c61642d3236306e772d323439333032383131312e77656270, '{\"day\":\"Thursday\",\"start\":\"18:34\",\"end\":\"19:34\"}'),
(41, 97, 'asfa', 3, 0x75706c6f6164732f363833633461663665663333635f696d61676573206e2e6a7067, '{\"day\":\"Monday\",\"start\":\"18:57\",\"end\":\"20:57\"}'),
(42, 98, 'asdfsafsa', 2, 0x75706c6f6164732f363833633531306331333663355f646f776e6c6f616420672832292e6a7067, '{\"status\": \"Not set\"}'),
(43, 98, 'asdfsafsa', 2, 0x75706c6f6164732f363833633531306331333663355f646f776e6c6f616420672832292e6a7067, '{\"day\":\"Monday\",\"start\":\"16:13\",\"end\":\"19:09\"}'),
(44, 64, 'Performance Nutrition for Athletes', 5, 0x75706c6f6164732f70726f66696c655f36345f313734373630333239332e6a706567, '{\"day\":\"Monday\",\"start\":\"14:10\",\"end\":\"15:10\"}');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `purchase_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `user_id`, `product_id`, `quantity`, `total_price`, `purchase_date`) VALUES
(14, 18, 25, 1, 80.00, '2025-05-19 07:41:28'),
(15, 18, 25, 1, 80.00, '2025-05-19 07:43:53'),
(16, 92, 30, 1, 95.00, '2025-05-31 15:33:33'),
(17, 92, 27, 1, 67.00, '2025-05-31 15:33:33'),
(18, 92, 27, 1, 67.00, '2025-05-31 17:25:12'),
(19, 92, 31, 1, 20.00, '2025-05-31 17:25:12'),
(20, 92, 27, 1, 67.00, '2025-05-31 17:26:57'),
(21, 19, 27, 1, 67.00, '2025-06-01 21:45:55'),
(22, 19, 26, 1, 35.00, '2025-06-01 21:45:55'),
(23, 19, 27, 1, 75.00, '2025-06-01 23:54:06'),
(24, 19, 29, 1, 75.00, '2025-06-01 23:54:06'),
(25, 19, 29, 1, 80.00, '2025-06-01 23:59:52'),
(26, 19, 27, 1, 67.00, '2025-06-01 23:59:52'),
(27, 19, 29, 1, 80.00, '2025-06-02 00:00:12'),
(28, 19, 27, 1, 67.00, '2025-06-02 00:00:12'),
(29, 19, 27, 1, 67.00, '2025-06-02 00:00:39'),
(30, 19, 29, 1, 80.00, '2025-06-02 00:00:39'),
(31, 19, 29, 1, 80.00, '2025-06-02 00:05:55'),
(32, 19, 31, 1, 20.00, '2025-06-02 00:05:55'),
(33, 19, 27, 1, 67.00, '2025-06-02 00:08:45'),
(34, 19, 29, 1, 80.00, '2025-06-02 00:08:45'),
(35, 19, 27, 1, 67.00, '2025-06-02 00:10:47'),
(36, 19, 29, 1, 80.00, '2025-06-02 00:10:47');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `coach_id` int(11) DEFAULT NULL,
  `nutritionist_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `type` enum('class','nutrition') NOT NULL,
  `booked_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `status` enum('active','expired','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `user_id`, `coach_id`, `nutritionist_id`, `class_id`, `type`, `booked_at`, `expires_at`, `status`) VALUES
(28, 11, 2, NULL, 5, 'class', '2025-05-01 16:37:26', '2025-05-31 16:37:26', 'active'),
(29, 11, 1, NULL, 4, 'class', '2025-05-01 16:37:30', '2025-05-31 16:37:30', 'active'),
(30, 11, 1, NULL, 12, 'class', '2025-05-01 16:38:01', '2025-05-31 16:38:01', 'active'),
(31, 11, 1, NULL, 13, 'class', '2025-05-01 16:42:05', '2025-05-31 16:42:05', 'active'),
(32, 11, 2, NULL, 16, 'class', '2025-05-02 21:57:40', '2025-06-01 21:57:40', 'active'),
(33, 11, 2, NULL, 24, 'class', '2025-05-02 22:05:57', '2025-06-01 22:05:57', 'active'),
(112, 18, NULL, 25, NULL, 'nutrition', '2025-05-30 23:50:21', '2025-06-12 22:50:21', 'active'),
(113, 87, 61, NULL, 36, 'class', '2025-05-31 14:45:32', '2025-06-30 14:45:32', 'active'),
(115, 87, 0, 33, 0, 'nutrition', '2025-05-31 15:46:00', '2025-06-13 14:46:00', 'active'),
(118, 92, 60, NULL, 34, 'class', '2025-05-31 15:28:18', '2025-06-30 15:28:18', 'active'),
(119, 92, 61, NULL, 36, 'class', '2025-05-31 15:29:23', '2025-06-30 15:29:23', 'active'),
(121, 92, 63, NULL, 40, 'class', '2025-05-31 16:52:18', '2025-06-30 16:52:18', 'active'),
(122, 88, NULL, 24, NULL, 'nutrition', '2025-06-01 15:20:01', '2025-06-14 14:20:01', 'active'),
(123, 21, 61, NULL, 36, 'class', '2025-06-01 14:33:16', '2025-07-01 14:33:16', 'active'),
(124, 21, 61, NULL, 37, 'class', '2025-06-01 14:33:34', '2025-07-01 14:33:34', 'active'),
(131, 23, 61, NULL, 36, 'class', '2025-06-01 17:01:49', '2025-07-01 17:01:49', 'active'),
(132, 23, 60, NULL, 34, 'class', '2025-06-01 17:02:01', '2025-07-01 17:02:01', 'active'),
(133, 23, 61, NULL, 37, 'class', '2025-06-01 17:02:11', '2025-07-01 17:02:11', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `shop`
--

CREATE TABLE `shop` (
  `id` int(11) NOT NULL,
  `productName` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `picturePath` longblob DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shop`
--

INSERT INTO `shop` (`id`, `productName`, `category`, `price`, `picturePath`, `description`) VALUES
(26, 'Pull-Up Bar', 'equipment', 35, 0x696d616765732f70756c6c75702e6a706567, 'This versatile doorway pull-up bar features three grip positions (wide, neutral, and narrow) to target different muscle groups. With a 300-pound weight capacity and no-tool installation, it\'s perfect for home workouts. The tension-mounted design works on most standard doorframes without drilling.'),
(27, 'Gymnastic Rings', 'equipment', 67, 0x696d616765732f72696e67732e6a706567, 'This versatile doorway pull-up bar features three grip positions (wide, neutral, and narrow) to target different muscle groups. With a 300-pound weight capacity and no-tool installation, it\'s perfect for home workouts. The tension-mounted design works on most standard doorframes without drilling.'),
(29, 'Jump Rope ', 'equipment', 80, 0x696d616765732f726f70652e6a706567, 'This versatile doorway pull-up bar features three grip positions (wide, neutral, and narrow) to target different muscle groups. With a 300-pound weight capacity and no-tool installation, it\'s perfect for home workouts. The tension-mounted design works on most standard doorframes without drilling.'),
(30, ' Adjustable Stepper', 'equipment', 95, 0x696d616765732f537465707065722e6a706567, 'These space-saving dumbbells adjust from 5 to 52.5 pounds with a simple dial system, replacing multiple dumbbell sets. '),
(31, 'Loop Bands', 'equipment', 20, 0x696d616765732f62616e642e6a706567, 'This set includes five latex bands ranging from 10 to 50 pounds of resistance. '),
(32, 'Adjustable Dumbbells', 'equipment', 400, 0x696d616765732f64756d2e6a706567, 'These space-saving dumbbells adjust from 5 to 52.5 pounds with a simple dial system, replacing multiple dumbbell sets.'),
(33, 'Stability Ball', 'equipment', 21.97, 0x696d616765732f62616c6c2e6a706567, 'Made from durable PVC material, this exercise ball supports up to 1,000 pounds and comes with a pump for easy inflation. '),
(34, 'Yoga Mat ', 'equipment', 55, 0x696d616765732f796f676d61742e6a7067, 'A yoga mat is a cushioned, non-slip surface designed to provide stability, comfort, and hygiene during yoga, Pilates, or fitness practices. '),
(35, 'Weighted Ball', 'equipment', 90, 0x696d616765732f77656967687462616c6c2e6a7067, 'Versatile fitness tool used for strength training, functional workouts, and rehabilitation. '),
(37, 'Whey Protein', 'supplements', 82, 0x696d616765732f77686579702e6a7067, 'Fast-absorbing protein powder derived from milk, rich in BCAAs for muscle repair.\r\nBest For: Post-workout recovery, muscle growth.'),
(38, 'Creatine Monohydrate', 'supplements', 40, 0x696d616765732f6372656174696e652e6a7067, 'Increases ATP production, boosting strength, endurance, and muscle volume.\r\nBest For: Strength training, explosive power.'),
(39, 'BCAA', 'supplements', 47, 0x696d616765732f424341412e6a7067, 'Branched-Chain Amino Acids (Leucine, Isoleucine, Valine) reduce muscle soreness.\r\nBest For: Endurance athletes, fasted training.'),
(40, ' Omega-3 Fish Oil ', 'supplements', 25, 0x696d616765732f6f6d656761332e6a7067, 'Rich in EPA & DHA, it supports joint mobility and reduces inflammation.\r\nBest For: Overall health, recovery.'),
(41, 'Multivitamin', 'supplements', 37, 0x696d616765732f566974616d696e2e6a7067, 'Essential vitamins/minerals for immune support and energy.\r\nBest For: Gym-goers with dietary gaps.'),
(42, 'Glutamine ', 'supplements', 32, 0x696d616765732f476c7574616d696e652e6a7067, 'Reduces muscle breakdown and supports digestion.\r\nBest for: overtraining recovery.'),
(50, 'Testosterone Booster ', 'supplements', 76, 0x696d616765732f54657374657374652e6a7067, 'Herbal blends (fenugreek, ashwagandha) to support testosterone levels.\r\nBest For: Men over 30, natural muscle growth.'),
(51, 'Protein Bars', 'supplements', 36, 0x696d616765732f626172732e6a7067, 'Chewy, chocolate-packed protein bars for on-the-go snacking.\r\nBest For: Quick protein fix, sweet tooth satisfaction.'),
(52, 'Chocolate Pre-Workout Drink', 'supplements', 58, 0x696d616765732f63686f6370726f2e6a7067, 'Energizing pre-workout powder with caffeine and nitric oxide boosters in chocolate flavor.\r\nBest For: Pre-gym energy without a sugary taste.'),
(53, 'Deep Tissue Massage Gun (6 Attachments)', 'equipment', 35, 0x696d616765732f646f776e6c6f6164202831292e6a7067, 'Professional-grade percussion therapy to relieve sore muscles. Quiet motor, 20-speed settings, and 5-hour battery life. Includes carrying case and demo videos for self-myofascial release.');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','coach','nutritionist') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `age`, `gender`, `email`, `password`, `role`) VALUES
(18, 'User 3', 21, 'Male', 'user3@gmail.com', '$2y$10$sCBN1W/Faf7KoKrcXCoGt.bXJjVikbTupiTdW97tqowURBNInqImK', 'user'),
(19, 'User 4', 30, 'Female', 'user4@gmail.com', '$2y$10$sCBN1W/Faf7KoKrcXCoGt.bXJjVikbTupiTdW97tqowURBNInqImK', 'user'),
(20, 'User 5', 24, 'Male', 'user5@gmail.com', '$2y$10$sCBN1W/Faf7KoKrcXCoGt.bXJjVikbTupiTdW97tqowURBNInqImK', 'user'),
(21, 'User 6', 31, 'Female', 'user6@gmail.com', '$2y$10$sCBN1W/Faf7KoKrcXCoGt.bXJjVikbTupiTdW97tqowURBNInqImK', 'user'),
(22, 'User 7', 22, 'Male', 'user7@gmail.com', '$2y$10$sCBN1W/Faf7KoKrcXCoGt.bXJjVikbTupiTdW97tqowURBNInqImK', 'user'),
(23, 'User 8', 26, 'Female', 'user8@gmail.com', '$2y$10$sCBN1W/Faf7KoKrcXCoGt.bXJjVikbTupiTdW97tqowURBNInqImK', 'user'),
(61, 'Ryan Debs', 18, 'male', 'Ryan1234@gmail.com', '$2y$10$GzXATDZRqM9cfuXH/hfsguh4iQEplRQIXAiA6CqjuVidDLbWieMgu', 'coach'),
(62, 'Lara Khoury', 18, 'female', 'Lara11@gmail.com', '$2y$10$tEmPSWT4b96rQjNtezPHteESCW8iSMgDdkKeW.G3QMWBM6/Y/QOui', 'coach'),
(64, 'Zayn Ahmad', 36, 'Male', 'Zayn44@gmail.com', '$2y$10$aKmR0ToIFpPglUahpfwwie9oCbfN.9DSXoJl9yIvUdO6lZFK0vlpO', 'nutritionist'),
(65, 'Amany Taleb', 34, 'Female', 'Amany99@gmail.com', '$2y$10$B72bRbN.FooMl0Y.T/VgCeb2ktVPo81AZ8SOylDHcLBXZcjKgiHzy', 'nutritionist'),
(67, 'Lea Hatem', 18, 'female', 'Lea88@gmail.com', '$2y$10$7ET8WjcIR6462gV7rpysVeEr7GhcMfYLGn5rrciba/MrFvS5TS0My', 'coach'),
(68, 'Majed Saleh', 26, 'Male', 'Majed00@gmail.com', '$2y$10$A6m7k3qpp6iYnThpX28eY.WFpCcCN.ZI/awIKGrqcUopI7GHVrIdm', 'nutritionist'),
(71, 'Rudy ahmad', 38, 'male', 'Rudy22@gmail.com', '$2y$10$6dYHNuOi5bc3SnUhf/3UlOovHGeBULqBhydbxW6.VvR/jRF301aAO', 'coach'),
(72, 'Lili Dagher', 28, 'female', 'Lili77@gmail.com', '$2y$10$9qdpmrBZtwhvEds90H2P5OymJr1SSuO3NAF2BVn.a3rB36A21y5mm', 'coach'),
(73, 'Christian Ghsoub', 24, 'Male', 'Christian11@gmail.com', '$2y$10$weSDJITodLGldCGp7nCg3.XBVdC44eNnUJkutsfAfT5yjhKOeUmPW', 'coach'),
(74, 'Maria Imad', 31, 'female', 'Maria55@gmail.com', '$2y$10$DPdDBs6myUKGnsVE3oUqY.hR/NHJxAm.DAKos1IzxkUBp9JoWhmL6', 'coach'),
(75, 'Rana Mslmene', 40, 'female', 'Rana33@gmail.com', '$2y$10$/evHkn7AMmF2PgFCUcTR4.dZ0H/1yHv3FxvZn0QsZq6UjfdQvzJVa', 'coach'),
(77, 'Vera Matta', 28, 'Female', 'Vera22@gmail.com', '$2y$10$3XCHUDNnyx3DG48nyNVQ6.UMf/ct./Y8HH.vWj8Wpth6tB8IEBE0G', 'nutritionist'),
(78, 'Vera Matta', 29, 'Female', 'Vera20@gmail.com', '$2y$10$rY0NTMzQKtIMU4GHT3XDP.5CLPJYt07T8YohbDi46xxj8EwrQSfgK', 'nutritionist'),
(79, 'Maya  Nassar', 35, 'Female', 'Maya30@gmail.com', '$2y$10$BrnQ/wxBv/nd15g4jKUpF.qiSCUl7GzAjXUAiFlctWtxSni04LUUe', 'nutritionist'),
(80, 'Hadi Abumrad', 38, 'Male', 'Hadi40@gmail.com', '$2y$10$Oz7e6iWoxIJLJ2NPKZeEBeYJRdCTqYgmZwUYhEVqUU49d5MnLI4GK', 'nutritionist'),
(86, 'Ali Ahmad', 25, 'Male', 'ali@gmail.com', '$2y$10$pM7p/m.cfoA7MWC/.sckEeb7F9yfp3K5KfnVoai.xhpQvYHDn26AC', 'user'),
(87, 'Sara Nasser', 28, 'Female', 'sara@gmail.com', '$2y$10$pM7p/m.cfoA7MWC/.sckEeb7F9yfp3K5KfnVoai.xhpQvYHDn26AC', 'user'),
(88, 'Omar Khaled', 30, 'Male', 'omar@gmail.com', '$2y$10$pM7p/m.cfoA7MWC/.sckEeb7F9yfp3K5KfnVoai.xhpQvYHDn26AC', 'user'),
(89, 'Lina Saad', 22, 'Female', 'lina@gmail.com', '$2y$10$pM7p/m.cfoA7MWC/.sckEeb7F9yfp3K5KfnVoai.xhpQvYHDn26AC', 'user'),
(90, 'Ziad Hammoud', 27, 'Male', 'ziad@gmail.com', '$2y$10$pM7p/m.cfoA7MWC/.sckEeb7F9yfp3K5KfnVoai.xhpQvYHDn26AC', 'user'),
(92, 'Emily Johnson', 25, 'Male', 'emily.johnson@gmail.com', '$2y$10$KmH9iYCwltyIST8X0i1seexoMedQZonksuLyEPyITWxU2U8IHI.Bi', 'user'),
(94, 'Ryan Foster', 28, 'Female', 'ryan.foster.sportsnutri@gmail.com', '$2y$10$TP56edah6WOe/79doUABlOhG5kWjY./cIimk0y77bAHeJE1EzyK96', 'nutritionist'),
(95, 'Sophia Chen', 18, 'female', 'sophia.chen.yoga@gmail.com', '$2y$10$eJxDa6lbCildXdNo3RVveuGR93uzJQTuQ0IQUOwY5xepyQ5e94Wp.', 'coach'),
(96, 'James Wilson', 18, 'male', 'james.wilson.trainer@gmail.com', '$2y$10$4.VVsJ64hqd.yyfFmHfevu7h5AXhr3x6NqJgbd8hHOXCXxjGd8k62', 'coach'),
(97, 'afdfdfs', 44, 'Male', 'dsabads@gmail.com', '$2y$10$4k7HXzme1FPyBQSCCYYVGOFLTsWIC9djTNYq3d3zHm6CZ7sOfPffy', 'nutritionist'),
(98, 'afdf', 33, 'Female', 'asdsdkan@gmail.com', '$2y$10$QjBZMgtvtfCYuGuLqs8LLObt4GuX6UNXj/IrxTIXoW4yNI25plGz2', 'nutritionist');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_class_coach` (`coach_id`);

--
-- Indexes for table `coach`
--
ALTER TABLE `coach`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nutritionist_id` (`nutritionist_id`);

--
-- Indexes for table `nutrition`
--
ALTER TABLE `nutrition`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nutrition_ibfk_1` (`user_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `coach_id` (`coach_id`),
  ADD KEY `nutritionist_id` (`nutritionist_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `shop`
--
ALTER TABLE `shop`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `coach`
--
ALTER TABLE `coach`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213;

--
-- AUTO_INCREMENT for table `nutrition`
--
ALTER TABLE `nutrition`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `shop`
--
ALTER TABLE `shop`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
