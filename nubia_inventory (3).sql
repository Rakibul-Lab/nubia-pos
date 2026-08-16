-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2026 at 03:51 PM
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
-- Database: `nubia_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `module` varchar(80) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `subject_type` varchar(80) DEFAULT NULL,
  `subject_id` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `module`, `description`, `subject_type`, `subject_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-04 10:08:16'),
(2, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-04 10:15:09'),
(3, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-04 10:25:00'),
(4, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-04 10:40:02'),
(5, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-04 10:40:28'),
(6, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-04 10:41:12'),
(7, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-04 10:41:34'),
(8, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-04 10:41:46'),
(9, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-04 10:48:07'),
(10, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-05 09:44:42'),
(11, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-05 09:45:23'),
(12, NULL, 'login.failed', 'Auth', 'Failed login for purchase@rrpgroup.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 11:22:07'),
(13, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 11:22:26'),
(14, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-07 11:34:05'),
(15, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-07 11:36:15'),
(16, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.8737', '2026-07-07 11:37:10'),
(17, 1, 'sale.created', 'Sales', 'Sale INV-2607-00001 for Tk 64,500.00', 'sale', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 11:44:56'),
(18, 1, 'sale.created', 'Sales', 'Sale INV-2607-00002 for Tk 500.00', 'sale', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 11:46:10'),
(19, 1, 'sale.created', 'Sales', 'Sale INV-2607-00003 for Tk 250.00', 'sale', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', '2026-07-07 11:47:26'),
(20, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 09:55:48'),
(21, 1, 'sale.created', 'Sales', 'Sale INV-2607-00004 for Tk 250.00', 'sale', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 10:04:02'),
(22, 1, 'sale.created', 'Sales', 'Sale INV-2607-00005 for Tk 0.00', 'sale', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 10:11:00'),
(23, 1, 'sale.created', 'Sales', 'Sale INV-2607-00006 for Tk 250.00', 'sale', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 10:17:52'),
(24, 1, 'customer.payment', 'Customers', 'Collected Tk 50.00 from customer #3', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 10:31:35'),
(25, 1, 'sale.created', 'Sales', 'Sale INV-2607-00007 for Tk 280.00', 'sale', 7, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 10:53:09'),
(26, 1, 'sale.exchange', 'Sales', 'Exchange EXC-260711-CCDF4: INV-2607-00006 → INV-2607-00007', 'sale', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 10:53:09'),
(27, 1, 'user.created', 'Users', 'Created user demo@gmail.com', 'user', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:13:42'),
(28, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:13:57'),
(29, 3, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:14:08'),
(30, 3, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:15:26'),
(31, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:15:37'),
(32, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:16:47'),
(33, 3, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:16:49'),
(34, 3, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:17:15'),
(35, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:17:37'),
(36, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:30:15'),
(37, NULL, 'login.failed', 'Auth', 'Failed login for demo@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:30:47'),
(38, 3, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:33:39'),
(39, 3, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:34:15'),
(40, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:34:26'),
(41, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:39:26'),
(42, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:42:21'),
(43, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:42:29'),
(44, NULL, 'login.failed', 'Auth', 'Failed login for admi5n@nubia.test', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:42:42'),
(45, NULL, 'login.failed', 'Auth', 'Failed login for admi5n@nubia.test', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:46:14'),
(46, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 11:46:22'),
(47, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 12:01:39'),
(48, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 12:06:11'),
(49, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 12:16:30'),
(50, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 12:16:37'),
(51, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 12:17:29'),
(52, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 12:18:13'),
(53, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 12:43:15'),
(54, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-11 12:43:27'),
(55, NULL, 'login.failed', 'Auth', 'Failed login for priyoponno@priyoponno.test', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 11:07:21'),
(56, NULL, 'login.failed', 'Auth', 'Failed login for priyoponno@priyoponno.test', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 11:07:23'),
(57, NULL, 'login.failed', 'Auth', 'Failed login for priyoponno.isd@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 11:27:54'),
(58, NULL, 'login.failed', 'Auth', 'Failed login for priyoponno.isd@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 11:28:52'),
(59, NULL, 'login.failed', 'Auth', 'Failed login for priyoponno@priyoponno.test', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 11:29:35'),
(60, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 11:30:33'),
(61, 1, 'logout', 'Auth', 'User signed out', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 11:48:07'),
(62, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-13 11:48:09'),
(63, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 11:39:19'),
(64, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 16:46:51'),
(65, 1, 'product.created', 'Products', 'Created product demo', 'product', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 17:49:06'),
(66, 1, 'product.serials_added', 'Products', 'Added 1 IMEI(s) to demo', 'product', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 17:49:17'),
(67, 1, 'product.serials_added', 'Products', 'Added 1 IMEI(s) to demo', 'product', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 17:49:38'),
(68, 1, 'product.serials_added', 'Products', 'Added 1 IMEI(s) to demo', 'product', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 17:56:02'),
(69, 1, 'sale.created', 'Sales', 'Sale INV-2608-00008 for Tk 6,000.00', 'sale', 8, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 18:56:36'),
(70, 1, 'product.serial_updated', 'Products', 'Changed unit 4547879897 to 4547879897 on demo', 'product', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 19:15:42'),
(71, 1, 'product.serial_updated', 'Products', 'Changed unit 987456321 to 987456322 on demo', 'product', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 19:16:03'),
(72, 1, 'product.serial_updated', 'Products', 'Changed unit 987456322 to 987456322 on demo', 'product', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 19:16:38'),
(73, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168', '2026-08-15 19:26:14'),
(74, 1, 'product.serial_removed', 'Products', 'Removed unit 987456322 from demo', 'product', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 19:32:34'),
(75, 1, 'login', 'Auth', 'User signed in', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.26100.9168', '2026-08-15 19:44:07'),
(76, 1, 'product.serial_removed', 'Products', 'Removed unit 487958635 from demo', 'product', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-15 19:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `logo`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Generic', 'generic', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(2, 'Samsung', 'samsung', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(3, 'Apple', 'apple', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(4, 'Nestle', 'nestle', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(5, 'Unilever', 'unilever', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Electronics', 'electronics', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(2, NULL, 'Groceries', 'groceries', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(3, NULL, 'Fashion', 'fashion', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(4, NULL, 'Pharmacy', 'pharmacy', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(5, 1, 'Mobile Phones', 'mobile-phones', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(6, 1, 'Accessories', 'accessories', NULL, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(160) DEFAULT NULL,
  `company` varchar(160) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `type` enum('retail','wholesale') NOT NULL DEFAULT 'retail',
  `opening_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `credit_limit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `loyalty_points` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `company`, `address`, `city`, `type`, `opening_balance`, `credit_limit`, `loyalty_points`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Walk-in Customer', '000', NULL, NULL, NULL, NULL, 'retail', 0.00, 0.00, 0, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(2, 'Rahim Traders', '01811111111', 'rahim@example.com', NULL, NULL, NULL, 'wholesale', 0.00, 0.00, 0, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(3, 'Karim Store', '01822222222', 'karim@example.com', NULL, NULL, NULL, 'wholesale', 0.00, 0.00, 0, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `direct_transactions`
--

CREATE TABLE `direct_transactions` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `supplier_name` varchar(160) DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(160) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `quantity` decimal(14,2) NOT NULL DEFAULT 1.00,
  `purchase_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `transportation_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `additional_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `add_to_inventory` tinyint(1) NOT NULL DEFAULT 0,
  `payment_method` enum('cash','card','bank','mobile','bkash','nagad','upay','dutch_bangla','credit') NOT NULL DEFAULT 'cash',
  `paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `due` decimal(14,2) NOT NULL DEFAULT 0.00,
  `purchase_id` int(10) UNSIGNED DEFAULT NULL,
  `sale_id` int(10) UNSIGNED DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `warehouse_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('cash','card','bank','mobile','bkash','nagad','upay','dutch_bangla') NOT NULL DEFAULT 'cash',
  `expense_date` date NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `name`, `status`, `created_at`) VALUES
(1, 'Salary', 1, '2026-07-04 10:05:31'),
(2, 'Electricity', 1, '2026-07-04 10:05:31'),
(3, 'Internet', 1, '2026-07-04 10:05:31'),
(4, 'Transport', 1, '2026-07-04 10:05:31'),
(5, 'Office', 1, '2026-07-04 10:05:31'),
(6, 'Marketing', 1, '2026-07-04 10:05:31'),
(7, 'Rent', 1, '2026-07-04 10:05:31'),
(8, 'Miscellaneous', 1, '2026-07-04 10:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(160) NOT NULL,
  `body` varchar(255) DEFAULT NULL,
  `icon` varchar(60) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `type` varchar(40) NOT NULL DEFAULT 'info',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `body`, `icon`, `link`, `type`, `is_read`, `created_at`) VALUES
(1, 1, '1 sale has pending due', 'Outstanding collections: 50.00', 'request_quote', '/accounting/receivables', 'receivables', 1, '2026-07-11 11:51:27'),
(3, 1, '1 product is low on stock', 'Restock items that fell to or below alert quantity.', 'warning', '/reports/stock', 'low_stock', 0, '2026-08-15 19:32:34');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(160) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `payable_type` enum('sale','purchase','direct','customer','supplier','expense') NOT NULL,
  `payable_id` int(10) UNSIGNED NOT NULL,
  `party_type` enum('customer','supplier','none') NOT NULL DEFAULT 'none',
  `party_id` int(10) UNSIGNED DEFAULT NULL,
  `direction` enum('in','out') NOT NULL,
  `amount` decimal(14,2) NOT NULL,
  `method` enum('cash','card','bank','mobile','bkash','nagad','upay','dutch_bangla','cheque') NOT NULL DEFAULT 'cash',
  `account` varchar(80) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `paid_at` date NOT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `reference`, `payable_type`, `payable_id`, `party_type`, `party_id`, `direction`, `amount`, `method`, `account`, `note`, `paid_at`, `created_by`, `created_at`) VALUES
(1, 'PAY-260707-418B6', 'sale', 1, 'customer', NULL, 'in', 64500.00, 'cash', NULL, 'Sale payment', '2026-07-07', 1, '2026-07-07 11:44:56'),
(2, 'PAY-260707-B4EF8', 'sale', 2, 'customer', NULL, 'in', 500.00, 'cash', NULL, 'Sale payment', '2026-07-07', 1, '2026-07-07 11:46:10'),
(3, 'PAY-260707-B37B4', 'sale', 3, 'customer', NULL, 'in', 250.00, 'cash', NULL, 'Sale payment', '2026-07-07', 1, '2026-07-07 11:47:26'),
(4, 'PAY-260711-89121', 'sale', 4, 'customer', NULL, 'in', 200.00, 'cash', NULL, 'Sale payment', '2026-07-11', 1, '2026-07-11 10:04:02'),
(5, 'PAY-260711-43A20', 'sale', 6, 'customer', 3, 'in', 200.00, 'cash', NULL, 'Sale payment', '2026-07-11', 1, '2026-07-11 10:17:52'),
(6, 'PAY-260711-401F2', 'customer', 3, 'customer', 3, 'in', 50.00, 'cash', NULL, 'Due collection', '2026-07-11', 1, '2026-07-11 10:31:35'),
(7, 'PAY-260711-31E6C', 'sale', 7, 'customer', 3, 'in', 30.00, 'cash', NULL, 'Sale payment (exchange balance)', '2026-07-11', 1, '2026-07-11 10:53:09'),
(8, 'PAY-260815-2618D', 'sale', 8, 'customer', NULL, 'in', 6000.00, 'cash', NULL, 'Sale payment', '2026-08-15', 1, '2026-08-15 18:56:36');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `module` varchar(80) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `created_at`) VALUES
(1, 'View Dashboard', 'dashboard.view', 'Main', '2026-07-04 10:05:31'),
(2, 'View Products', 'products.view', 'Inventory', '2026-07-04 10:05:31'),
(3, 'Create Products', 'products.create', 'Inventory', '2026-07-04 10:05:31'),
(4, 'Edit Products', 'products.edit', 'Inventory', '2026-07-04 10:05:31'),
(5, 'Delete Products', 'products.delete', 'Inventory', '2026-07-04 10:05:31'),
(6, 'Manage Stock', 'stock.manage', 'Inventory', '2026-07-04 10:05:31'),
(7, 'View Customers', 'customers.view', 'People', '2026-07-04 10:05:31'),
(8, 'Manage Customers', 'customers.manage', 'People', '2026-07-04 10:05:31'),
(9, 'View Suppliers', 'suppliers.view', 'People', '2026-07-04 10:05:31'),
(10, 'Manage Suppliers', 'suppliers.manage', 'People', '2026-07-04 10:05:31'),
(11, 'View Purchases', 'purchases.view', 'Transactions', '2026-07-04 10:05:31'),
(12, 'Create Purchases', 'purchases.create', 'Transactions', '2026-07-04 10:05:31'),
(13, 'Delete Purchases', 'purchases.delete', 'Transactions', '2026-07-04 10:05:31'),
(14, 'View Sales', 'sales.view', 'Transactions', '2026-07-04 10:05:31'),
(15, 'Create Sales', 'sales.create', 'Transactions', '2026-07-04 10:05:31'),
(16, 'Delete Sales', 'sales.delete', 'Transactions', '2026-07-04 10:05:31'),
(17, 'Use POS Terminal', 'pos.use', 'Main', '2026-07-04 10:05:31'),
(18, 'Direct Buy & Sell', 'direct.use', 'Main', '2026-07-04 10:05:31'),
(19, 'View Expenses', 'expenses.view', 'Finance', '2026-07-04 10:05:31'),
(20, 'Manage Expenses', 'expenses.manage', 'Finance', '2026-07-04 10:05:31'),
(21, 'View Accounting', 'accounting.view', 'Finance', '2026-07-04 10:05:31'),
(22, 'View Reports', 'reports.view', 'Finance', '2026-07-04 10:05:31'),
(23, 'Manage Users', 'users.manage', 'Administration', '2026-07-04 10:05:31'),
(24, 'Manage Roles', 'roles.manage', 'Administration', '2026-07-04 10:05:31'),
(25, 'View Activity Logs', 'logs.view', 'Administration', '2026-07-04 10:05:31'),
(26, 'Manage Settings', 'settings.manage', 'Administration', '2026-07-04 10:05:31'),
(27, 'View Categories', 'categories.view', 'Inventory', '2026-07-11 11:27:36'),
(28, 'Manage Categories', 'categories.manage', 'Inventory', '2026-07-11 11:27:36'),
(29, 'View Brands', 'brands.view', 'Inventory', '2026-07-11 11:27:36'),
(30, 'Manage Brands', 'brands.manage', 'Inventory', '2026-07-11 11:27:36'),
(31, 'View Units', 'units.view', 'Inventory', '2026-07-11 11:27:36'),
(32, 'Manage Units', 'units.manage', 'Inventory', '2026-07-11 11:27:36'),
(33, 'View Warehouses', 'warehouses.view', 'Inventory', '2026-07-11 11:27:36'),
(34, 'Manage Warehouses', 'warehouses.manage', 'Inventory', '2026-07-11 11:27:36'),
(35, 'View Stock', 'stock.view', 'Inventory', '2026-07-11 11:27:36');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `brand_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `sku` varchar(80) NOT NULL,
  `barcode` varchar(120) DEFAULT NULL,
  `type` enum('standard','variant','service') NOT NULL DEFAULT 'standard',
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `cost_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `wholesale_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax_rate` decimal(6,2) NOT NULL DEFAULT 0.00,
  `alert_quantity` decimal(14,2) NOT NULL DEFAULT 5.00,
  `has_serial` tinyint(1) NOT NULL DEFAULT 0,
  `has_imei` tinyint(1) NOT NULL DEFAULT 0,
  `has_expiry` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `brand_id`, `unit_id`, `name`, `slug`, `sku`, `barcode`, `type`, `description`, `image`, `cost_price`, `selling_price`, `wholesale_price`, `tax_rate`, `alert_quantity`, `has_serial`, `has_imei`, `has_expiry`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 5, 2, 1, 'Samsung Galaxy A15', 'samsung-galaxy-a15', 'SKU-A15', '8801000000015', 'standard', NULL, NULL, 18000.00, 21500.00, 20500.00, 5.00, 5.00, 0, 1, 0, 1, NULL, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(2, 5, 3, 1, 'Apple iPhone 15', 'apple-iphone-15', 'SKU-IP15', '8801000000022', 'standard', NULL, NULL, 110000.00, 129000.00, 125000.00, 5.00, 3.00, 0, 1, 0, 1, NULL, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(3, 6, 1, 1, 'USB-C Cable 1m', 'usb-c-cable-1m', 'SKU-USBC', '8801000000039', 'standard', NULL, NULL, 120.00, 250.00, 200.00, 0.00, 20.00, 0, 0, 0, 1, NULL, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(4, 2, 4, 1, 'Nescafe Classic 100g', 'nescafe-classic-100g', 'SKU-NESC', '8801000000046', 'standard', NULL, NULL, 480.00, 650.00, 600.00, 0.00, 15.00, 0, 0, 0, 1, NULL, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(5, 2, 5, 1, 'Lifebuoy Soap 100g', 'lifebuoy-soap', 'SKU-LIFE', '8801000000053', 'standard', NULL, NULL, 45.00, 70.00, 60.00, 0.00, 30.00, 0, 0, 0, 1, NULL, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(6, 1, 3, 1, 'demo', 'demo', 'SKU-AD0455', 'demo', 'standard', NULL, NULL, 4500.00, 6000.00, 5000.00, 0.00, 5.00, 0, 1, 0, 1, 1, '2026-08-15 17:49:06', '2026-08-15 17:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_serials`
--

CREATE TABLE `product_serials` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `serial_no` varchar(120) DEFAULT NULL,
  `imei` varchar(120) DEFAULT NULL,
  `status` enum('available','sold','returned','damaged') NOT NULL DEFAULT 'available',
  `warehouse_id` int(10) UNSIGNED DEFAULT NULL,
  `sale_item_id` int(10) UNSIGNED DEFAULT NULL,
  `purchase_item_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_serials`
--

INSERT INTO `product_serials` (`id`, `product_id`, `variant_id`, `serial_no`, `imei`, `status`, `warehouse_id`, `sale_item_id`, `purchase_item_id`, `created_at`) VALUES
(1, 6, NULL, NULL, '321456789', 'sold', 1, 8, NULL, '2026-08-15 17:49:06'),
(3, 6, NULL, NULL, '987458632', 'available', 1, NULL, NULL, '2026-08-15 17:49:06'),
(5, 6, NULL, NULL, '568978455', 'available', 1, NULL, NULL, '2026-08-15 17:49:17'),
(6, 6, NULL, NULL, '4578898545', 'available', 1, NULL, NULL, '2026-08-15 17:49:38'),
(7, 6, NULL, NULL, '4547879897', 'available', 2, NULL, NULL, '2026-08-15 17:56:02'),
(8, 1, NULL, NULL, 'TEMP-1-1-0001', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(9, 1, NULL, NULL, 'TEMP-1-1-0002', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(10, 1, NULL, NULL, 'TEMP-1-1-0003', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(11, 1, NULL, NULL, 'TEMP-1-1-0004', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(12, 1, NULL, NULL, 'TEMP-1-1-0005', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(13, 1, NULL, NULL, 'TEMP-1-1-0006', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(14, 1, NULL, NULL, 'TEMP-1-1-0007', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(15, 1, NULL, NULL, 'TEMP-1-1-0008', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(16, 1, NULL, NULL, 'TEMP-1-1-0009', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(17, 2, NULL, NULL, 'TEMP-2-1-0001', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(18, 2, NULL, NULL, 'TEMP-2-1-0002', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(19, 2, NULL, NULL, 'TEMP-2-1-0003', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(20, 2, NULL, NULL, 'TEMP-2-1-0004', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(21, 2, NULL, NULL, 'TEMP-2-1-0005', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(22, 2, NULL, NULL, 'TEMP-2-1-0006', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(23, 2, NULL, NULL, 'TEMP-2-1-0007', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(24, 2, NULL, NULL, 'TEMP-2-1-0008', 'available', 1, NULL, NULL, '2026-08-15 19:10:01'),
(25, 2, NULL, NULL, 'TEMP-2-1-0009', 'available', 1, NULL, NULL, '2026-08-15 19:10:01');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_name` varchar(160) NOT NULL,
  `color` varchar(60) DEFAULT NULL,
  `size` varchar(60) DEFAULT NULL,
  `sku` varchar(90) NOT NULL,
  `barcode` varchar(120) DEFAULT NULL,
  `cost_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `warehouse_id` int(10) UNSIGNED NOT NULL,
  `purchase_date` date NOT NULL,
  `status` enum('ordered','received','pending','cancelled') NOT NULL DEFAULT 'received',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(14,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `due` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('paid','partial','unpaid') NOT NULL DEFAULT 'unpaid',
  `note` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `purchase_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` decimal(14,2) NOT NULL,
  `unit_cost` decimal(14,2) NOT NULL,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL,
  `returned_qty` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `purchase_id` int(10) UNSIGNED NOT NULL,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `quote_date` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','accepted','declined','converted') NOT NULL DEFAULT 'draft',
  `note` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `reference`, `customer_id`, `quote_date`, `valid_until`, `subtotal`, `discount`, `tax`, `total`, `status`, `note`, `created_by`, `created_at`) VALUES
(1, 'QT-260704-FC8C4', NULL, '2026-07-04', NULL, 300.00, 10.00, 5.00, 295.00, 'sent', NULL, 1, '2026-07-04 10:41:46');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `quotation_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` decimal(14,2) NOT NULL,
  `unit_price` decimal(14,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`id`, `quotation_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 2, 2.00, 150.00, 300.00);

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `selector` varchar(24) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super-admin', 'Full unrestricted access', 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(2, 'Admin', 'admin', 'Administrative access', 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(3, 'Manager', 'manager', 'Manage operations & reports', 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(4, 'Cashier', 'cashier', 'POS & sales operations', 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(5, 'Salesman', 'salesman', 'Sales & customers', 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(6, 'Store Keeper', 'store-keeper', 'Inventory & stock', 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(7, 'Accountant', 'accountant', 'Accounting & expenses', 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(8, 'Employee', 'employee', 'Limited access', 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(10) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(2, 14),
(2, 15),
(2, 16),
(2, 17),
(2, 18),
(2, 19),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(2, 24),
(2, 25),
(2, 26),
(2, 27),
(2, 28),
(2, 29),
(2, 30),
(2, 31),
(2, 32),
(2, 33),
(2, 34),
(2, 35),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(3, 5),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 11),
(3, 12),
(3, 13),
(3, 14),
(3, 15),
(3, 16),
(3, 17),
(3, 18),
(3, 19),
(3, 20),
(3, 21),
(3, 22),
(3, 25),
(3, 27),
(3, 28),
(3, 29),
(3, 30),
(3, 31),
(3, 32),
(3, 33),
(3, 34),
(3, 35),
(4, 1),
(4, 2),
(4, 7),
(4, 8),
(4, 14),
(4, 15),
(4, 17),
(4, 18),
(5, 1),
(5, 2),
(5, 7),
(5, 9),
(5, 11),
(5, 14),
(5, 15),
(5, 17),
(5, 19),
(5, 22),
(6, 2),
(6, 3),
(6, 4),
(6, 5),
(6, 6),
(6, 27),
(6, 28),
(6, 29),
(6, 30),
(6, 31),
(6, 32),
(6, 33),
(6, 34),
(6, 35);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice_no` varchar(40) NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `warehouse_id` int(10) UNSIGNED NOT NULL,
  `sale_date` date NOT NULL,
  `type` enum('pos','retail','wholesale') NOT NULL DEFAULT 'pos',
  `status` enum('completed','pending','cancelled','draft') NOT NULL DEFAULT 'completed',
  `subtotal` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount_type` enum('fixed','percent') NOT NULL DEFAULT 'fixed',
  `coupon_code` varchar(40) DEFAULT NULL,
  `tax` decimal(14,2) NOT NULL DEFAULT 0.00,
  `vat` decimal(14,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `profit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `due` decimal(14,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('paid','partial','unpaid') NOT NULL DEFAULT 'paid',
  `payment_method` enum('cash','card','bank','mobile','bkash','nagad','upay','dutch_bangla','credit','mixed') NOT NULL DEFAULT 'cash',
  `note` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_no`, `customer_id`, `warehouse_id`, `sale_date`, `type`, `status`, `subtotal`, `discount`, `discount_type`, `coupon_code`, `tax`, `vat`, `shipping`, `total`, `total_cost`, `profit`, `paid`, `due`, `change_amount`, `payment_status`, `payment_method`, `note`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'INV-2607-00001', NULL, 1, '2026-07-07', 'pos', 'completed', 64500.00, 0.00, 'fixed', NULL, 0.00, 0.00, 0.00, 64500.00, 54000.00, 10500.00, 64500.00, 0.00, 0.00, 'paid', 'cash', NULL, 1, '2026-07-07 11:44:56', '2026-07-07 11:44:56'),
(2, 'INV-2607-00002', NULL, 1, '2026-07-07', 'pos', 'completed', 500.00, 0.00, 'fixed', NULL, 0.00, 0.00, 0.00, 500.00, 240.00, 260.00, 500.00, 0.00, 0.00, 'paid', 'cash', NULL, 1, '2026-07-07 11:46:10', '2026-07-07 11:46:10'),
(3, 'INV-2607-00003', NULL, 1, '2026-07-07', 'pos', 'completed', 250.00, 0.00, 'fixed', NULL, 0.00, 0.00, 0.00, 250.00, 120.00, 130.00, 250.00, 0.00, 0.00, 'paid', 'cash', NULL, 1, '2026-07-07 11:47:26', '2026-07-07 11:47:26'),
(4, 'INV-2607-00004', NULL, 1, '2026-07-11', 'pos', 'completed', 250.00, 0.00, 'fixed', NULL, 0.00, 0.00, 0.00, 250.00, 120.00, 130.00, 200.00, 50.00, 0.00, 'partial', 'cash', NULL, 1, '2026-07-11 10:04:02', '2026-07-11 10:04:02'),
(5, 'INV-2607-00005', NULL, 1, '2026-07-11', 'pos', 'completed', 250.00, 250.00, 'percent', NULL, 0.00, 0.00, 0.00, 0.00, 120.00, -120.00, 0.00, 0.00, 0.00, 'paid', 'cash', NULL, 1, '2026-07-11 10:11:00', '2026-07-11 10:11:00'),
(6, 'INV-2607-00006', 3, 1, '2026-07-11', 'pos', 'completed', 250.00, 0.00, 'fixed', NULL, 0.00, 0.00, 0.00, 250.00, 120.00, 130.00, 250.00, 0.00, 0.00, 'paid', 'cash', NULL, 1, '2026-07-11 10:17:52', '2026-07-11 10:31:35'),
(7, 'INV-2607-00007', 3, 1, '2026-07-11', 'pos', 'completed', 280.00, 0.00, 'fixed', NULL, 0.00, 0.00, 0.00, 280.00, 180.00, 100.00, 280.00, 0.00, 0.00, 'paid', 'mixed', 'Exchange from INV-2607-00006 — ffff', 1, '2026-07-11 10:53:09', '2026-07-11 10:53:09'),
(8, 'INV-2608-00008', NULL, 1, '2026-08-15', 'pos', 'completed', 6000.00, 0.00, 'fixed', NULL, 0.00, 0.00, 0.00, 6000.00, 4500.00, 1500.00, 6000.00, 0.00, 0.00, 'paid', 'cash', NULL, 1, '2026-08-15 18:56:36', '2026-08-15 18:56:36');

-- --------------------------------------------------------

--
-- Table structure for table `sale_exchanges`
--

CREATE TABLE `sale_exchanges` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `original_sale_id` int(10) UNSIGNED NOT NULL,
  `new_sale_id` int(10) UNSIGNED DEFAULT NULL,
  `sale_return_id` int(10) UNSIGNED DEFAULT NULL,
  `return_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `new_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `difference` decimal(14,2) NOT NULL DEFAULT 0.00,
  `settlement` enum('even','customer_pays','refund') NOT NULL DEFAULT 'even',
  `amount_paid` decimal(14,2) NOT NULL DEFAULT 0.00,
  `amount_refunded` decimal(14,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(40) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_exchanges`
--

INSERT INTO `sale_exchanges` (`id`, `reference`, `original_sale_id`, `new_sale_id`, `sale_return_id`, `return_total`, `new_total`, `difference`, `settlement`, `amount_paid`, `amount_refunded`, `payment_method`, `reason`, `created_by`, `created_at`) VALUES
(1, 'EXC-260711-CCDF4', 6, 7, 1, 250.00, 280.00, 30.00, 'customer_pays', 30.00, 0.00, 'cash', 'ffff', 1, '2026-07-11 10:53:09');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `sale_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` decimal(14,2) NOT NULL,
  `unit_price` decimal(14,2) NOT NULL,
  `unit_cost` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(14,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(14,2) NOT NULL,
  `returned_qty` decimal(14,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `variant_id`, `quantity`, `unit_price`, `unit_cost`, `discount`, `tax`, `subtotal`, `returned_qty`) VALUES
(1, 1, 1, NULL, 3.00, 21500.00, 18000.00, 0.00, 0.00, 64500.00, 0.00),
(2, 2, 3, NULL, 2.00, 250.00, 120.00, 0.00, 0.00, 500.00, 0.00),
(3, 3, 3, NULL, 1.00, 250.00, 120.00, 0.00, 0.00, 250.00, 0.00),
(4, 4, 3, NULL, 1.00, 250.00, 120.00, 0.00, 0.00, 250.00, 0.00),
(5, 5, 3, NULL, 1.00, 250.00, 120.00, 0.00, 0.00, 250.00, 0.00),
(6, 6, 3, NULL, 1.00, 250.00, 120.00, 0.00, 0.00, 250.00, 1.00),
(7, 7, 5, NULL, 4.00, 70.00, 45.00, 0.00, 0.00, 280.00, 0.00),
(8, 8, 6, NULL, 1.00, 6000.00, 4500.00, 0.00, 0.00, 6000.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `sale_returns`
--

CREATE TABLE `sale_returns` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `sale_id` int(10) UNSIGNED NOT NULL,
  `total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_returns`
--

INSERT INTO `sale_returns` (`id`, `reference`, `sale_id`, `total`, `reason`, `created_by`, `created_at`) VALUES
(1, 'SRET-260711-6C2B5', 6, 250.00, 'ffff', 1, '2026-07-11 10:53:09');

-- --------------------------------------------------------

--
-- Table structure for table `sale_return_items`
--

CREATE TABLE `sale_return_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `sale_return_id` int(10) UNSIGNED NOT NULL,
  `sale_item_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` decimal(14,2) NOT NULL,
  `unit_price` decimal(14,2) NOT NULL,
  `subtotal` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_return_items`
--

INSERT INTO `sale_return_items` (`id`, `sale_return_id`, `sale_item_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 6, 3, 1.00, 250.00, 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(60) NOT NULL DEFAULT 'general',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `updated_at`) VALUES
(1, 'business_name', 'Nubia Inventory', 'general', '2026-07-04 10:05:31'),
(2, 'business_email', 'contact@nubia.test', 'general', '2026-07-04 10:05:31'),
(3, 'business_phone', '+880 1700 000000', 'general', '2026-07-04 10:05:31'),
(4, 'business_address', 'RRP Center, Post Office More, Ishwardi, Pabna', 'general', '2026-07-07 11:47:13'),
(5, 'currency', 'BDT', 'general', '2026-07-04 10:05:31'),
(6, 'currency_symbol', 'Tk', 'general', '2026-07-04 10:05:31'),
(7, 'timezone', 'Asia/Dhaka', 'general', '2026-07-04 10:05:31'),
(8, 'invoice_prefix', 'INV', 'invoice', '2026-07-04 10:05:31'),
(9, 'purchase_prefix', 'PUR', 'invoice', '2026-07-04 10:05:31'),
(10, 'tax_rate', '5', 'tax', '2026-07-04 10:05:31'),
(11, 'vat_rate', '0', 'tax', '2026-07-04 10:05:31'),
(12, 'theme', 'light', 'appearance', '2026-08-13 12:02:59'),
(13, 'language', 'en', 'appearance', '2026-07-04 10:05:31'),
(14, 'logo', '', 'general', '2026-07-04 10:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `warehouse_id` int(10) UNSIGNED NOT NULL,
  `quantity` decimal(14,2) NOT NULL DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `batch_no` varchar(80) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`id`, `product_id`, `variant_id`, `warehouse_id`, `quantity`, `expiry_date`, `batch_no`, `updated_at`) VALUES
(1, 1, NULL, 1, 9.00, NULL, NULL, '2026-07-07 11:44:56'),
(2, 2, NULL, 1, 9.00, NULL, NULL, '2026-07-04 10:41:46'),
(3, 3, NULL, 1, 45.00, NULL, NULL, '2026-07-11 10:53:09'),
(4, 4, NULL, 1, 25.00, NULL, NULL, '2026-07-04 10:05:31'),
(5, 5, NULL, 1, 96.00, NULL, NULL, '2026-07-11 10:53:09'),
(6, 6, NULL, 1, 3.00, NULL, NULL, '2026-08-15 19:48:01'),
(7, 6, NULL, 2, 1.00, NULL, NULL, '2026-08-15 17:56:02');

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `warehouse_id` int(10) UNSIGNED NOT NULL,
  `type` enum('addition','subtraction') NOT NULL DEFAULT 'addition',
  `reason` varchar(255) DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_adjustments`
--

INSERT INTO `stock_adjustments` (`id`, `reference`, `warehouse_id`, `type`, `reason`, `created_by`, `created_at`) VALUES
(1, 'ADJ-260704-AD9D1', 1, 'addition', 'smoke test', 1, '2026-07-04 10:41:46');

-- --------------------------------------------------------

--
-- Table structure for table `stock_logs`
--

CREATE TABLE `stock_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `variant_id` int(10) UNSIGNED DEFAULT NULL,
  `warehouse_id` int(10) UNSIGNED NOT NULL,
  `type` enum('purchase','sale','adjustment','transfer_in','transfer_out','return_in','return_out','opening','damage','direct_sell') NOT NULL,
  `reference_type` varchar(40) DEFAULT NULL,
  `reference_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` decimal(14,2) NOT NULL,
  `balance_after` decimal(14,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `imei_codes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_logs`
--

INSERT INTO `stock_logs` (`id`, `product_id`, `variant_id`, `warehouse_id`, `type`, `reference_type`, `reference_id`, `quantity`, `balance_after`, `note`, `imei_codes`, `created_by`, `created_at`) VALUES
(1, 2, NULL, 1, 'adjustment', 'adjustment', 0, 5.00, 9.00, 'Adjustment ADJ-260704-AD9D1', NULL, 1, '2026-07-04 10:41:46'),
(2, 1, NULL, 1, 'sale', 'sale', 1, -3.00, 9.00, 'Sale INV-2607-00001', NULL, 1, '2026-07-07 11:44:56'),
(3, 3, NULL, 1, 'sale', 'sale', 2, -2.00, 48.00, 'Sale INV-2607-00002', NULL, 1, '2026-07-07 11:46:10'),
(4, 3, NULL, 1, 'sale', 'sale', 3, -1.00, 47.00, 'Sale INV-2607-00003', NULL, 1, '2026-07-07 11:47:26'),
(5, 3, NULL, 1, 'sale', 'sale', 4, -1.00, 46.00, 'Sale INV-2607-00004', NULL, 1, '2026-07-11 10:04:02'),
(6, 3, NULL, 1, 'sale', 'sale', 5, -1.00, 45.00, 'Sale INV-2607-00005', NULL, 1, '2026-07-11 10:11:00'),
(7, 3, NULL, 1, 'sale', 'sale', 6, -1.00, 44.00, 'Sale INV-2607-00006', NULL, 1, '2026-07-11 10:17:52'),
(8, 3, NULL, 1, 'return_in', 'sale_return', 1, 1.00, 45.00, 'Exchange return SRET-260711-6C2B5', NULL, 1, '2026-07-11 10:53:09'),
(9, 5, NULL, 1, 'sale', 'sale', 7, -4.00, 96.00, 'Sale INV-2607-00007', NULL, 1, '2026-07-11 10:53:09'),
(10, 6, NULL, 1, 'opening', 'product', 6, 4.00, 4.00, 'Opening stock (IMEI)', NULL, 1, '2026-08-15 17:49:06'),
(11, 6, NULL, 1, 'adjustment', 'product', 6, 1.00, 5.00, 'IMEI stock-in', NULL, 1, '2026-08-15 17:49:17'),
(12, 6, NULL, 1, 'adjustment', 'product', 6, 1.00, 6.00, 'IMEI stock-in', NULL, 1, '2026-08-15 17:49:38'),
(13, 6, NULL, 2, 'adjustment', 'product', 6, 1.00, 1.00, 'IMEI stock-in', NULL, 1, '2026-08-15 17:56:02'),
(14, 6, NULL, 1, 'sale', 'sale', 8, -1.00, 5.00, 'Sale INV-2608-00008 · IMEI/Serial: 321456789', '321456789', 1, '2026-08-15 18:56:36'),
(15, 6, NULL, 1, 'adjustment', 'product', 6, -1.00, 4.00, 'IMEI removed · IMEI/Serial: 987456322', '987456322', 1, '2026-08-15 19:32:34'),
(16, 6, NULL, 1, 'adjustment', 'product', 6, -1.00, 3.00, 'IMEI removed · IMEI/Serial: 487958635', '487958635', 1, '2026-08-15 19:48:01');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` int(10) UNSIGNED NOT NULL,
  `reference` varchar(40) NOT NULL,
  `from_warehouse` int(10) UNSIGNED NOT NULL,
  `to_warehouse` int(10) UNSIGNED NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed') NOT NULL DEFAULT 'completed',
  `created_by` int(10) UNSIGNED DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(160) DEFAULT NULL,
  `company` varchar(160) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `opening_balance` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `email`, `company`, `address`, `city`, `opening_balance`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tech Distributors Ltd', '01911111111', 'sales@techdist.com', 'Tech Distributors', NULL, NULL, 0.00, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(2, 'Global FMCG Supply', '01922222222', 'info@globalfmcg.com', 'Global FMCG', NULL, NULL, 0.00, 1, '2026-07-04 10:05:31', '2026-07-04 10:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(80) NOT NULL,
  `short_name` varchar(20) NOT NULL,
  `base_unit` int(10) UNSIGNED DEFAULT NULL,
  `operator` enum('*','/') NOT NULL DEFAULT '*',
  `operation_value` decimal(12,4) NOT NULL DEFAULT 1.0000,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `short_name`, `base_unit`, `operator`, `operation_value`, `status`, `created_at`) VALUES
(1, 'Piece', 'pcs', NULL, '*', 1.0000, 1, '2026-07-04 10:05:31'),
(2, 'Kilogram', 'kg', NULL, '*', 1.0000, 1, '2026-07-04 10:05:31'),
(3, 'Gram', 'g', NULL, '*', 1.0000, 1, '2026-07-04 10:05:31'),
(4, 'Litre', 'ltr', NULL, '*', 1.0000, 1, '2026-07-04 10:05:31'),
(5, 'Box', 'box', NULL, '*', 1.0000, 1, '2026-07-04 10:05:31'),
(6, 'Pack', 'pack', NULL, '*', 1.0000, 1, '2026-07-04 10:05:31'),
(7, 'Dozen', 'dz', NULL, '*', 1.0000, 1, '2026-07-04 10:05:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `warehouse_id` int(10) UNSIGNED DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(160) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `otp_code` varchar(10) DEFAULT NULL,
  `otp_expires_at` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `warehouse_id`, `name`, `email`, `phone`, `password`, `avatar`, `status`, `otp_code`, `otp_expires_at`, `last_login_at`, `last_login_ip`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Super Admin', 'admin@nubia.test', '01700000000', '$2y$10$KI.MZFKDEfbFBh3mgWZuIOAqn8gOu5R2kPQ/rM35ZOEwvHOY62A12', NULL, 'active', NULL, NULL, '2026-08-15 19:44:07', '127.0.0.1', '2026-07-04 10:05:31', '2026-08-15 19:44:07'),
(2, 4, 1, 'Cashier One', 'cashier@nubia.test', '01700000001', '$2y$10$KI.MZFKDEfbFBh3mgWZuIOAqn8gOu5R2kPQ/rM35ZOEwvHOY62A12', NULL, 'active', NULL, NULL, NULL, NULL, '2026-07-04 10:05:31', '2026-07-04 10:05:31'),
(3, 5, NULL, 'demo', 'demo@gmail.com', '01734258746', '$2y$10$f8CR2j9VKzQ9yi1P.c0s7eXLNm.HJ5xrLyKQeAYhZsGvHXEvn5n1u', NULL, 'active', NULL, NULL, '2026-07-11 11:33:39', '127.0.0.1', '2026-07-11 11:13:42', '2026-07-11 11:33:39');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `code` varchar(40) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(160) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `code`, `phone`, `email`, `address`, `is_default`, `status`, `created_at`) VALUES
(1, 'Main Store', 'WH-MAIN', '017000000000', NULL, 'Head Office, Dhaka', 1, 1, '2026-07-04 10:05:31'),
(2, 'Warehouse B', 'WH-B', '017000000001', NULL, 'Branch, Chittagong', 0, 1, '2026-07-04 10:05:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_alog_user` (`user_id`),
  ADD KEY `idx_alog_module` (`module`),
  ADD KEY `idx_alog_date` (`created_at`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_brands_slug` (`slug`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categories_slug` (`slug`),
  ADD KEY `idx_categories_parent` (`parent_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customers_phone` (`phone`),
  ADD KEY `idx_customers_name` (`name`);

--
-- Indexes for table `direct_transactions`
--
ALTER TABLE `direct_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_direct_ref` (`reference`),
  ADD KEY `idx_direct_date` (`created_at`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_expense_ref` (`reference`),
  ADD KEY `idx_expense_category` (`category_id`),
  ADD KEY `idx_expense_date` (`expense_date`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_user` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pr_email` (`email`),
  ADD KEY `idx_pr_token` (`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pay_payable` (`payable_type`,`payable_id`),
  ADD KEY `idx_pay_party` (`party_type`,`party_id`),
  ADD KEY `idx_pay_date` (`paid_at`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_permissions_slug` (`slug`),
  ADD KEY `idx_permissions_module` (`module`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_products_sku` (`sku`),
  ADD KEY `idx_products_barcode` (`barcode`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_brand` (`brand_id`),
  ADD KEY `idx_products_name` (`name`),
  ADD KEY `fk_products_unit` (`unit_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pimg_product` (`product_id`);

--
-- Indexes for table `product_serials`
--
ALTER TABLE `product_serials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_product_serials_imei` (`imei`),
  ADD KEY `idx_serial_product` (`product_id`),
  ADD KEY `idx_serial_no` (`serial_no`),
  ADD KEY `idx_serial_imei` (`imei`),
  ADD KEY `idx_serial_sale_item` (`sale_item_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_variant_sku` (`sku`),
  ADD KEY `idx_variant_product` (`product_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_purchase_ref` (`reference`),
  ADD KEY `idx_purchase_supplier` (`supplier_id`),
  ADD KEY `idx_purchase_date` (`purchase_date`),
  ADD KEY `fk_purchase_warehouse` (`warehouse_id`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pitem_purchase` (`purchase_id`),
  ADD KEY `idx_pitem_product` (`product_id`);

--
-- Indexes for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_preturn_ref` (`reference`),
  ADD KEY `fk_preturn_purchase` (`purchase_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_quote_ref` (`reference`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_qitem_quote` (`quotation_id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rt_selector` (`selector`),
  ADD KEY `idx_rt_user` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_roles_slug` (`slug`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_rp_permission` (`permission_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sale_invoice` (`invoice_no`),
  ADD KEY `idx_sale_customer` (`customer_id`),
  ADD KEY `idx_sale_date` (`sale_date`),
  ADD KEY `idx_sale_status` (`status`),
  ADD KEY `fk_sale_warehouse` (`warehouse_id`);

--
-- Indexes for table `sale_exchanges`
--
ALTER TABLE `sale_exchanges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sexchange_ref` (`reference`),
  ADD KEY `idx_sex_original` (`original_sale_id`),
  ADD KEY `idx_sex_new` (`new_sale_id`),
  ADD KEY `fk_sex_return` (`sale_return_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sitem_sale` (`sale_id`),
  ADD KEY `idx_sitem_product` (`product_id`);

--
-- Indexes for table `sale_returns`
--
ALTER TABLE `sale_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sreturn_ref` (`reference`),
  ADD KEY `fk_sreturn_sale` (`sale_id`);

--
-- Indexes for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sri_return` (`sale_return_id`),
  ADD KEY `idx_sri_item` (`sale_item_id`),
  ADD KEY `fk_sri_product` (`product_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_settings_key` (`key`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_stock` (`product_id`,`variant_id`,`warehouse_id`,`batch_no`),
  ADD KEY `idx_stock_warehouse` (`warehouse_id`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_adj_ref` (`reference`);

--
-- Indexes for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_slog_product` (`product_id`),
  ADD KEY `idx_slog_type` (`type`),
  ADD KEY `idx_slog_ref` (`reference_type`,`reference_id`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_transfer_ref` (`reference`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_suppliers_phone` (`phone`),
  ADD KEY `idx_suppliers_name` (`name`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_email` (`email`),
  ADD KEY `idx_users_role` (`role_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_warehouses_code` (`code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `direct_transactions`
--
ALTER TABLE `direct_transactions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_serials`
--
ALTER TABLE `product_serials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sale_exchanges`
--
ALTER TABLE `sale_exchanges`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sale_returns`
--
ALTER TABLE `sale_returns`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `fk_expense_category` FOREIGN KEY (`category_id`) REFERENCES `expense_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_unit` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `fk_pimg_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_serials`
--
ALTER TABLE `product_serials`
  ADD CONSTRAINT `fk_serial_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `fk_variant_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `fk_purchase_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_purchase_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `fk_pitem_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_pitem_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD CONSTRAINT `fk_preturn_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `fk_qitem_quote` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `fk_rt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sale_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sale_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`);

--
-- Constraints for table `sale_exchanges`
--
ALTER TABLE `sale_exchanges`
  ADD CONSTRAINT `fk_sex_new` FOREIGN KEY (`new_sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sex_original` FOREIGN KEY (`original_sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sex_return` FOREIGN KEY (`sale_return_id`) REFERENCES `sale_returns` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `fk_sitem_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_sitem_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sale_returns`
--
ALTER TABLE `sale_returns`
  ADD CONSTRAINT `fk_sreturn_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  ADD CONSTRAINT `fk_sri_item` FOREIGN KEY (`sale_item_id`) REFERENCES `sale_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sri_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_sri_return` FOREIGN KEY (`sale_return_id`) REFERENCES `sale_returns` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stock_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD CONSTRAINT `fk_slog_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
