-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 05:39 AM
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
-- Database: `workaholic`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `admin_name`, `admin_email`, `admin_password`, `created_at`) VALUES
(2, 'Drashti', 'db@gmail.com', '$2y$10$/WJuHGwao2VflQS7pXNUTu5983dUK3auKlEs8M6jf8vpHA4HLLfBG', '2025-12-01 17:36:14'),
(4, 'Prachi', 'pj@gamil.com', '$2y$10$9peBTEr5SWolI3FDcPYnmeFdtBG9DA1ya8syJ5h6SoFsrsNysl.ze', '2025-12-01 18:15:18'),
(13, 'Manu', 'md@gmail.com', '$2y$10$OR4ZscOlinsXj5yLWtYE6.v.B495gflB7r4pD/BNC.rIIAPB6Hqp2', '2025-12-02 00:07:00');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `category_description`) VALUES
(1, 'IT', 'avc'),
(3, 'It', 'wer'),
(6, 'Finance', 'fin');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `company_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_address` text NOT NULL,
  `company_description` text NOT NULL,
  `company_website` varchar(255) NOT NULL,
  `business_type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `user_id`, `company_name`, `company_address`, `company_description`, `company_website`, `business_type`, `created_at`) VALUES
(1, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 'IT', '2025-12-01 13:04:09'),
(2, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 'IT', '2025-12-01 13:04:09'),
(3, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 'IT', '2025-12-01 13:04:18'),
(4, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 'IT', '2025-12-01 13:12:49'),
(5, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 'IT', '2025-12-01 13:12:59'),
(11, 8, 'Carposium Empire', 'Rajkot, Gujarat', 'abc', 'url.com', 'IT', '2025-12-02 03:15:45');

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `contract_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `agreed_amount` decimal(10,2) NOT NULL,
  `status` enum('active','completed','cancelled','') NOT NULL DEFAULT 'active',
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `freelancers`
--

CREATE TABLE `freelancers` (
  `freelancer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `bio` text NOT NULL,
  `skills` text NOT NULL,
  `experience_year` int(11) NOT NULL,
  `portfolio_url` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancers`
--

INSERT INTO `freelancers` (`freelancer_id`, `user_id`, `fname`, `lname`, `bio`, `skills`, `experience_year`, `portfolio_url`, `created_at`) VALUES
(1, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:14:44'),
(2, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:15:14'),
(3, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:15:19'),
(4, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:15:29'),
(5, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:15:36'),
(6, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:18:12'),
(7, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:20:52'),
(8, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:20:58'),
(9, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:31:55'),
(10, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:31:59'),
(11, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:34:01'),
(12, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:34:15'),
(13, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:34:27'),
(14, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:37:52'),
(15, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:37:53'),
(16, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:38:14'),
(17, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:38:22'),
(18, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:41:15'),
(19, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:46:38'),
(20, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:46:42'),
(21, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:46:48'),
(22, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:46:50'),
(23, 1, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-01 13:47:37'),
(25, 5, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-02 01:29:18');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `jobs_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `budget` decimal(10,2) NOT NULL,
  `status` enum('open','in_progress','completed','close') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `send_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('card','upi','bank') NOT NULL,
  `payment_date` datetime NOT NULL,
  `status` enum('pending','released','failed') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `proposals`
--

CREATE TABLE `proposals` (
  `proposal_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `cover_letter` text NOT NULL,
  `bid_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `reviewee_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `role` enum('company','freelancer') NOT NULL,
  `user_phone` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `profile_img` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `user_email`, `role`, `user_phone`, `user_password`, `profile_img`, `created_at`) VALUES
(5, 'anjali', 'ap@gmail.com', 'freelancer', '9876543210', '$2y$10$sHntiM.9XikcZ/bPVkl11uu1ZNdsY9z0eje2IE3dH0a5e/tHZNS1S', '1764638958_WhatsApp Image 2025-01-23 at 17.17.37_d6f5189c.jpg', '2025-12-02 01:29:18'),
(8, 'tushar', 'tb@gmail.com', 'company', '7894561230', '$2y$10$rfQs.G49AtjMylLesdjMd.fcj7Lv6/pSUlsVQKmEUHpY56WuLzunK', '1764645345_Tuchu.jpg', '2025-12-02 03:15:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`company_id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contract_id`);

--
-- Indexes for table `freelancers`
--
ALTER TABLE `freelancers`
  ADD PRIMARY KEY (`freelancer_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`jobs_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`);

--
-- Indexes for table `proposals`
--
ALTER TABLE `proposals`
  ADD PRIMARY KEY (`proposal_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `freelancers`
--
ALTER TABLE `freelancers`
  MODIFY `freelancer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `jobs_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `proposals`
--
ALTER TABLE `proposals`
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
