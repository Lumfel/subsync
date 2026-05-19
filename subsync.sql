-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 19, 2026 at 06:12 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `subsync`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `contact_number`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'System Admin', 'admin@subsync.com', '$2y$12$TnwkVZG9oxgOeAv4tm4IXObeVM.m0zRyOMjzIsLp52e3DyJoaU3ie', NULL, 'Active', NULL, '2026-05-17 00:34:22', '2026-05-17 00:34:22');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `officer_id` bigint UNSIGNED DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'notice',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `admin_id`, `officer_id`, `title`, `content`, `tag`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Monthly Dues Reminder', 'Kindly settle your monthly dues before May 31, 2026.', 'Finance', '2026-05-17 21:19:03', '2026-05-17 21:19:03'),
(2, 1, NULL, 'Road Repair Schedule', 'Main road repair scheduled for June 5–7, 2026. Expect traffic.', 'Maintenance', '2026-05-17 21:19:03', '2026-05-17 21:19:03'),
(3, NULL, 1, 'Barangay Assembly', 'Barangay assembly on June 10 at 8AM. Attendance required.', 'Community', '2026-05-17 21:19:03', '2026-05-17 21:19:03'),
(4, 1, NULL, 'Test System Announcement', 'This is a test announcement from the admin dashboard.', 'notice', '2026-05-17 21:25:57', '2026-05-17 21:25:57'),
(5, NULL, 1, 'Officer: Community Meeting - Block 1', 'All Block 1 residents are invited to the community meeting on June 15 at 7PM.', 'notice', '2026-05-17 21:36:50', '2026-05-17 21:36:50');

-- --------------------------------------------------------

--
-- Table structure for table `announcement_views`
--

