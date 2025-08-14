-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 14, 2025 at 05:24 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apartment_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `apartment`
--

CREATE TABLE `apartment` (
  `apartment_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `apartment_name` varchar(100) NOT NULL,
  `location` varchar(150) NOT NULL,
  `landlord_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apartment`
--

INSERT INTO `apartment` (`apartment_id`, `client_id`, `apartment_name`, `location`, `landlord_name`) VALUES
(1, 0, 'Sunrise Apt', 'Thika', 'John Njogu'),
(2, 0, 'Rihlab Apt', 'Nairobi', 'Ann Waithera'),
(3, 0, 'Palace Apt', 'Kisii', 'Peter Kamau'),
(7, 0, 'KEjani Apt', 'Juja', 'GOK');

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_type` enum('tenant','admin') NOT NULL,
  `sender_unit` varchar(50) NOT NULL,
  `receiver_unit` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quoted_message` text DEFAULT NULL,
  `replied_to_msg_id` int(11) DEFAULT NULL,
  `is_answered` tinyint(1) DEFAULT 0,
  `tenant_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_type`, `sender_unit`, `receiver_unit`, `message`, `sent_at`, `quoted_message`, `replied_to_msg_id`, `is_answered`, `tenant_id`, `client_id`) VALUES
(1, 'tenant', 'Sun 1B03', NULL, 'helo', '2025-08-03 15:16:11', NULL, NULL, 0, NULL, NULL),
(2, 'admin', '', 'Sun 1B03', 'Yes,how can we help?', '2025-08-03 15:16:35', 'helo', NULL, 0, NULL, NULL),
(3, 'admin', '', 'Sun 1B03', 'Was your problem solved?', '2025-08-09 05:20:24', 'helo', NULL, 0, NULL, NULL),
(4, 'tenant', 'Sun 1B03', NULL, 'yes thank you', '2025-08-09 05:22:02', NULL, NULL, 0, NULL, NULL),
(5, 'tenant', 'Sun S004', NULL, 'helo', '2025-08-13 19:08:48', NULL, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `client_name`, `contact_email`, `domain`, `status`, `created_at`) VALUES
(2, 'Hentrance Agency', 'grandad@gmail.com', 'hentrance', 'Active', '2025-08-12 13:06:34');

-- --------------------------------------------------------

--
-- Table structure for table `completed_requests`
--

CREATE TABLE `completed_requests` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `house_type` varchar(50) DEFAULT NULL,
  `issue` text DEFAULT NULL,
  `expense_amount` decimal(10,2) DEFAULT NULL,
  `completion_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `completed_requests`
--

INSERT INTO `completed_requests` (`id`, `client_id`, `unit`, `house_type`, `issue`, `expense_amount`, `completion_date`) VALUES
(23, 0, 'Sun S001', NULL, 'plumbing', 8000.00, '2025-08-07');

-- --------------------------------------------------------

--
-- Table structure for table `former_tenants`
--

CREATE TABLE `former_tenants` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `national_id` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `apartment_name` varchar(100) DEFAULT NULL,
  `moved_in_at` datetime DEFAULT NULL,
  `moved_out_at` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Moved Out',
  `apartment_id` int(11) DEFAULT NULL,
  `house_types` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `former_tenants`
--

INSERT INTO `former_tenants` (`id`, `client_id`, `name`, `national_id`, `phone`, `unit`, `apartment_name`, `moved_in_at`, `moved_out_at`, `status`, `apartment_id`, `house_types`) VALUES
(1, 0, 'Meshak', 'MM66556', '0742335264', 'Pal 1B01', 'Sunrise Apt', '2025-07-30 11:20:08', '2025-07-30 12:25:38', 'Moved Out', NULL, NULL),
(2, 0, 'Brianh', 'BB9999', '0742335265', 'Rih 1B02', '', '2025-07-30 11:23:36', '2025-07-30 12:31:30', 'Moved Out', NULL, NULL),
(3, 0, 'Benard', 'BD5544', '0742335263', 'Sun 1B03', '', '2025-08-02 19:33:33', '2025-08-03 17:53:37', 'Moved Out', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `houses`
--

CREATE TABLE `houses` (
  `id` int(11) NOT NULL,
  `apartment_id` int(11) DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `floor` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Vacant',
  `rent` int(11) NOT NULL,
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `houses`
--

INSERT INTO `houses` (`id`, `apartment_id`, `unit`, `floor`, `type_id`, `status`, `rent`, `client_id`) VALUES
(1, 2, 'Rih B001', 1, 25, 'Occupied', 5500, 0),
(2, 3, 'Pal B001', 1, 26, 'Occupied', 4500, 0),
(3, 3, 'Pal S001', 1, 27, 'Vacant', 3000, 0),
(4, 3, 'Pal B002', 1, 26, 'Vacant', 4500, 0),
(5, 3, 'Pal B003', 1, 26, 'Vacant', 4500, 0),
(6, 3, 'Pal B004', 1, 26, 'Occupied', 4500, 0),
(7, 3, 'Pal B005', 1, 26, 'Occupied', 4500, 0),
(8, 2, 'Rih B002', 1, 25, 'Occupied', 5500, 0),
(9, 2, 'Rih B003', 1, 25, 'Vacant', 5500, 0),
(10, 2, 'Rih B004', 1, 25, 'Occupied', 5500, 0),
(11, 2, 'Rih B005', 1, 25, 'Occupied', 5500, 0),
(12, 1, 'Sun B001', 1, 16, 'Vacant', 4500, 0),
(13, 1, 'Sun B002', 1, 16, 'Occupied', 4500, 0),
(14, 1, 'Sun B003', 1, 16, 'Occupied', 4500, 0),
(15, 1, 'Sun B004', 1, 16, 'Occupied', 4500, 0),
(16, 1, 'Sun B005', 1, 16, 'Vacant', 4500, 0),
(17, 1, 'Sun S001', 1, 35, 'Occupied', 2700, 0),
(18, 1, 'Sun S002', 1, 35, 'Vacant', 2700, 0),
(19, 1, 'Sun S003', 1, 35, 'Occupied', 2700, 0),
(20, 1, 'Sun S004', 1, 35, 'Occupied', 2700, 0),
(21, 1, 'Sun S005', 1, 35, 'Vacant', 2700, 0),
(22, 1, 'Sun 1B01', 1, 36, 'Vacant', 6500, 0),
(23, 1, 'Sun 1B02', 1, 36, 'Vacant', 6500, 0),
(24, 1, 'Sun 1B03', 1, 36, 'Occupied', 6500, 0),
(25, 1, 'Sun 1B04', 1, 36, 'Occupied', 6500, 0),
(26, 1, 'Sun 1B05', 1, 36, 'Occupied', 6500, 0),
(27, 1, 'Sun 2B01', 1, 37, 'Vacant', 8000, 0),
(28, 1, 'Sun 2B02', 1, 37, 'Occupied', 8000, 0),
(29, 1, 'Sun 2B03', 1, 37, 'Occupied', 8000, 0),
(30, 1, 'Sun 2B04', 1, 37, 'Vacant', 8000, 0),
(31, 1, 'Sun 2B05', 1, 37, 'Occupied', 8000, 0),
(32, 2, 'Rih S001', 1, 31, 'Vacant', 3500, 0),
(33, 2, 'Rih S002', 1, 31, 'Occupied', 3500, 0),
(34, 2, 'Rih S003', 1, 31, 'Occupied', 3500, 0),
(35, 2, 'Rih S004', 1, 31, 'Occupied', 3500, 0),
(36, 2, 'Rih S005', 1, 31, 'Vacant', 3500, 0),
(37, 2, 'Rih 1B01', 1, 32, 'Vacant', 8000, 0),
(38, 2, 'Rih 1B02', 1, 32, 'Occupied', 8000, 0),
(39, 2, 'Rih 1B03', 1, 32, 'Vacant', 8000, 0),
(40, 2, 'Rih 1B04', 1, 32, 'Occupied', 8000, 0),
(41, 2, 'Rih 1B05', 1, 32, 'Occupied', 8000, 0),
(43, 2, 'Rih 2B01', 1, 34, 'Occupied', 12000, 0),
(44, 2, 'Rih 2B02', 1, 34, 'Occupied', 12000, 0),
(45, 2, 'Rih 2B03', 1, 34, 'Vacant', 12000, 0),
(46, 2, 'Rih 2B04', 1, 34, 'Occupied', 12000, 0),
(47, 2, 'Rih 2B05', 1, 34, 'Vacant', 12000, 0),
(48, 3, 'Pal S002', 1, 27, 'Occupied', 3000, 0),
(49, 3, 'Pal S003', 1, 27, 'Occupied', 3000, 0),
(50, 3, 'Pal S004', 1, 27, 'Occupied', 3000, 0),
(51, 3, 'Pal S005', 1, 27, 'Vacant', 3000, 0),
(52, 3, 'Pal 1B01', 1, 28, 'Occupied', 7500, 0),
(53, 3, 'Pal 1B02', 1, 28, 'Occupied', 7500, 0),
(54, 3, 'Pal 1B03', 1, 28, 'Occupied', 7500, 0),
(55, 3, 'Pal 1B04', 1, 28, 'Occupied', 7500, 0),
(56, 3, 'Pal 1B05', 1, 28, 'Occupied', 7500, 0),
(58, 3, 'Pal 2B01', 1, 29, 'Occupied', 10000, 0),
(59, 3, 'Pal 2B02', 1, 29, 'Occupied', 10000, 0),
(60, 3, 'Pal 2B03', 1, 29, 'Vacant', 10000, 0),
(61, 3, 'Pal 2B04', 1, 29, 'Occupied', 10000, 0),
(62, 3, 'Pal 2B05', 1, 29, 'Vacant', 10000, 0);

-- --------------------------------------------------------

--
-- Table structure for table `house_types`
--

CREATE TABLE `house_types` (
  `id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `default_rent` int(11) NOT NULL,
  `apartment_id` int(11) DEFAULT NULL,
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `house_types`
--

INSERT INTO `house_types` (`id`, `type_name`, `default_rent`, `apartment_id`, `client_id`) VALUES
(16, 'Bedsitter Sun', 4500, 1, 0),
(25, 'Bedsitter Rih', 5500, 2, 0),
(26, 'Bedsitter Pal', 4500, 3, 0),
(27, 'Single Room Pal', 3000, 3, 0),
(28, '1 Bedroom Pal', 7500, 3, 0),
(29, '2 Bedroom Pal', 10000, 3, 0),
(31, 'Single Room Rih', 3500, 2, 0),
(32, '1 Bedroom Rih', 8000, 2, 0),
(34, '2 Bedroom Rih', 12000, 2, 0),
(35, 'Single Room Sun', 2700, 1, 0),
(36, '1 Bedroom Sun', 6500, 1, 0),
(37, '2 Bedroom Sun', 8000, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

CREATE TABLE `maintenance_requests` (
  `id` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `issue` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `request_date` date NOT NULL,
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `tenant_id`, `title`, `message`, `created_at`, `is_read`, `client_id`) VALUES
(19, 40, 'overdue', 'hey pay rent', '2025-07-30 17:33:22', 1, 0),
(20, 43, 'overdue', 'hey pay rent', '2025-07-30 17:33:23', 1, 0),
(21, 43, 'Maintenance Request Received', 'Hi Samson, your maintenance request has been received on 30 Jul 2025 16:43', '2025-07-30 17:43:24', 0, 0),
(22, 45, 'overdue alert', 'your rent is due', '2025-07-31 20:47:51', 1, 0),
(23, 40, 'payment', 'pay rent', '2025-08-01 16:43:47', 0, 0),
(24, 43, 'payment', 'pay rent', '2025-08-01 16:43:47', 0, 0),
(25, 44, 'payment', 'pay rent', '2025-08-01 16:43:47', 0, 0),
(26, 45, 'payment', 'pay rent', '2025-08-01 16:43:47', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `partial_payments`
--

CREATE TABLE `partial_payments` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `rent_expected` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `month` int(11) DEFAULT month(curdate()),
  `year` int(11) DEFAULT year(curdate()),
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partial_payments`
--

INSERT INTO `partial_payments` (`id`, `tenant_id`, `amount`, `balance`, `rent_expected`, `payment_date`, `month`, `year`, `client_id`) VALUES
(1, 45, 5500.00, 2500.00, 8000.00, '2025-08-01 16:15:14', 8, 2025, 0),
(2, 45, 2500.00, 0.00, 8000.00, '2025-08-01 16:15:32', 8, 2025, 0),
(3, 43, 1700.00, 1000.00, 2700.00, '2025-08-01 16:20:26', 8, 2025, 0),
(4, 43, 1000.00, 0.00, 2700.00, '2025-08-01 16:22:48', 8, 2025, 0),
(5, 44, 4000.00, 4000.00, 8000.00, '2025-08-01 16:24:51', 8, 2025, 0),
(6, 44, 4000.00, 0.00, 8000.00, '2025-08-01 16:24:54', 8, 2025, 0),
(7, 46, 8000.00, 4000.00, 12000.00, '2025-08-01 16:41:00', 8, 2025, 0),
(8, 46, 4000.00, 0.00, 12000.00, '2025-08-01 16:41:27', 8, 2025, 0),
(9, 40, 4000.00, 500.00, 4500.00, '2025-08-01 17:18:18', 8, 2025, 0),
(10, 40, 500.00, 0.00, 4500.00, '2025-08-01 17:19:22', 8, 2025, 0),
(11, 47, 2000.00, 1500.00, 3500.00, '2025-08-01 21:36:16', 8, 2025, 0),
(12, 47, 1500.00, 0.00, 3500.00, '2025-08-01 21:36:43', 8, 2025, 0),
(13, 48, 4500.00, 1000.00, 5500.00, '2025-08-02 19:02:12', 8, 2025, 0),
(14, 48, 1000.00, 0.00, 5500.00, '2025-08-02 19:02:30', 8, 2025, 0),
(15, 49, 2500.00, 500.00, 3000.00, '2025-08-02 19:32:08', 8, 2025, 0),
(16, 49, 500.00, 0.00, 3000.00, '2025-08-02 19:32:29', 8, 2025, 0),
(17, 50, 4400.00, 2100.00, 6500.00, '2025-08-02 19:34:13', 8, 2025, 0),
(18, 50, 2100.00, 0.00, 6500.00, '2025-08-02 19:35:51', 8, 2025, 0),
(19, 51, 5000.00, 2500.00, 7500.00, '2025-08-02 19:37:16', 8, 2025, 0),
(20, 51, 2500.00, 0.00, 7500.00, '2025-08-03 11:25:36', 8, 2025, 0),
(21, 52, 5000.00, 1500.00, 6500.00, '2025-08-03 17:56:37', 8, 2025, 0),
(22, 52, 1500.00, 0.00, 6500.00, '2025-08-03 17:57:04', 8, 2025, 0),
(23, 53, 8000.00, 2000.00, 10000.00, '2025-08-04 14:27:14', 8, 2025, 0),
(24, 53, 2000.00, 0.00, 10000.00, '2025-08-04 14:27:26', 8, 2025, 0),
(25, 55, 5700.00, 4300.00, 10000.00, '2025-08-05 07:03:16', 8, 2025, 0),
(26, 55, 4300.00, 0.00, 10000.00, '2025-08-05 07:03:40', 8, 2025, 0),
(27, 57, 5500.00, 1000.00, 6500.00, '2025-08-05 18:43:50', 8, 2025, 0),
(28, 57, 1000.00, 0.00, 6500.00, '2025-08-05 18:43:58', 8, 2025, 0),
(29, 58, 5500.00, 2500.00, 8000.00, '2025-08-06 11:24:02', 8, 2025, 0),
(30, 58, 2500.00, 0.00, 8000.00, '2025-08-06 11:27:33', 8, 2025, 0),
(31, 59, 4000.00, 1500.00, 5500.00, '2025-08-06 12:01:45', 8, 2025, 0),
(32, 59, 1500.00, 0.00, 5500.00, '2025-08-06 12:01:57', 8, 2025, 0),
(33, 60, 500.00, 500.00, 3500.00, '2025-08-06 00:00:00', 8, 2025, 0),
(34, 60, 500.00, 2500.00, 3500.00, '2025-08-06 12:27:48', 8, 2025, 0),
(35, 60, 500.00, 2000.00, 3500.00, '2025-08-06 12:27:52', 8, 2025, 0),
(36, 60, 500.00, 1500.00, 3500.00, '2025-08-06 12:27:58', 8, 2025, 0),
(37, 60, 500.00, 1000.00, 3500.00, '2025-08-06 12:28:02', 8, 2025, 0),
(38, 60, 500.00, 500.00, 3500.00, '2025-08-06 12:28:09', 8, 2025, 0),
(39, 60, 500.00, 0.00, 3500.00, '2025-08-06 12:28:32', 8, 2025, 0),
(44, 62, 3000.00, 3000.00, 4500.00, '2025-08-06 00:00:00', 8, 2025, 0),
(45, 62, 1500.00, 0.00, 4500.00, '2025-08-06 18:20:47', 8, 2025, 0),
(46, 63, 4000.00, 4000.00, 7500.00, '2025-08-08 00:00:00', 8, 2025, 0),
(47, 63, 3500.00, 0.00, 7500.00, '2025-08-08 21:16:56', 8, 2025, 0),
(49, 64, 3500.00, 1000.00, 4500.00, '2025-08-09 07:59:39', 8, 2025, 0),
(50, 64, 1000.00, 0.00, 4500.00, '2025-08-09 08:01:15', 8, 2025, 0),
(70, 68, 2000.00, 1000.00, 3000.00, '2025-08-09 11:30:58', 8, 2025, 0),
(71, 68, 1000.00, 0.00, 3000.00, '2025-08-09 11:31:09', 8, 2025, 0),
(91, 70, 3000.00, 3500.00, 6500.00, '2025-08-09 11:58:22', 8, 2025, 0),
(92, 70, 3000.00, 500.00, 6500.00, '2025-08-09 11:58:25', 8, 2025, 0),
(93, 70, 500.00, 0.00, 6500.00, '2025-08-09 11:58:40', 8, 2025, 0),
(99, 71, 2000.00, 3500.00, 5500.00, '2025-08-09 12:14:16', 8, 2025, 0),
(101, 71, 3500.00, 0.00, 5500.00, '2025-08-09 12:15:17', 8, 2025, 0),
(103, 73, 1500.00, 1500.00, 3000.00, '2025-08-09 00:00:00', 8, 2025, 0),
(104, 73, 1500.00, 0.00, 3000.00, '2025-08-09 12:19:37', 8, 2025, 0),
(106, 75, 5000.00, 3000.00, 8000.00, '2025-08-09 12:24:04', 8, 2025, 0),
(107, 75, 3000.00, 0.00, 8000.00, '2025-08-09 12:24:18', 8, 2025, 0),
(110, 76, 8000.00, 8000.00, 10000.00, '2025-08-09 00:00:00', 8, 2025, 0),
(111, 76, 2000.00, 0.00, 10000.00, '2025-08-09 13:41:22', 8, 2025, 0);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `apartment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Paid','Pending','Failed') DEFAULT 'Paid',
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `tenant_id`, `apartment_id`, `amount`, `payment_date`, `created_at`, `status`, `client_id`) VALUES
(5, 43, NULL, 2700.00, '2025-07-31', '2025-07-30 10:29:36', 'Paid', 0),
(9, 40, NULL, 4500.00, '2025-07-31', '2025-07-31 08:34:16', 'Paid', 0),
(11, 45, NULL, 8000.00, '2025-07-01', '2025-07-31 14:16:49', 'Paid', 0),
(13, 43, NULL, 2700.00, '2025-08-01', '2025-08-01 02:39:10', 'Paid', 0),
(30, 44, NULL, 8000.00, '2025-08-01', '2025-08-01 12:45:06', 'Paid', 0),
(35, 46, NULL, 12000.00, '2025-08-01', '2025-08-01 13:09:57', 'Paid', 0),
(36, 45, NULL, 8000.00, '2025-08-01', '2025-08-01 13:14:12', 'Paid', 0),
(37, 40, NULL, 4500.00, '2025-08-01', '2025-08-01 14:17:37', 'Paid', 0),
(38, 47, NULL, 3500.00, '2025-08-01', '2025-08-01 15:12:48', 'Paid', 0),
(39, 48, NULL, 5500.00, '2025-08-01', '2025-08-01 15:13:47', 'Paid', 0),
(43, 49, NULL, 3000.00, '2025-08-02', '2025-08-02 16:31:52', 'Paid', 0),
(46, 51, NULL, 7500.00, '2025-08-02', '2025-08-02 16:36:53', 'Paid', 0),
(47, 52, NULL, 6500.00, '2025-08-03', '2025-08-03 14:54:21', 'Paid', 0),
(48, 53, NULL, 10000.00, '2025-08-03', '2025-08-03 14:57:55', 'Paid', 0),
(49, 54, NULL, 7500.00, '2025-08-05', '2025-08-04 09:37:25', 'Paid', 0),
(50, 55, NULL, 10000.00, '2025-08-04', '2025-08-04 14:57:11', 'Paid', 0),
(57, 56, NULL, 7500.00, '2025-08-05', '2025-08-05 12:03:34', 'Paid', 0),
(69, 57, NULL, 6500.00, '2025-08-05', '2025-08-05 15:43:50', 'Paid', 0),
(71, 58, NULL, 8000.00, '2025-08-05', '2025-08-05 15:45:09', 'Paid', 0),
(72, 59, NULL, 5500.00, '2025-08-06', '2025-08-06 08:39:50', 'Paid', 0),
(74, 60, NULL, 3500.00, '2025-08-06', '2025-08-06 09:22:27', 'Paid', 0),
(75, 61, NULL, 4500.00, '2025-08-06', '2025-08-06 10:38:17', 'Paid', 0),
(77, 62, NULL, 4500.00, '2025-08-06', '2025-08-06 15:09:36', 'Paid', 0),
(78, 63, NULL, 7500.00, '2025-08-08', '2025-08-08 13:56:51', 'Paid', 0),
(80, 64, NULL, 4500.00, '2025-08-09', '2025-08-09 04:57:40', 'Paid', 0),
(81, 65, NULL, 12000.00, '2025-08-09', '2025-08-09 05:02:38', 'Paid', 0),
(82, 66, NULL, 5500.00, '2025-08-09', '2025-08-09 08:07:00', 'Paid', 0),
(84, 67, NULL, 8000.00, '2025-08-09', '2025-08-09 08:14:46', 'Paid', 0),
(89, 68, NULL, 3000.00, '2025-08-09', '2025-08-09 08:29:18', 'Paid', 0),
(92, 69, NULL, 4500.00, '2025-08-09', '2025-08-09 08:47:34', 'Paid', 0),
(94, 70, NULL, 6500.00, '2025-08-09', '2025-08-09 08:56:08', 'Paid', 0),
(96, 71, NULL, 5500.00, '2025-08-09', '2025-08-09 09:05:18', 'Paid', 0),
(97, 72, NULL, 12000.00, '2025-08-09', '2025-08-09 09:16:14', 'Paid', 0),
(98, 73, NULL, 3000.00, '2025-08-09', '2025-08-09 09:18:47', 'Paid', 0),
(99, 74, NULL, 3500.00, '2025-08-09', '2025-08-09 09:20:46', 'Paid', 0),
(100, 75, NULL, 8000.00, '2025-08-09', '2025-08-09 09:22:59', 'Paid', 0),
(101, 76, NULL, 10000.00, '2025-08-09', '2025-08-09 10:40:17', 'Paid', 0),
(103, 77, NULL, 2700.00, '2025-08-11', '2025-08-11 18:26:08', 'Paid', 0),
(109, 78, NULL, 7500.00, '2025-08-12', '2025-08-12 06:42:17', 'Paid', 0),
(129, 79, NULL, 8000.00, '2025-08-12', '2025-08-12 08:52:41', 'Paid', 0),
(134, 80, NULL, 2700.00, '2025-08-12', '2025-08-12 09:59:33', 'Paid', 0),
(135, 81, NULL, 4500.00, '2025-08-13', '2025-08-13 19:10:49', 'Paid', 0);

-- --------------------------------------------------------

--
-- Table structure for table `prepayments`
--

CREATE TABLE `prepayments` (
  `id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `apartment_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance` decimal(10,2) NOT NULL,
  `date_paid` datetime NOT NULL DEFAULT current_timestamp(),
  `last_deduction` date DEFAULT NULL,
  `status` enum('Active','Exhausted') DEFAULT 'Active',
  `rent_expected` decimal(10,2) NOT NULL DEFAULT 0.00,
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prepayments`
--

INSERT INTO `prepayments` (`id`, `tenant_id`, `apartment_id`, `amount`, `balance`, `date_paid`, `last_deduction`, `status`, `rent_expected`, `client_id`) VALUES
(5, 60, NULL, 500.00, 4000.00, '2025-08-06 00:00:00', '2025-08-06', 'Active', 3500.00, 0),
(6, 61, NULL, 10000.00, 5500.00, '2025-08-06 00:00:00', '2025-08-06', 'Active', 4500.00, 0),
(7, 62, NULL, 1000.00, 5000.00, '2025-08-06 00:00:00', '2025-08-09', 'Active', 4500.00, 0),
(8, 47, NULL, 3500.00, 3500.00, '2025-08-07 00:00:00', '2025-08-07', 'Active', 3500.00, 0),
(10, 63, NULL, 4000.00, 0.00, '2025-08-08 00:00:00', '2025-08-08', 'Active', 7500.00, 0),
(12, 64, NULL, 5500.00, 5500.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 4500.00, 0),
(13, 65, NULL, 25000.00, 13000.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 12000.00, 0),
(14, 66, NULL, 7500.00, 5000.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 5500.00, 0),
(16, 67, NULL, 8000.00, 5000.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 8000.00, 0),
(17, 69, NULL, 5500.00, 5000.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 4500.00, 0),
(18, 70, NULL, 2000.00, 0.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 6500.00, 0),
(20, 71, NULL, 5500.00, 4000.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 5500.00, 0),
(21, 72, NULL, 10000.00, 6000.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 12000.00, 0),
(22, 73, NULL, 1500.00, 0.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 3000.00, 0),
(24, 74, NULL, 3500.00, 5600.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 3500.00, 0),
(25, 76, NULL, 8000.00, 0.00, '2025-08-09 00:00:00', '2025-08-09', 'Active', 10000.00, 0),
(27, 77, NULL, 4400.00, 2700.00, '2025-08-11 00:00:00', '2025-08-11', 'Active', 2700.00, 0),
(30, 78, NULL, 10500.00, 7500.00, '2025-08-12 00:00:00', '2025-08-12', 'Active', 7500.00, 0),
(36, 79, NULL, 12000.00, 6000.00, '2025-08-12 00:00:00', '2025-08-12', 'Active', 8000.00, 0),
(41, 80, NULL, 700.00, 1000.00, '2025-08-12 00:00:00', '2025-08-12', 'Active', 2700.00, 0),
(42, 81, NULL, 2000.00, 1000.00, '2025-08-13 00:00:00', '2025-08-13', 'Active', 4500.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `name`, `contact`, `role`, `date_created`, `client_id`) VALUES
(2, 'Babi', '0700192233', 'System User', '2025-07-05 20:23:53', 0),
(3, 'James', '0700112733', 'Agent', '2025-07-05 20:25:18', 0),
(5, 'Mercy', '0700112233', 'Caretaker', '2025-07-06 10:42:36', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `national_id` varchar(20) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `apartment_name` varchar(100) NOT NULL,
  `status` enum('Active','Pending','Former') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `client_id` int(11) NOT NULL,
  `apartment_id` int(11) DEFAULT NULL,
  `house_type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`id`, `name`, `national_id`, `phone`, `unit`, `apartment_name`, `status`, `created_at`, `client_id`, `apartment_id`, `house_type`) VALUES
(40, 'Prisca', 'PP12345', '0742335263', 'Pal B001', 'Sunrise Apt', 'Active', '2025-07-29 16:16:45', 0, NULL, NULL),
(43, 'Samson', 'SS9988', '0742335260', 'Sun S001', '', 'Active', '2025-07-30 09:00:15', 0, NULL, NULL),
(44, 'Mildred', 'MD5567', '0742335260', 'Sun 2B03', '', 'Active', '2025-07-30 18:16:29', 0, NULL, NULL),
(45, 'Mariah', 'MA5544', '0742335260', 'Rih 1B02', '', 'Active', '2025-07-31 13:43:38', 0, NULL, NULL),
(46, 'Kimtai', 'kk556', '0742335263', 'Rih 2B04', '', 'Active', '2025-08-01 12:46:56', 0, NULL, NULL),
(47, 'Annah', 'AN7878', '0742335260', 'Rih S003', '', 'Active', '2025-08-01 15:12:42', 0, NULL, NULL),
(48, 'Bruce', 'BB7667', '0742335265', 'Rih B004', '', 'Active', '2025-08-01 15:13:43', 0, NULL, NULL),
(49, 'Musa', 'MU4455', '0742335263', 'Pal S003', '', 'Active', '2025-08-02 02:54:16', 0, NULL, NULL),
(51, 'Silas', 'SS564', '0742335263', 'Pal 1B02', '', 'Active', '2025-08-02 16:36:50', 0, NULL, NULL),
(52, 'Lucy', 'LU4455', '0742335263', 'Sun 1B03', '', 'Active', '2025-08-03 14:54:16', 0, NULL, NULL),
(53, 'Moses', 'MO8888', '0742335263', 'Pal 2B02', '', 'Active', '2025-08-03 14:57:49', 0, NULL, NULL),
(54, 'Peter', 'PP3322', '0742335263', 'Pal 1B04', '', 'Active', '2025-08-04 09:36:43', 0, NULL, NULL),
(55, 'Pogba', 'PG4454', '0742335263', 'Pal 2B01', '', 'Active', '2025-08-04 14:57:04', 0, NULL, NULL),
(56, 'Enock', 'EN5454', '0742335260', 'Pal 1B01', '', 'Active', '2025-08-05 10:42:54', 0, NULL, NULL),
(57, 'Mercy', 'MW 6565', '0742335263', 'Sun 1B04', '', 'Active', '2025-08-05 11:24:30', 0, NULL, NULL),
(58, 'Eunice', 'UU7878', '0742335263', 'Rih 1B05', '', 'Active', '2025-08-05 15:44:30', 0, NULL, NULL),
(59, 'Onyango', 'ON8989', '0742335263', 'Rih B002', '', 'Active', '2025-08-06 08:39:45', 0, NULL, NULL),
(60, 'Mwende', 'MW5454', '0742335263', 'Rih S002', '', 'Active', '2025-08-06 09:21:13', 0, NULL, NULL),
(61, 'John', 'JH7878', '0742335263', 'Sun B003', '', 'Active', '2025-08-06 10:38:13', 0, NULL, NULL),
(62, 'Maryline', 'ML453', '0742335264', 'Sun B004', '', 'Active', '2025-08-06 13:56:32', 0, NULL, NULL),
(63, 'Judy', 'JB775', '0742335263', 'Pal 1B03', '', 'Active', '2025-08-08 13:56:47', 0, NULL, NULL),
(64, 'Fridah', 'FD7667', '0742335263', 'Pal B004', '', 'Active', '2025-08-09 04:54:22', 0, NULL, NULL),
(65, 'Jaymoh', 'JM897', '0742335263', 'Rih 2B02', '', 'Active', '2025-08-09 05:02:33', 0, NULL, NULL),
(66, 'Frankline', 'FS453', '0742335264', 'Rih B001', '', 'Active', '2025-08-09 08:06:56', 0, NULL, NULL),
(67, 'Brenda', 'BM897', '0742335260', 'Rih 1B04', '', 'Active', '2025-08-09 08:11:43', 0, NULL, NULL),
(68, 'Alice', 'AA7744', '0742335260', 'Pal S002', '', 'Active', '2025-08-09 08:20:27', 0, NULL, NULL),
(69, 'Meshak', 'MJU787', '0742335263', 'Pal B005', '', 'Active', '2025-08-09 08:33:16', 0, NULL, NULL),
(70, 'Jock', 'JO5674', '0742335264', 'Sun 1B05', '', 'Active', '2025-08-09 08:53:58', 0, NULL, NULL),
(71, 'Pauline', 'PO678', '0742335260', 'Rih B005', '', 'Active', '2025-08-09 08:59:25', 0, NULL, NULL),
(72, 'Mshukiwa', 'MSHY67', '0742335263', 'Rih 2B01', '', 'Active', '2025-08-09 09:16:11', 0, NULL, NULL),
(73, 'Msoo', 'MS98', '0742335260', 'Pal S004', '', 'Active', '2025-08-09 09:18:44', 0, NULL, NULL),
(74, 'Jenifer', 'JEN675', '0742335263', 'Rih S004', '', 'Active', '2025-08-09 09:20:43', 0, NULL, NULL),
(75, 'Mueni', 'MU786', '0742335260', 'Sun 2B02', '', 'Active', '2025-08-09 09:22:56', 0, NULL, NULL),
(76, 'Goefrey', 'GF098', '0742335263', 'Pal 2B04', '', 'Active', '2025-08-09 10:40:12', 0, NULL, NULL),
(77, 'Lawrence', 'LW4566', '0742335264', 'Sun S003', '', 'Active', '2025-08-11 18:22:46', 0, NULL, NULL),
(78, 'Pamela', 'PK7898', '0742335260', 'Pal 1B05', '', 'Active', '2025-08-11 18:30:18', 0, NULL, NULL),
(79, 'Selemani', 'SEL90', '0742335263', 'Sun 2B05', '', 'Active', '2025-08-12 06:46:37', 0, NULL, NULL),
(80, 'Otieno', 'OTI78', '0742335263', 'Sun S004', '', 'Active', '2025-08-12 09:18:59', 0, NULL, NULL),
(81, 'Ruth', 'RUM879', '0742335263', 'Sun B002', '', 'Active', '2025-08-13 19:10:45', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','user') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `client_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `client_id`) VALUES
(4, 'Jock Njoga', '$2y$10$nyP8nfO9DwmNbL31.BqUR.c.EYVAMyjLz26DqMw4Uv.qd8o6REjiy', 'admin', '2025-07-05 21:58:27', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apartment`
--
ALTER TABLE `apartment`
  ADD PRIMARY KEY (`apartment_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `completed_requests`
--
ALTER TABLE `completed_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `former_tenants`
--
ALTER TABLE `former_tenants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `houses`
--
ALTER TABLE `houses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `fk_apartment_id` (`apartment_id`);

--
-- Indexes for table `house_types`
--
ALTER TABLE `house_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_name` (`type_name`),
  ADD UNIQUE KEY `unique_apartment_type` (`type_name`,`apartment_id`);

--
-- Indexes for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `partial_payments`
--
ALTER TABLE `partial_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_tenant` (`tenant_id`);

--
-- Indexes for table `prepayments`
--
ALTER TABLE `prepayments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `fk_prep_apartment` (`apartment_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `contact` (`contact`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apartment`
--
ALTER TABLE `apartment`
  MODIFY `apartment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `completed_requests`
--
ALTER TABLE `completed_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `former_tenants`
--
ALTER TABLE `former_tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `houses`
--
ALTER TABLE `houses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `house_types`
--
ALTER TABLE `house_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `maintenance_requests`
--
ALTER TABLE `maintenance_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `partial_payments`
--
ALTER TABLE `partial_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `prepayments`
--
ALTER TABLE `prepayments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `houses`
--
ALTER TABLE `houses`
  ADD CONSTRAINT `fk_apartment_id` FOREIGN KEY (`apartment_id`) REFERENCES `apartment` (`apartment_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `houses_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `house_types` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prepayments`
--
ALTER TABLE `prepayments`
  ADD CONSTRAINT `fk_prep_apartment` FOREIGN KEY (`apartment_id`) REFERENCES `apartment` (`apartment_id`),
  ADD CONSTRAINT `prepayments_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
