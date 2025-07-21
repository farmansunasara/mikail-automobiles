-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 12:05 PM
-- Server version: 11.6.2-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mikail_automobiles`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Honda Activa 6G', 'Parts for Honda Activa 6G', '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(2, 'Honda Activa 5G', 'Parts for Honda Activa 5G', '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(3, 'TVS Jupiter', 'Parts for TVS Jupiter', '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(4, 'Bajaj Pulsar', 'Parts for Bajaj Pulsar', '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(5, 'Hero Splendor', 'Parts for Hero Splendor', '2025-07-21 04:00:46', '2025-07-21 04:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `state` varchar(255) NOT NULL,
  `gstin` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `mobile`, `address`, `state`, `gstin`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Rajesh Kumar', '9876543210', '123, MG Road, Bangalore', 'Karnataka', '29ABCDE1234F1Z5', 'rajesh.kumar@email.com', '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(2, 'Priya Sharma', '9876543211', '456, Park Street, Mumbai', 'Maharashtra', '27FGHIJ5678K2A6', 'priya.sharma@email.com', '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(3, 'Amit Patel', '9876543212', '789, Ring Road, Ahmedabad', 'Gujarat', '24LMNOP9012Q3B7', 'amit.patel@email.com', '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(4, 'Sunita Singh', '9876543213', '321, Civil Lines, Delhi', 'Delhi', '07RSTUV3456W4C8', 'sunita.singh@email.com', '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(5, 'Vikram Motors', '9876543214', '654, Industrial Area, Chennai', 'Tamil Nadu', '33XYZAB7890D5E9', 'info@vikrammotors.com', '2025-07-21 04:00:46', '2025-07-21 04:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `cgst` decimal(12,2) NOT NULL,
  `sgst` decimal(12,2) NOT NULL,
  `grand_total` decimal(12,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `customer_id`, `invoice_date`, `total_amount`, `cgst`, `sgst`, `grand_total`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'INV-2025-0001', 3, '2025-07-21', 280.00, 39.20, 39.20, 358.40, NULL, '2025-07-21 04:22:59', '2025-07-21 04:22:59');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `gst_rate` decimal(5,2) NOT NULL,
  `cgst` decimal(10,2) NOT NULL,
  `sgst` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `product_id`, `quantity`, `price`, `gst_rate`, `cgst`, `sgst`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 9, 1, 280.00, 28.00, 39.20, 39.20, 280.00, '2025-07-21 04:22:59', '2025-07-21 04:22:59');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_07_21_062104_create_categories_table', 1),
(5, '2025_07_21_062112_create_subcategories_table', 1),
(6, '2025_07_21_062121_create_products_table', 1),
(7, '2025_07_21_062129_create_product_components_table', 1),
(8, '2025_07_21_062135_create_stock_logs_table', 1),
(9, '2025_07_21_062142_create_customers_table', 1),
(10, '2025_07_21_062407_create_invoices_table', 1),
(11, '2025_07_21_062539_create_invoice_items_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `subcategory_id` bigint(20) UNSIGNED NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `hsn_code` varchar(255) NOT NULL,
  `gst_rate` decimal(5,2) NOT NULL,
  `is_composite` tinyint(1) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `subcategory_id`, `color`, `quantity`, `price`, `hsn_code`, `gst_rate`, `is_composite`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Headlight Assembly', 1, 1, 'Clear', 50, 1200.00, '85122000', 18.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:08:49'),
(2, 'Tail Light', 1, 1, 'Red', 75, 450.00, '85122000', 18.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(3, 'Floor Mat', 1, 2, 'Black', 30, 250.00, '40169300', 18.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(4, 'Side Panel Left', 1, 2, 'White', 25, 800.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(5, 'Side Panel Right', 1, 2, 'White', 25, 800.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(6, 'Air Filter', 1, 3, 'White', 100, 180.00, '84213100', 18.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(7, 'Spark Plug', 1, 3, 'Silver', 200, 120.00, '85111000', 18.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(8, 'Engine Oil', 1, 3, 'Golden', 80, 350.00, '27101981', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(9, 'Brake Pad Set', 1, 2, 'Black', 59, 280.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:22:59'),
(10, 'Chain Set', 1, 3, 'Silver', 40, 650.00, '87149900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(11, 'Front Mudguard', 1, 2, 'Black', 35, 320.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(12, 'Front Mudguard', 1, 2, 'Red', 28, 320.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(13, 'Front Mudguard', 1, 2, 'Blue', 22, 320.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(14, 'Front Mudguard', 1, 2, 'White', 18, 320.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(15, 'Rear Mudguard', 1, 2, 'Black', 30, 280.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(16, 'Rear Mudguard', 1, 2, 'Red', 25, 280.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(17, 'Rear Mudguard', 1, 2, 'Blue', 20, 280.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(18, 'Rear Mudguard', 1, 2, 'White', 15, 280.00, '87089900', 28.00, 0, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(19, 'Complete Flooring Kit', 1, 2, 'Mixed', 10, 2100.00, '87089900', 28.00, 1, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(20, 'Service Kit Basic', 1, 3, 'Mixed', 15, 850.00, '84213100', 18.00, 1, NULL, NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `product_components`
--

CREATE TABLE `product_components` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_product_id` bigint(20) UNSIGNED NOT NULL,
  `component_product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity_needed` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_components`
--

INSERT INTO `product_components` (`id`, `parent_product_id`, `component_product_id`, `quantity_needed`, `created_at`, `updated_at`) VALUES
(1, 19, 3, 1, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(2, 19, 4, 1, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(3, 19, 5, 1, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(4, 20, 6, 1, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(5, 20, 7, 1, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(6, 20, 8, 1, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(7, 20, 9, 1, '2025-07-21 04:00:46', '2025-07-21 04:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('3QYBx9GLHiRjwfIaj5Z5Zd6Yog9AoeUFNltG31jQ', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiaUJzWVBPMHJvQjJTVzh5VlE4S2lYR2hHeWU3b2U5WVpSRFRUV0Z3WiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTg6Imh0dHA6Ly9sb2NhbGhvc3QvbWlrYWlsLWF1dG9tb2JpbGVzL3B1YmxpYy9pbnZvaWNlcy9jcmVhdGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1753090746),
('4cLudDsh9BMXhWJNP6aZDrZ9YFeJ9nQrfXxZYGH0', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoia2VtbFR1UkZDb0swa2tYQ1p5SlREaWVvbldSWU1uZ2laOTNGV1l2QyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMwOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvaW52b2ljZXMiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1753091582),
('EsZ5dMNjYu3rjqofCMyER9BmRtnMogAzFL1a693V', 1, '::1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoic09TalRaTkhWeFFHOVpoc050MnpGRTFYdGdVWnhkYjh3VTJnS2REeiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTg6Imh0dHA6Ly9sb2NhbGhvc3QvbWlrYWlsLWF1dG9tb2JpbGVzL3B1YmxpYy9pbnZvaWNlcy9jcmVhdGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1753091148);

-- --------------------------------------------------------

--
-- Table structure for table `stock_logs`
--

CREATE TABLE `stock_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `change_type` enum('inward','outward') NOT NULL,
  `quantity` int(11) NOT NULL,
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_logs`
--

INSERT INTO `stock_logs` (`id`, `product_id`, `change_type`, `quantity`, `previous_quantity`, `new_quantity`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 'inward', 10, 50, 60, 'Testing stock inward functionality - New shipment received', '2025-07-21 04:07:08', '2025-07-21 04:07:08'),
(2, 1, 'outward', 10, 60, 50, 'scrap', '2025-07-21 04:08:49', '2025-07-21 04:08:49'),
(3, 9, 'outward', 1, 60, 59, 'Sale via Invoice #INV-2025-0001', '2025-07-21 04:22:59', '2025-07-21 04:22:59');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Electrical', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(2, 1, 'Body Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(3, 1, 'Engine Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(4, 1, 'Brake System', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(5, 1, 'Suspension', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(6, 2, 'Electrical', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(7, 2, 'Body Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(8, 2, 'Engine Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(9, 2, 'Brake System', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(10, 3, 'Electrical', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(11, 3, 'Body Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(12, 3, 'Engine Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(13, 3, 'Transmission', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(14, 4, 'Electrical', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(15, 4, 'Body Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(16, 4, 'Engine Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(17, 4, 'Fuel System', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(18, 5, 'Electrical', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(19, 5, 'Body Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(20, 5, 'Engine Parts', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46'),
(21, 5, 'Clutch System', NULL, '2025-07-21 04:00:46', '2025-07-21 04:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@mikailautomobiles.com', '2025-07-21 04:00:46', '$2y$12$d87uNXXvZSlRc.sUbw3c8e.PxY02wIiaC9AMrqIVvchQcFaiXCZ1W', 'viUj4DeCPA', '2025-07-21 04:00:46', '2025-07-21 04:00:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_items_invoice_id_foreign` (`invoice_id`),
  ADD KEY `invoice_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `products_category_id_foreign` (`category_id`),
  ADD KEY `products_subcategory_id_foreign` (`subcategory_id`);

--
-- Indexes for table `product_components`
--
ALTER TABLE `product_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_components_parent_product_id_foreign` (`parent_product_id`),
  ADD KEY `product_components_component_product_id_foreign` (`component_product_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_logs_product_id_foreign` (`product_id`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subcategories_category_id_foreign` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `product_components`
--
ALTER TABLE `product_components`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `stock_logs`
--
ALTER TABLE `stock_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoice_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_subcategory_id_foreign` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_components`
--
ALTER TABLE `product_components`
  ADD CONSTRAINT `product_components_component_product_id_foreign` FOREIGN KEY (`component_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_components_parent_product_id_foreign` FOREIGN KEY (`parent_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_logs`
--
ALTER TABLE `stock_logs`
  ADD CONSTRAINT `stock_logs_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