CREATE TABLE `announcement_views` (
  `id` bigint UNSIGNED NOT NULL,
  `resident_id` bigint UNSIGNED NOT NULL,
  `announcement_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`id`, `title`, `created_at`) VALUES
(1, 'Pedro Reyes', '2026-05-18 05:45:37'),
(2, 'Ana Garcia', '2026-05-18 05:45:37'),
(3, 'Juan dela Cruz (Officer)', '2026-05-18 05:52:51');

-- --------------------------------------------------------

--
-- Table structure for table `conv_participants`
--

CREATE TABLE `conv_participants` (
  `id` bigint UNSIGNED NOT NULL,
  `conversation_id` bigint UNSIGNED NOT NULL,
  `resident_id` bigint UNSIGNED DEFAULT NULL,
  `officer_id` bigint UNSIGNED DEFAULT NULL,
  `participant_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conv_participants`
--

INSERT INTO `conv_participants` (`id`, `conversation_id`, `resident_id`, `officer_id`, `participant_type`, `created_at`) VALUES
(1, 1, 1, NULL, 'resident', '2026-05-18 05:45:37'),
(2, 2, 2, NULL, 'resident', '2026-05-18 05:45:37'),
(3, 3, NULL, 1, 'officer', '2026-05-18 05:52:51');

-- --------------------------------------------------------

--
-- Table structure for table `delinquents`
--

CREATE TABLE `delinquents` (
  `id` bigint UNSIGNED NOT NULL,
  `house_id` bigint UNSIGNED NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_flagged` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delinquents`
--

INSERT INTO `delinquents` (`id`, `house_id`, `reason`, `date_flagged`, `created_at`, `updated_at`) VALUES
(1, 3, 'Unpaid dues for 5 months', '2026-05-01', '2026-05-17 21:20:53', '2026-05-17 21:20:53');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `admin_id`, `name`, `description`, `latitude`, `longitude`, `status`, `created_at`) VALUES
(1, NULL, 'Clubhouse', 'Main clubhouse for events and gatherings.', 10.6228000, 122.9614000, 'Active', '2026-05-19 02:41:23'),
(2, NULL, 'Basketball Court', 'Outdoor basketball court open daily 6AM–10PM.', 10.6232000, 122.9608000, 'Active', '2026-05-19 02:41:23'),
(3, NULL, 'Main Entrance Gate', 'Primary entrance and exit of the subdivision.', 10.6222000, 122.9625000, 'Active', '2026-05-19 02:41:23'),
(4, NULL, 'Children\'s Playground', 'Playground area for children aged 3–12.', 10.6238000, 122.9602000, 'Active', '2026-05-19 02:41:23'),
(5, NULL, 'Multi-Purpose Hall', 'Available for rent for community events.', 10.6224000, 122.9619000, 'Active', '2026-05-19 02:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `families`
--

CREATE TABLE `families` (
  `id` bigint UNSIGNED NOT NULL,
  `family_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `family_head` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `members` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_records`
--

CREATE TABLE `financial_records` (
  `id` bigint UNSIGNED NOT NULL,
  `resident_id` bigint UNSIGNED NOT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `record_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `record_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `financial_records`
--

INSERT INTO `financial_records` (`id`, `resident_id`, `admin_id`, `record_type`, `description`, `amount`, `record_date`, `created_at`) VALUES
(1, 1, 1, 'Due', 'Monthly dues May 2026', 500.00, '2026-05-31', '2026-05-18 05:20:33'),
(2, 2, 1, 'Payment', 'Monthly dues May 2026 - Paid', 500.00, '2026-05-10', '2026-05-18 05:20:33'),
(3, 3, 1, 'Due', 'Outstanding balance', 2500.00, '2026-05-31', '2026-05-18 05:20:33');

-- --------------------------------------------------------

--
-- Table structure for table `financial_reports`
--

CREATE TABLE `financial_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `month` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `previous_balance` decimal(12,2) NOT NULL DEFAULT '0.00',
  `collections` json NOT NULL,
  `expenses` json NOT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `households`
--

CREATE TABLE `households` (
  `id` bigint UNSIGNED NOT NULL,
  `block_lot_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Occupied',
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `households`
--

INSERT INTO `households` (`id`, `block_lot_number`, `status`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 'Blk 1 Lot 5', 'Active', 10.6230000, 122.9610000, '2026-05-17 21:19:02', '2026-05-17 21:19:02'),
(2, 'Blk 2 Lot 3', 'Active', 10.6235000, 122.9620000, '2026-05-17 21:19:02', '2026-05-17 21:19:02'),
(3, 'Blk 3 Lot 8', 'Delinquent', 10.6220000, 122.9605000, '2026-05-17 21:19:02', '2026-05-17 21:19:02'),
(4, 'Blk 4 Lot 2', 'Active', 10.6240000, 122.9615000, '2026-05-17 21:25:08', '2026-05-17 21:25:08'),
(5, 'Blk 5 Lot 10', 'Active', 10.6225000, 122.9630000, '2026-05-17 21:54:23', '2026-05-17 21:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `household_members`
--

CREATE TABLE `household_members` (
  `id` bigint UNSIGNED NOT NULL,
  `house_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `household_members`
--

INSERT INTO `household_members` (`id`, `house_id`, `name`, `relationship`, `contact_number`, `created_at`) VALUES
(1, 1, 'Lina Reyes', 'Spouse', '09111111111', '2026-05-18 05:19:02'),
(2, 1, 'Ricky Reyes', 'Child', NULL, '2026-05-18 05:19:02'),
(3, 2, 'Bert Garcia', 'Spouse', '09222222222', '2026-05-18 05:19:02'),
(4, 1, 'Rosa Santos', NULL, NULL, '2026-05-18 05:54:58');

-- --------------------------------------------------------

--
-- Table structure for table `issue_reports`
--

CREATE TABLE `issue_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `resident_id` bigint UNSIGNED NOT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `issue_reports`
--

INSERT INTO `issue_reports` (`id`, `resident_id`, `category`, `title`, `description`, `latitude`, `longitude`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Infrastructure', 'Broken Street Light', 'Street light near Blk 1 Lot 5 has been out for 3 days.', 10.6231000, 122.9611000, 'In Progress', '2026-05-17 21:19:03', '2026-05-17 21:27:49'),
(2, 2, 'Noise', 'Loud Music at Night', 'Neighbor plays loud music past midnight.', 10.6236000, 122.9621000, 'Resolved', '2026-05-17 21:19:03', '2026-05-17 21:39:43'),
(3, 3, 'Drainage', 'Clogged Drain', 'Drainage near Blk 3 is clogged causing flooding.', 10.6221000, 122.9606000, 'Resolved', '2026-05-17 21:19:03', '2026-05-17 21:19:03'),
(4, 1, 'Road / Pavement', 'Pothole on Main Road', 'Large pothole near entrance gate, causing vehicle damage.', 10.6228000, 122.9618000, 'In Progress', '2026-05-17 21:33:18', '2026-05-17 21:37:30');

-- --------------------------------------------------------

--
-- Table structure for table `issue_responses`
--

CREATE TABLE `issue_responses` (
  `id` bigint UNSIGNED NOT NULL,
  `issue_id` bigint UNSIGNED NOT NULL,
  `officer_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `responder_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `issue_responses`
--

INSERT INTO `issue_responses` (`id`, `issue_id`, `officer_id`, `admin_id`, `responder_type`, `response_content`, `created_at`) VALUES
(1, 1, NULL, 1, 'admin', 'Team dispatched to fix the street light.', '2026-05-18 05:28:01'),
(2, 1, NULL, 1, 'admin', 'Team dispatched to fix the street light.', '2026-05-18 05:29:16'),
(3, 4, 1, NULL, 'officer', 'Officer team dispatched to assess the pothole.', '2026-05-18 05:37:30'),
(4, 4, 1, NULL, 'officer', 'Officer team dispatched to assess the pothole.', '2026-05-18 05:38:57'),
(5, 2, 1, NULL, 'officer', 'Warning issued to neighbor. Issue resolved.', '2026-05-18 05:39:43');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint UNSIGNED NOT NULL,
  `uploader_id` bigint UNSIGNED NOT NULL,
  `uploader_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entity_id` bigint UNSIGNED DEFAULT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint UNSIGNED NOT NULL,
  `conversation_id` bigint UNSIGNED NOT NULL,
  `resident_id` bigint UNSIGNED DEFAULT NULL,
  `officer_id` bigint UNSIGNED DEFAULT NULL,
  `sender_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `resident_id`, `officer_id`, `sender_type`, `content`, `created_at`) VALUES
(1, 1, 1, NULL, 'resident', 'Hello Admin, I have a question about my dues.', '2026-05-17 19:45:37'),
(2, 1, NULL, NULL, 'admin', 'Hello Pedro! How can I help you?', '2026-05-17 20:45:37'),
(3, 2, 2, NULL, 'resident', 'Good morning! Just checking on the road repair schedule.', '2026-05-16 21:45:37'),
(4, 1, NULL, NULL, 'admin', 'Your balance has been updated.', '2026-05-18 05:46:54'),
(5, 1, NULL, NULL, 'admin', 'test message', '2026-05-18 05:47:03'),
(6, 1, NULL, NULL, 'admin', 'Your balance has been updated.', '2026-05-17 21:47:25'),
(7, 1, NULL, NULL, 'admin', 'Thank you for letting me know!', '2026-05-17 21:49:30'),
(8, 3, NULL, NULL, 'admin', 'Good morning, Officer! Please handle the noise complaint.', '2026-05-17 18:52:51'),
(9, 3, NULL, 1, 'officer', 'Understood, I will check it out now.', '2026-05-17 19:52:51');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_05_16_112739_create_sessions_table', 1),
(2, '2026_05_17_000001_create_admins_table', 2),
(3, '2026_05_17_000002_create_officers_table', 2),
(4, '2026_05_17_000003_recreate_households_table', 2),
(5, '2026_05_17_000004_create_residents_table', 2),
(6, '2026_05_17_000005_create_household_members_table', 2),
(7, '2026_05_17_000006_create_announcements_table', 2),
(8, '2026_05_17_000007_create_announcement_views_table', 2),
(9, '2026_05_17_000008_create_media_table', 3),
(10, '2026_05_17_000009_create_financial_records_table', 3),
(11, '2026_05_17_000010_create_issue_reports_table', 3),
(12, '2026_05_17_000011_create_issue_responses_table', 3),
(13, '2026_05_17_000012_create_recommendations_table', 3),
(14, '2026_05_17_000013_create_conversations_table', 3),
(15, '2026_05_17_000014_create_conv_participants_table', 3),
(16, '2026_05_17_000015_create_messages_table', 3),
(17, '2026_05_17_000016_create_facilities_table', 3),
(18, '2026_05_17_000017_drop_stale_singular_tables', 4),
(19, '2026_05_17_103040_add_tag_to_announcements_table', 5),
(20, '2026_05_18_052008_create_delinquents_table', 6),
(21, '2026_05_18_100000_create_officer_files_table', 7),
(22, '2026_05_19_000022_create_financial_reports_table', 8),
(23, '2026_05_19_000023_create_families_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table `officers`
--

CREATE TABLE `officers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `role_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `officers`
--

INSERT INTO `officers` (`id`, `name`, `email`, `password`, `contact_number`, `status`, `role_description`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Juan dela Cruz', 'officer1@subsync.com', '$2y$12$J5c7FPOSmLyRfuqhNire9.shQn/f1R0djzBDchFlh26PIvidhrtPq', '09171234567', 'Active', 'Security Officer', NULL, '2026-05-17 21:19:01', '2026-05-17 21:19:01'),
(2, 'Maria Santos', 'officer2@subsync.com', '$2y$12$W7ho4ugUxDD1sX940z.LOe.74eEpUBOtvArBOemckALWyqeoLLLny', '09271234567', 'Active', 'Community Liaison', NULL, '2026-05-17 21:19:02', '2026-05-17 21:19:02');

-- --------------------------------------------------------

--
-- Table structure for table `officer_files`
--

CREATE TABLE `officer_files` (
  `id` bigint UNSIGNED NOT NULL,
  `officer_id` bigint UNSIGNED DEFAULT NULL,
  `officer_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `size_bytes` bigint UNSIGNED NOT NULL DEFAULT '0',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE `recommendations` (
  `id` bigint UNSIGNED NOT NULL,
  `resident_id` bigint UNSIGNED NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `recommendations`
--

INSERT INTO `recommendations` (`id`, `resident_id`, `title`, `description`, `status`, `created_at`) VALUES
(1, 1, 'Install More Street Lights', 'Please install additional street lights along the main road.', 'Reviewed', '2026-05-18 05:19:03'),
(2, 2, 'Community Garden', 'Suggest creating a community garden in the empty lot near Blk 2.', 'Approved', '2026-05-18 05:19:03'),
(3, 1, 'Add More Benches', 'Please add benches along the main road for residents to rest.', 'Pending', '2026-05-18 05:33:58'),
(4, 1, 'Add More Benches', 'Please add benches along the main road for residents to rest.', 'Pending', '2026-05-17 21:34:36');

-- --------------------------------------------------------

--
-- Table structure for table `residents`
--

CREATE TABLE `residents` (
  `id` bigint UNSIGNED NOT NULL,
  `house_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `current_balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `residents`
--

INSERT INTO `residents` (`id`, `house_id`, `name`, `email`, `password`, `contact_number`, `status`, `current_balance`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pedro Reyes', 'resident1@subsync.com', '$2y$12$oBydemTTsxMRu34ce.jhI.lA8ifkx5mr88.pW/eyr5vtErIDYlPlS', '09181234567', 'Active', 500.00, NULL, '2026-05-17 21:19:02', '2026-05-17 21:19:02'),
(2, 2, 'Ana Garcia', 'resident2@subsync.com', '$2y$12$nHKSQrjWi65n4OtrnUa.Cu4gtJYzCevCEftAMQglI0g9FSUCwEvnO', '09281234567', 'Active', 0.00, NULL, '2026-05-17 21:19:02', '2026-05-17 21:19:02'),
(3, 3, 'Carlos Bautista', 'resident3@subsync.com', '$2y$12$I8E/nYpkJWLNOFf3th89GeuxaR0g46T9OAmC/kEAsnlnF/nKi087e', '09381234567', 'Delinquent', 2500.00, NULL, '2026-05-17 21:19:02', '2026-05-17 21:19:02'),
(4, 4, 'Test Resident', 'testres@subsync.com', '$2y$12$vj6qn3LXh7uLK5yPaqJLz.3GFBnGbb.zSzEm/WHBBXaGvN1aQ9AYe', '09991234567', 'Active', 0.00, NULL, '2026-05-17 21:25:09', '2026-05-17 21:25:09');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('0ii8w9dauOct0KTgb0RBrRJLXR42qsVGV68SVlou', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0', 'eyJfdG9rZW4iOiJoQTRTSlN6QlVvVWQxUUhvOWptZ01uTEpFU1dPMHhtTFg5RlpQRnZOIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9hZG1pblwvbG9naW4iLCJyb3V0ZSI6ImFkbWluLmxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779169239),
('aBRyPjfRL2W27JvXltrFg1Fwv9VctiaOwARoa46K', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'eyJfdG9rZW4iOiJKYmZSbE03SE50T2VoR1NJNUREMWZoTWZGc2FUd1BzZ01UZkc0MlVZIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9hcGlcL3Jlc2lkZW50cyIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJhZG1pbl9pZCI6MSwiYWRtaW5fbmFtZSI6IlN5c3RlbSBBZG1pbiIsImxvZ2luX3Jlc2lkZW50XzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsImxvZ2luX29mZmljZXJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1779169437),
('GBRG4PjPJ5w9dRW1e8m1Ut3Lo50YMJ0elAdCAtoC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.120.0 Chrome/142.0.7444.265 Electron/39.8.8 Safari/537.36', 'eyJfdG9rZW4iOiJmcEpGWmhYbjVEUkpxWVpiQ21qd2pTaXV6M2Juem1qam1pSEFsSk4zIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9hZG1pblwvbG9naW4iLCJyb3V0ZSI6ImFkbWluLmxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1779169354),
('UaKQyeyZ07Ofaw9Zo2g6xa2cMLki5rwrWXlrY263', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 OPR/131.0.0.0', 'eyJfdG9rZW4iOiI4UW9vclBMa21VOHZnbnhydXcwUDBjVnp1RHpmcGF5clNqcU1Dbjg1IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9hcGlcL3Jlc2lkZW50cyIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJhZG1pbl9pZCI6MSwiYWRtaW5fbmFtZSI6IlN5c3RlbSBBZG1pbiIsImxvZ2luX29mZmljZXJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1779170211);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admins_email_unique` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_admin_id_foreign` (`admin_id`),
  ADD KEY `announcements_officer_id_foreign` (`officer_id`);

--
-- Indexes for table `announcement_views`
--
ALTER TABLE `announcement_views`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcement_views_resident_id_announcement_id_unique` (`resident_id`,`announcement_id`),
  ADD KEY `announcement_views_announcement_id_foreign` (`announcement_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `conv_participants`
--
ALTER TABLE `conv_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conv_participants_conversation_id_foreign` (`conversation_id`),
  ADD KEY `conv_participants_resident_id_foreign` (`resident_id`),
  ADD KEY `conv_participants_officer_id_foreign` (`officer_id`);

--
-- Indexes for table `delinquents`
--
ALTER TABLE `delinquents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delinquents_house_id_foreign` (`house_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facilities_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `families`
--
ALTER TABLE `families`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_records`
--
ALTER TABLE `financial_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financial_records_resident_id_foreign` (`resident_id`),
  ADD KEY `financial_records_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `financial_reports`
--
ALTER TABLE `financial_reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `households`
--
ALTER TABLE `households`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `household_members`
--
ALTER TABLE `household_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `household_members_house_id_foreign` (`house_id`);

--
-- Indexes for table `issue_reports`
--
ALTER TABLE `issue_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_reports_resident_id_foreign` (`resident_id`);

--
-- Indexes for table `issue_responses`
--
ALTER TABLE `issue_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_responses_issue_id_foreign` (`issue_id`),
  ADD KEY `issue_responses_officer_id_foreign` (`officer_id`),
  ADD KEY `issue_responses_admin_id_foreign` (`admin_id`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_conversation_id_foreign` (`conversation_id`),
  ADD KEY `messages_resident_id_foreign` (`resident_id`),
  ADD KEY `messages_officer_id_foreign` (`officer_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `officers`
--
ALTER TABLE `officers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `officers_email_unique` (`email`);

--
-- Indexes for table `officer_files`
--
ALTER TABLE `officer_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `officer_files_officer_id_foreign` (`officer_id`);

--
-- Indexes for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recommendations_resident_id_foreign` (`resident_id`);

--
-- Indexes for table `residents`
--
ALTER TABLE `residents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `residents_email_unique` (`email`),
  ADD KEY `residents_house_id_foreign` (`house_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `announcement_views`
--
ALTER TABLE `announcement_views`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `conv_participants`
--
ALTER TABLE `conv_participants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `delinquents`
--
ALTER TABLE `delinquents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `families`
--
ALTER TABLE `families`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_records`
--
ALTER TABLE `financial_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `financial_reports`
--
ALTER TABLE `financial_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `households`
--
ALTER TABLE `households`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `household_members`
--
ALTER TABLE `household_members`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `issue_reports`
--
ALTER TABLE `issue_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `issue_responses`
--
ALTER TABLE `issue_responses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `officers`
--
ALTER TABLE `officers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `officer_files`
--
ALTER TABLE `officer_files`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `recommendations`
--
ALTER TABLE `recommendations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `residents`
--
ALTER TABLE `residents`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `announcements_officer_id_foreign` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcement_views`
--
ALTER TABLE `announcement_views`
  ADD CONSTRAINT `announcement_views_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcement_views_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conv_participants`
--
ALTER TABLE `conv_participants`
  ADD CONSTRAINT `conv_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conv_participants_officer_id_foreign` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conv_participants_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delinquents`
--
ALTER TABLE `delinquents`
  ADD CONSTRAINT `delinquents_house_id_foreign` FOREIGN KEY (`house_id`) REFERENCES `households` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `facilities`
--
ALTER TABLE `facilities`
  ADD CONSTRAINT `facilities_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `financial_records`
--
ALTER TABLE `financial_records`
  ADD CONSTRAINT `financial_records_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_records_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `household_members`
--
ALTER TABLE `household_members`
  ADD CONSTRAINT `household_members_house_id_foreign` FOREIGN KEY (`house_id`) REFERENCES `households` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issue_reports`
--
ALTER TABLE `issue_reports`
  ADD CONSTRAINT `issue_reports_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `issue_responses`
--
ALTER TABLE `issue_responses`
  ADD CONSTRAINT `issue_responses_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `issue_responses_issue_id_foreign` FOREIGN KEY (`issue_id`) REFERENCES `issue_reports` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issue_responses_officer_id_foreign` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_officer_id_foreign` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `officer_files`
--
ALTER TABLE `officer_files`
  ADD CONSTRAINT `officer_files_officer_id_foreign` FOREIGN KEY (`officer_id`) REFERENCES `officers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `recommendations`
--
ALTER TABLE `recommendations`
  ADD CONSTRAINT `recommendations_resident_id_foreign` FOREIGN KEY (`resident_id`) REFERENCES `residents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `residents`
--
ALTER TABLE `residents`
  ADD CONSTRAINT `residents_house_id_foreign` FOREIGN KEY (`house_id`) REFERENCES `households` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
