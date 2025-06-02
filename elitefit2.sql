-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 02, 2025 at 01:18 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `elitefit2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
CREATE TABLE IF NOT EXISTS `admin_users` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `first_name`, `last_name`, `email`, `password`, `date_created`) VALUES
(1, 'admin', 'manages', 'admin@gmail.com', '$2y$10$4yDhe3cAceq3DfU9/sWgx.cXCoetBXSKSG4vJgdYzyjGFzu8hyxbG', '2025-04-21 23:01:30');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_managers`
--

DROP TABLE IF EXISTS `equipment_managers`;
CREATE TABLE IF NOT EXISTS `equipment_managers` (
  `manager_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`manager_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `equipment_managers`
--

INSERT INTO `equipment_managers` (`manager_id`, `first_name`, `last_name`, `email`, `password`, `date_created`) VALUES
(2, 'Equipment', 'Boss', 'equipment@gmail.com', '$2y$10$uo4RUI8wPun/X9f5EmJUwuFcX41twJLO/uxrsSsOLwaC5pv5rbl2i', '2025-04-21 21:36:08'),
(3, 'equip', 'ment', 'tools@gmail.com', '$2y$10$3AC/DCCGAOKf8jMmH.wl3ewDHwmzfAX0NHdnKLXJ.AMgP6pU71kJ.', '2025-04-22 12:23:13'),
(4, 'heee', 'yess', 'he1@gmail.com', '$2y$10$yHDK1qUQbIcNiekruJIYLuDysNXvC1ld2dKIN/4ODN3MKKUbJxX9K', '2025-06-02 12:22:52');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_status`
--

DROP TABLE IF EXISTS `equipment_status`;
CREATE TABLE IF NOT EXISTS `equipment_status` (
  `equipment_id` int NOT NULL AUTO_INCREMENT,
  `equipment_name` varchar(100) NOT NULL,
  `status` enum('Available','Maintenance','Out of Service') DEFAULT 'Available',
  `last_maintenance_date` date DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  PRIMARY KEY (`equipment_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `equipment_status`
--

INSERT INTO `equipment_status` (`equipment_id`, `equipment_name`, `status`, `last_maintenance_date`, `next_maintenance_date`) VALUES
(1, 'Treadmill', 'Available', '2025-03-15', '2025-06-15'),
(2, 'Elliptical Machine', 'Available', '2025-04-01', '2025-07-01'),
(3, 'Stationary Bike', 'Available', '2025-03-20', '2025-06-20'),
(4, 'Rowing Machine', 'Maintenance', '2025-01-10', '2025-04-10'),
(5, 'Stair Climber', 'Available', '2025-02-28', '2025-05-28'),
(7, 'Dumbbell Set (5-50 lbs)', 'Available', '2025-03-05', '2025-06-05'),
(8, 'Bench Press', 'Available', '2025-02-15', '2025-05-15'),
(9, 'Squat Rack', 'Available', '2025-03-01', '2025-06-01'),
(10, 'Lat Pulldown Machine', 'Available', '2025-02-20', '2025-05-20'),
(11, 'Chest Press Machine', 'Out of Service', '2024-12-15', '2025-03-15'),
(12, 'Leg Press Machine', 'Available', '2025-01-30', '2025-04-30'),
(13, 'Cable Crossover Machine', 'Available', '2025-03-12', '2025-06-12'),
(14, 'Smith Machine', 'Available', '2025-02-25', '2025-05-25'),
(15, 'Kettlebell Set', 'Available', '2025-03-08', '2025-06-08'),
(16, 'Medicine Balls', 'Available', '2025-02-10', '2025-05-10'),
(17, 'Resistance Bands Set', 'Available', '2025-01-15', '2025-04-15'),
(18, 'TRX Suspension Trainer', 'Available', '2025-03-18', '2025-06-18'),
(19, 'Plyometric Boxes', 'Available', '2025-02-05', '2025-05-05'),
(20, 'Power Rack', 'Available', '2025-01-20', '2025-04-20'),
(21, 'Olympic Weight Set', 'Available', '2025-03-22', '2025-06-22'),
(22, 'Battle Ropes', 'Available', '2025-02-15', '2025-05-15'),
(23, 'Punching Bag', 'Maintenance', '2024-12-01', '2025-03-01'),
(24, 'Foam Rollers', 'Available', '2025-01-10', '2025-04-10');

-- --------------------------------------------------------

--
-- Table structure for table `trainer_assignments`
--

DROP TABLE IF EXISTS `trainer_assignments`;
CREATE TABLE IF NOT EXISTS `trainer_assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `trainer_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`assignment_id`),
  KEY `fk_user` (`user_id`),
  KEY `fk_trainer` (`trainer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `trainer_assignments`
--

INSERT INTO `trainer_assignments` (`assignment_id`, `user_id`, `trainer_id`, `start_date`, `end_date`, `is_active`) VALUES
(1, 13, 4, '0000-00-00', NULL, 1),
(2, 14, 4, '0000-00-00', NULL, 1),
(3, 25, 4, '0000-00-00', NULL, 1),
(4, 12, 4, '0000-00-00', NULL, 1),
(5, 15, 4, '0000-00-00', NULL, 1),
(6, 13, 6, '0000-00-00', NULL, 1),
(7, 14, 6, '0000-00-00', NULL, 1),
(8, 16, 6, '0000-00-00', NULL, 1),
(9, 13, 9, '0000-00-00', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `trainer_details`
--

DROP TABLE IF EXISTS `trainer_details`;
CREATE TABLE IF NOT EXISTS `trainer_details` (
  `trainer_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `certification` varchar(100) NOT NULL,
  `years_of_experience` int NOT NULL,
  `availability` text NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `date_of_birth` date NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `date_registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`trainer_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `trainer_details`
--

INSERT INTO `trainer_details` (`trainer_id`, `first_name`, `last_name`, `email`, `contact_number`, `specialization`, `certification`, `years_of_experience`, `availability`, `gender`, `date_of_birth`, `profile_picture`, `password`, `date_registered`) VALUES
(4, 'juli', 'jo', 'juli@gmail.com', '05554324556', 'Yoga only', 'World cert', 5, 'Monday-Friday 9am-5pm', 'Male', '1998-10-13', '../uploads/trainers/trainer_68056a530e370.jpg', '$2y$10$3YAWmOETFRbIJ0XOtkO2LeAw1hJ9qwZPY16O2EFM7O5HX9ZsbizpG', '2025-04-20 21:42:43'),
(5, 'Trainer', 'obolo', 'trainerobolo@gmail.com', '09877424', 'yoga', 'World cert', 20, 'all week ', 'Male', '2000-06-07', NULL, '$2y$10$IpPZxLaDn6X1zmoWAJWEF.EqeNlMX1jP4FRQaUADMCw8auYHi3jl2', '2025-04-22 12:21:52'),
(6, 'bbb', 'vv', 'juliusyawli@gmail.com', '565667777', 'yoga', 'jhs', 12, 'monday - friday ', 'Male', '1999-09-07', '../uploads/trainers/trainer_6833b92cae7f0.jpg', '$2y$10$KI6omHnqOajBc6PXsm2fjee5qebRyZgBbTFM.Wl/Sp9vmdn2FObEq', '2025-05-26 00:43:24'),
(9, 'he', 'is', 'heyawli@gmail.com', '0531171653', 'yoga', 'BSC', 10, 'all week', 'Male', '1997-05-04', '../uploads/trainers/trainer_683d973cc2fd0.jpg', '$2y$10$C8NgkLJx46.cwpZh.ajXdOlnTAydIc0LGmKT9RXvAYPp04Y.iWoQG', '2025-06-02 12:21:16');

-- --------------------------------------------------------

--
-- Table structure for table `trainer_messages`
--

DROP TABLE IF EXISTS `trainer_messages`;
CREATE TABLE IF NOT EXISTS `trainer_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `trainer_id` int NOT NULL,
  `message_title` varchar(100) NOT NULL,
  `message_content` text NOT NULL,
  `message_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`),
  KEY `user_id` (`user_id`),
  KEY `trainer_id` (`trainer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `trainer_messages`
--

INSERT INTO `trainer_messages` (`message_id`, `user_id`, `trainer_id`, `message_title`, `message_content`, `message_date`, `is_read`) VALUES
(1, 25, 4, 'work', 'today work', '2025-04-27 19:56:02', 0),
(2, 13, 4, 'water', 'come today', '2025-04-28 07:35:37', 0),
(3, 16, 6, 'jo', 'hi', '2025-05-25 19:44:14', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_fitness_details`
--

DROP TABLE IF EXISTS `user_fitness_details`;
CREATE TABLE IF NOT EXISTS `user_fitness_details` (
  `table_id` int NOT NULL AUTO_INCREMENT,
  `user_weight` double NOT NULL,
  `user_height` double NOT NULL,
  `user_bodytype` varchar(30) NOT NULL,
  `preffered_workout_plan_1` int NOT NULL,
  `preffered_workout_plan_2` int NOT NULL,
  `preffered_workout_plan_3` int NOT NULL,
  `fitness_goal_1` text NOT NULL,
  `fitness_goal_2` text NOT NULL,
  `fitness_goal_3` text NOT NULL,
  `experience_level` int NOT NULL,
  `health_condition` int NOT NULL,
  `health_condition_description` text NOT NULL,
  PRIMARY KEY (`table_id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_fitness_details`
--

INSERT INTO `user_fitness_details` (`table_id`, `user_weight`, `user_height`, `user_bodytype`, `preffered_workout_plan_1`, `preffered_workout_plan_2`, `preffered_workout_plan_3`, `fitness_goal_1`, `fitness_goal_2`, `fitness_goal_3`, `experience_level`, `health_condition`, `health_condition_description`) VALUES
(22, 78, 178, 'average', 1, 3, 9, '', '', '', 0, 0, ''),
(23, 200, 180, 'Very big', 1, 4, 6, '', '', '', 0, 0, ''),
(24, 34, 565, 'average', 9, 5, 6, '', '', '', 0, 0, ''),
(25, 90, 120, 'Endomorph', 1, 4, 9, '', '', '', 0, 0, ''),
(26, 100, 100, 'Ectomorph', 9, 6, 8, '', '', '', 0, 0, ''),
(27, 100, 100, 'Ectomorph', 9, 8, 5, '', '', '', 0, 0, ''),
(28, 100, 100, 'Ectomorph', 7, 7, 9, '', '', '', 0, 0, ''),
(29, 50, 100, 'Ectomorph', 3, 9, 6, '', '', '', 0, 0, ''),
(30, 120, 122, 'Ectomorph', 2, 8, 5, '', '', '', 0, 0, ''),
(31, 77, 136, 'Mesomorph', 2, 9, 7, '', '', '', 0, 0, ''),
(32, 88, 150, 'Ectomorph', 1, 3, 7, '', '', '', 0, 0, ''),
(33, 88, 156, 'Ectomorph', 3, 9, 4, '', '', '', 0, 0, ''),
(34, 77, 156, 'Mesomorph', 1, 4, 8, '', '', '', 0, 0, ''),
(35, 88, 154, 'Mesomorph', 1, 5, 9, '', '', '', 0, 0, ''),
(36, 77, 156, 'Ectomorph', 3, 4, 10, '', '', '', 0, 0, ''),
(37, 78, 156, 'Mesomorph', 1, 4, 8, '', '', '', 0, 0, ''),
(38, 78, 159, 'Mesomorph', 1, 4, 8, '', '', '', 0, 0, ''),
(39, 88, 198, 'Mesomorph', 1, 7, 9, '', '', '', 0, 0, ''),
(40, 78, 156, 'Mesomorph', 1, 4, 8, '', '', '', 0, 0, ''),
(41, 56, 123, 'Ectomorph', 3, 9, 5, '', '', '', 0, 0, ''),
(42, 67, 123, 'Ectomorph', 5, 2, 9, '', '', '', 0, 0, ''),
(43, 67, 123, 'Ectomorph', 5, 2, 9, '', '', '', 0, 0, ''),
(44, 56, 123, 'Ectomorph', 1, 3, 8, '', '', '', 0, 0, ''),
(45, 122, 100, 'Ectomorph', 6, 10, 7, '', '', '', 0, 0, ''),
(46, 100, 120, 'Mesomorph', 3, 9, 7, '', '', '', 0, 0, ''),
(47, 100, 120, 'Endomorph', 6, 2, 9, '', '', '', 0, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `user_goals`
--

DROP TABLE IF EXISTS `user_goals`;
CREATE TABLE IF NOT EXISTS `user_goals` (
  `goal_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `goal_type` varchar(50) NOT NULL,
  `target_value` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `target_date` date NOT NULL,
  `is_completed` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`goal_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_login_details`
--

DROP TABLE IF EXISTS `user_login_details`;
CREATE TABLE IF NOT EXISTS `user_login_details` (
  `table_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `user_password` varchar(254) NOT NULL,
  PRIMARY KEY (`table_id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_login_details`
--

INSERT INTO `user_login_details` (`table_id`, `username`, `user_password`) VALUES
(30, 'jypaula81@gmail.com', '$2y$10$7SLliPEfmhn82iCBCblaMOkDtPaqZ963tS.RF.kq5Ikz4rk6ZMj/S'),
(44, 'maxgrip500@gmail.com', '$2y$10$r7nKwasLrma72ehiKomFsejoZfBGxbsNISjocvEJ0jRVythR9/3/C'),
(46, 'agianiwill@gmail.com', '$2y$10$zAekLjnzylNVhu4bmgTNnOtuy.LaeCdrLx9/Rc.GNb.fQELz.V7RG'),
(47, 'harry-johnson.agyemang@rmu.edu.gh', '$2y$10$qFv7hl0P7ZQV66WVkrxYR.Pg3SCoMruZPND6kgFTStwulxLAs6XOC');

-- --------------------------------------------------------

--
-- Table structure for table `user_progress`
--

DROP TABLE IF EXISTS `user_progress`;
CREATE TABLE IF NOT EXISTS `user_progress` (
  `progress_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `date_recorded` date NOT NULL,
  `weight` decimal(5,2) NOT NULL COMMENT 'Weight in kilograms',
  `body_fat_percentage` decimal(4,2) DEFAULT NULL COMMENT 'Body fat percentage',
  `muscle_mass` decimal(5,2) DEFAULT NULL COMMENT 'Muscle mass in kilograms',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`progress_id`),
  KEY `idx_user_date` (`user_id`,`date_recorded`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_progress`
--

INSERT INTO `user_progress` (`progress_id`, `user_id`, `date_recorded`, `weight`, `body_fat_percentage`, `muscle_mass`, `created_at`) VALUES
(1, 25, '2025-04-27', 100.00, 99.99, 67.00, '2025-04-27 23:19:51');

-- --------------------------------------------------------

--
-- Table structure for table `user_register_details`
--

DROP TABLE IF EXISTS `user_register_details`;
CREATE TABLE IF NOT EXISTS `user_register_details` (
  `table_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(40) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `contact_number` text NOT NULL,
  `email` varchar(70) NOT NULL,
  `location` varchar(50) NOT NULL,
  `gender` char(6) NOT NULL,
  `date_of_birth` date NOT NULL,
  `profile_picture` blob NOT NULL,
  `date_of_registration` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_password` varchar(255) NOT NULL,
  `is_verified` tinyint(1) DEFAULT '0',
  `verification_otp` varchar(10) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `reset_otp` varchar(10) DEFAULT NULL,
  `reset_otp_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`table_id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_register_details`
--

INSERT INTO `user_register_details` (`table_id`, `first_name`, `last_name`, `contact_number`, `email`, `location`, `gender`, `date_of_birth`, `profile_picture`, `date_of_registration`, `user_password`, `is_verified`, `verification_otp`, `otp_expiry`, `verification_token`, `created_at`, `updated_at`, `reset_otp`, `reset_otp_expiry`) VALUES
(13, 'Emma', 'Unel', '123409876', 'emma@gmail.com', 'accra', 'Male', '2000-09-29', 0x2e2e2f75706c6f6164732f313734343633323233315f6470322e6a7067, '2025-04-14 07:03:51', 'eafb964b67cc3913213c4ab9a2701c90dae1b48cbcc5f591939616cd89bb4ffb', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(12, 'julius', 'yawli', '05554324556', 'joyce2025t@gmail.com', 'tema', 'Female', '1992-03-22', 0x2e2e2f75706c6f6164732f313734323832323832365f706963747572652e6a7067, '2025-03-24 08:27:06', 'e3b602a529a9e712c47daef6dedd9d24ae9b7125e2886ae8378ca1831894dd16', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(14, 'Emma', 'Unel', '1234098765', 'emma02@gmail.com', 'accra', 'Male', '2000-09-29', 0x2e2e2f75706c6f6164732f313734343633323537355f6470322e6a7067, '2025-04-14 07:09:35', '590264cf7cf159f604bbf7babc7166e4fc712d379a88ca81676c8a0d7b060547', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(15, 'kofi', 'benn', '123409876534', 'benben@gmail.com', 'accra', 'Male', '2000-08-31', 0x2e2e2f75706c6f6164732f313734343633323934305f6470322e6a7067, '2025-04-14 07:15:40', '3681bb851fd12818db7fa32be470390f44344bff6d5875a97c23a10e8fe3d727', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(16, 'rgrg', 'eggeg', '342342424', 'juju@gmail.com', 'df', 'Male', '2000-06-15', 0x2e2e2f75706c6f6164732f313734343730383730355f70702e6a7067, '2025-04-15 04:18:25', '07d16dbe9f53f5a3e2c4e0b8a945f5c107c52052dde47fc84e15fadcbd1758db', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(26, 'user', '1', '0888845', 'paulafike2016@gmail.com', 'tema', 'Female', '0000-00-00', 0x433a5c77616d7036345c7777775c656c6974656669745c72656769737465722f2e2e2f75706c6f6164732f313734363331333833315f70702e6a7067, '2025-05-03 18:10:31', 'e6b6c22ee2cc46d1133f9942cf4bf1e55a1343bc00dc8cbfdfa6681e6b9dd31d', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(18, 'juju', 'vhbh', '09876474', 'jujuju34@gmail.com', 'tema', 'Male', '2001-10-15', 0x2e2e2f75706c6f6164732f313734343732333237335f70702e6a7067, '2025-04-15 08:21:13', 'fdab07b7e93e03e836787b623ee450e2fbf159dab83080e03630997fc7021a4a', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(19, 'bobo', 'bobobo', '3434343', 'hihi@gmail.com', 'da', 'Male', '2002-02-27', 0x2e2e2f75706c6f6164732f313734353137313831365f70702e6a7067, '2025-04-20 12:56:56', '40726aee9dca2aaa51a2f1adbbf134fd139cb1f084d3625577a744403019d642', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(22, 'julius', 'yawli', '03454663444', 'julius@gmail.com', 'accra', 'Male', '1998-06-09', 0x433a5c77616d7036345c7777775c656c6974656669745c72656769737465722f2e2e2f75706c6f6164732f313734353137383437325f70702e6a7067, '2025-04-20 14:47:52', 'e1a6eabfdfb8fc1640320bad9eced0808dca6d85a4b981f93e9c3f015c630f22', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(27, 'Julius', 'Yawli', '0531171643', 'juliusonyawli@gmail.com', 'tema', 'Male', '0000-00-00', 0x433a5c77616d7036345c7777775c656c6974656669745c72656769737465722f2e2e2f75706c6f6164732f313734373233353539365f70702e6a7067, '2025-05-14 10:13:16', '4abed528f57cdf38db78e76ae2230310065266a0f094f8eb2c64d69b497ef546', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(28, 'Julius', 'Yawli', '0531171649', 'juliusyawli@gmail.com', 'tema', 'Male', '0000-00-00', 0x433a5c77616d7036345c7777775c656c6974656669745c72656769737465722f2e2e2f75706c6f6164732f313734373233353838335f70702e6a7067, '2025-05-14 10:18:03', 'c92c62fac1021559998351f7c7baf8eeee026bda6177ef26eac71447687a379b', 0, NULL, NULL, NULL, '2025-05-25 17:13:24', '2025-05-25 17:13:24', NULL, NULL),
(46, 'benten', 'tenben', '054321239', 'agianiwill@gmail.com', 'bnr', 'Male', '1998-09-02', 0x433a5c77616d7036345c7777775c656c697465666974325c72656769737465722f2e2e2f75706c6f6164732f313734383231393337345f70702e6a7067, '2025-05-25 19:29:34', '$2y$10$zAekLjnzylNVhu4bmgTNnOtuy.LaeCdrLx9/Rc.GNb.fQELz.V7RG', 1, NULL, NULL, NULL, '2025-05-26 00:29:34', '2025-05-26 00:30:04', NULL, NULL),
(44, 'Max', 'Grip', '02035678265', 'maxgrip500@gmail.com', 'Ghana', 'Male', '1996-12-30', 0x433a5c77616d7036345c7777775c6669745c72656769737465722f2e2e2f75706c6f6164732f313734383230333431355f576861747341707020496d61676520323032352d30352d31332061742031362e30322e31315f64633534393063622e6a7067, '2025-05-25 20:03:35', '$2y$10$l4/tG3X3gwscawk3DPzX.eQdPazp4YTfLMy./ArwEpX1Q19bYD6Ly', 1, NULL, NULL, NULL, '2025-05-25 20:03:35', '2025-05-25 20:12:18', NULL, NULL),
(47, 'sir', 'harry', 'jdhhfjhdjhf', 'harry-johnson.agyemang@rmu.edu.gh', 'tema', 'Male', '1997-09-03', 0x433a5c77616d7036345c7777775c656c697465666974325c72656769737465722f2e2e2f75706c6f6164732f313734383836363437375f70702e6a7067, '2025-06-02 07:14:37', '$2y$10$qFv7hl0P7ZQV66WVkrxYR.Pg3SCoMruZPND6kgFTStwulxLAs6XOC', 1, NULL, NULL, NULL, '2025-06-02 12:14:37', '2025-06-02 12:15:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_workout_progress`
--

DROP TABLE IF EXISTS `user_workout_progress`;
CREATE TABLE IF NOT EXISTS `user_workout_progress` (
  `progress_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `workout_plan_id` int NOT NULL,
  `current_week` int NOT NULL DEFAULT '1',
  `completed_weeks` varchar(255) DEFAULT '',
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`progress_id`),
  UNIQUE KEY `user_workout` (`user_id`,`workout_plan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_workout_progress`
--

INSERT INTO `user_workout_progress` (`progress_id`, `user_id`, `workout_plan_id`, `current_week`, `completed_weeks`, `last_updated`) VALUES
(1, 25, 1, 2, '1', '2025-04-27 22:13:53'),
(2, 25, 4, 2, '1', '2025-04-27 22:14:28'),
(3, 22, 1, 2, '1', '2025-04-28 12:41:34'),
(4, 46, 3, 2, '1', '2025-05-26 00:37:58');

-- --------------------------------------------------------

--
-- Table structure for table `workout_plan`
--

DROP TABLE IF EXISTS `workout_plan`;
CREATE TABLE IF NOT EXISTS `workout_plan` (
  `table_id` int NOT NULL AUTO_INCREMENT,
  `workout_name` varchar(255) NOT NULL,
  PRIMARY KEY (`table_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workout_plan`
--

INSERT INTO `workout_plan` (`table_id`, `workout_name`) VALUES
(1, 'Strength Training'),
(2, 'Cardio (Aerobic Exercise)'),
(3, 'High-Intensity Interval Training (HIIT)'),
(4, 'Yoga'),
(5, 'Pilates'),
(6, 'CrossFit'),
(7, 'Circuit Training'),
(8, 'Calisthenics'),
(9, 'Martial Arts-Based Workouts'),
(10, 'Dance Workouts');

-- --------------------------------------------------------

--
-- Table structure for table `workout_plan_details`
--

DROP TABLE IF EXISTS `workout_plan_details`;
CREATE TABLE IF NOT EXISTS `workout_plan_details` (
  `detail_id` int NOT NULL AUTO_INCREMENT,
  `workout_plan_id` int NOT NULL,
  `week_number` int NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `duration_weeks` int NOT NULL,
  `goal` text NOT NULL,
  `activities` text NOT NULL,
  PRIMARY KEY (`detail_id`),
  KEY `workout_plan_id` (`workout_plan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workout_plan_details`
--

INSERT INTO `workout_plan_details` (`detail_id`, `workout_plan_id`, `week_number`, `title`, `description`, `duration_weeks`, `goal`, `activities`) VALUES
(1, 1, 1, 'Week 1-2', 'Full-body workouts', 8, 'Build foundational strength, progressive overload', 'Basic compound lifts: squat, deadlift, bench, row 3x/week'),
(2, 1, 3, 'Week 3-4', 'Upper/lower split', 8, 'Build foundational strength, progressive overload', 'Add variations: RDLs, incline bench, pull-ups 4x/week'),
(3, 1, 5, 'Week 5-6', 'Push/pull/legs', 8, 'Build foundational strength, progressive overload', 'Introduce supersets, increase weight intensity 5x/week'),
(4, 1, 7, 'Week 7-8', 'Periodization focus', 8, 'Build foundational strength, progressive overload', '1RM testing, deload, hypertrophy-style finisher sets'),
(5, 2, 1, 'Week 1-2', 'Low-intensity steady-state', 6, 'Build cardiovascular endurance', '20–30 mins LISS cardio, 3–4x/week'),
(6, 2, 3, 'Week 3-4', 'Tempo runs/cycling intervals', 6, 'Build cardiovascular endurance', 'Add tempo runs or cycling intervals 2x/week, LISS 2x/week'),
(7, 2, 5, 'Week 5-6', 'Long-distance sessions', 6, 'Build cardiovascular endurance', 'Mix in long-distance sessions (45–60 mins) with recovery walks on off days'),
(15, 3, 1, 'Week 1-2', 'Beginner HIIT', 6, 'Introduce HIIT concepts, build endurance', '20 sec work / 40 sec rest intervals, 8 rounds, 3x/week (e.g., jumping jacks, bodyweight squats, push-ups)'),
(16, 3, 3, 'Week 3-4', 'Intermediate HIIT', 6, 'Increase intensity and duration', '30 sec work / 30 sec rest intervals, 10 rounds, 3x/week (e.g., burpees, mountain climbers, lunges)'),
(17, 3, 5, 'Week 5-6', 'Advanced HIIT', 6, 'Maximize fat burning and endurance', '40 sec work / 20 sec rest intervals, 12 rounds, 3x/week (e.g., sprints, jump squats, plank variations)'),
(18, 4, 1, 'Week 1-2', 'Foundational Poses', 8, 'Learn basic yoga poses and breathing', '30 min sessions 3x/week: Sun salutations, downward dog, warrior poses, child\'s pose'),
(19, 4, 3, 'Week 3-4', 'Flow Sequences', 8, 'Build strength and flexibility', '45 min sessions 3x/week: Vinyasa flows, balance poses, deeper stretches'),
(20, 4, 5, 'Week 5-6', 'Intermediate Practice', 8, 'Increase challenge and duration', '60 min sessions 3x/week: Arm balances, inversions, longer holds'),
(21, 4, 7, 'Week 7-8', 'Advanced Sequences', 8, 'Master complex poses and meditation', '75 min sessions 3x/week: Advanced flows, meditation, pranayama'),
(22, 5, 1, 'Week 1-2', 'Core Fundamentals', 6, 'Build core strength and stability', '30 min sessions 3x/week: Pelvic tilts, leg circles, single leg stretches'),
(23, 5, 3, 'Week 3-4', 'Full Body Engagement', 6, 'Improve posture and flexibility', '45 min sessions 3x/week: Roll up, single leg kick, side kicks'),
(24, 5, 5, 'Week 5-6', 'Advanced Control', 6, 'Develop precision and endurance', '60 min sessions 3x/week: Teaser, corkscrew, swan dive'),
(25, 6, 1, 'Week 1-2', 'Foundational Movements', 8, 'Learn CrossFit basics', '3x/week: Air squats, push-ups, ring rows, box jumps'),
(26, 6, 3, 'Week 3-4', 'Olympic Lifts Intro', 8, 'Master weightlifting techniques', '3x/week: Clean & jerk, snatch progressions, kettlebell swings'),
(27, 6, 5, 'Week 5-6', 'WOD Implementation', 8, 'Complete timed workouts', '4x/week: AMRAPs, EMOMs, benchmark WODs'),
(28, 6, 7, 'Week 7-8', 'Performance Training', 8, 'Increase intensity and load', '5x/week: Complex lifts, gymnastics skills, endurance WODs'),
(29, 7, 1, 'Week 1-2', 'Basic Circuits', 6, 'Build endurance and strength', '30 sec per station, 8 stations, 3x/week (e.g., squats, push-ups, lunges, planks)'),
(30, 7, 3, 'Week 3-4', 'Timed Circuits', 6, 'Increase intensity', '45 sec per station, 10 stations, 3x/week (add weights and more complex movements)'),
(31, 7, 5, 'Week 5-6', 'Advanced Circuits', 6, 'Maximize calorie burn', '60 sec per station, 12 stations, 4x/week (combine cardio and strength)'),
(32, 8, 1, 'Week 1-2', 'Bodyweight Basics', 8, 'Master fundamental movements', '3x/week: Push-ups, pull-ups, dips, squats, lunges'),
(33, 8, 3, 'Week 3-4', 'Skill Development', 8, 'Learn intermediate skills', '4x/week: Handstand progressions, muscle-up progressions, pistol squats'),
(34, 8, 5, 'Week 5-6', 'Strength Building', 8, 'Increase reps and difficulty', '4x/week: Weighted calisthenics, advanced variations'),
(35, 8, 7, 'Week 7-8', 'Advanced Movements', 8, 'Master complex skills', '5x/week: Planche progressions, front lever, human flag'),
(36, 9, 1, 'Week 1-2', 'Basic Strikes & Stances', 8, 'Learn fundamentals', '3x/week: Jab, cross, front kick, horse stance, footwork drills'),
(37, 9, 3, 'Week 3-4', 'Combinations & Defense', 8, 'Build coordination', '3x/week: Punch-kick combos, blocks, shadow boxing'),
(38, 9, 5, 'Week 5-6', 'Conditioning & Sparring', 8, 'Increase intensity', '4x/week: Pad work, light sparring, defensive drills'),
(39, 9, 7, 'Week 7-8', 'Advanced Techniques', 8, 'Master complex moves', '4x/week: Spinning techniques, takedowns, weapon forms'),
(40, 10, 1, 'Week 1-2', 'Basic Steps & Rhythm', 6, 'Learn foundational moves', '30 min sessions 3x/week: Basic steps, isolations, simple routines'),
(41, 10, 3, 'Week 3-4', 'Combination Building', 6, 'Increase complexity', '45 min sessions 3x/week: Longer sequences, coordination drills'),
(42, 10, 5, 'Week 5-6', 'Full Routines', 6, 'Master complete dances', '60 min sessions 4x/week: Choreographed routines, performance pieces');

-- --------------------------------------------------------

--
-- Table structure for table `workout_sessions`
--

DROP TABLE IF EXISTS `workout_sessions`;
CREATE TABLE IF NOT EXISTS `workout_sessions` (
  `session_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `workout_plan_id` int NOT NULL,
  `week_number` int NOT NULL DEFAULT '1',
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `duration_minutes` int DEFAULT NULL,
  `calories_burned` int DEFAULT NULL,
  `notes` text,
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `workout_plan_id` (`workout_plan_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workout_sessions`
--

INSERT INTO `workout_sessions` (`session_id`, `user_id`, `workout_plan_id`, `week_number`, `start_time`, `end_time`, `duration_minutes`, `calories_burned`, `notes`) VALUES
(3, 22, 1, 3, '2025-04-22 13:11:29', NULL, NULL, NULL, NULL),
(2, 22, 1, 1, '2025-04-22 09:16:31', NULL, NULL, NULL, NULL),
(4, 22, 1, 1, '2025-04-22 13:11:48', NULL, NULL, NULL, NULL),
(5, 22, 1, 1, '2025-04-22 13:18:18', '2025-04-28 12:41:34', 8603, NULL, NULL),
(6, 25, 1, 1, '2025-04-27 22:13:52', '2025-04-27 22:13:53', 0, NULL, NULL),
(7, 25, 4, 1, '2025-04-27 22:14:27', '2025-04-27 22:14:28', 0, NULL, NULL),
(8, 46, 3, 1, '2025-05-26 00:37:52', '2025-05-26 00:37:58', 0, NULL, NULL),
(9, 47, 2, 1, '2025-06-02 12:18:11', NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
