-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 27, 2025 at 05:41 PM
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
(6, 'Finance', 'fin'),
(8, 'HR', 'hr');

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
  `category_id` int(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`company_id`, `user_id`, `company_name`, `company_address`, `company_description`, `company_website`, `category_id`, `created_at`) VALUES
(1, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 0, '2025-12-01 13:04:09'),
(2, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 0, '2025-12-01 13:04:09'),
(3, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 0, '2025-12-01 13:04:18'),
(4, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 0, '2025-12-01 13:12:49'),
(5, 2, 'Tushar Babar', 'Rajkot, Gujarat', 'Good with Foreign Clients.', 'abc.in', 0, '2025-12-01 13:12:59'),
(11, 8, 'Carposium Empire', 'Rajkot, Gujarat', 'abc', 'url.com', 6, '2025-12-27 14:09:28'),
(12, 10, 'U & K', 'Rajkot, Gujarat', 'avc', 'abc.in', 1, '2025-12-27 15:22:14');

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `contract_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
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
(31, 11, 'Jenny', 'Wilson', 'abc', 'Java', 1, 'xyz.com', '2025-12-27 15:19:28'),
(32, 12, 'Anjali', 'Parmar', 'Graphic Designer', 'Photoshop', 1, 'abc.com', '2025-12-27 16:33:21');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `jobs_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `budget` decimal(10,2) NOT NULL,
  `status` enum('open','in_progress','completed','close') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`jobs_id`, `company_id`, `category_id`, `title`, `description`, `budget`, `status`, `created_at`) VALUES
(3, 11, 1, 'ABVXC', 'xyz', 500000.00, 'in_progress', '2025-12-08 23:12:40'),
(4, 11, 1, 'xyz', 'avc', 250000.00, 'open', '2025-12-08 23:12:21'),
(6, 12, 1, 'InfoTech', 'abcabcabc', 5000.00, 'open', '2025-12-27 16:39:58');

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

--
-- Dumping data for table `proposals`
--

INSERT INTO `proposals` (`proposal_id`, `job_id`, `freelancer_id`, `cover_letter`, `bid_amount`, `status`, `created_at`) VALUES
(4, 4, 9, 'abc', 200000.00, 'pending', '2025-12-08 23:33:40'),
(7, 3, 9, 'asdvscv', 450000.00, '', '2025-12-09 01:41:23'),
(8, 4, 12, 'abc', 250.00, 'pending', '2025-12-27 16:38:23');

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
(8, 'Carposium Empire', 'tb@gmail.com', 'company', '7894561230', '$2y$10$rfQs.G49AtjMylLesdjMd.fcj7Lv6/pSUlsVQKmEUHpY56WuLzunK', 'uploads/profile/1765214440_Tushar.jpg', '2025-12-08 17:20:40'),
(10, 'U & K', 'uk@gmail.com', 'company', '7894561230', '$2y$10$CeXDcF158EBrw2CYVhJpwuLpw4Q1.ZN6GQAP0NwqMzrAlFmlINtQK', 'uploads/profile/1766848628_1766351456_vivek (1).jpg', '2025-12-27 15:22:14'),
(11, 'Jenny', 'jn@gmail.com', 'freelancer', '7418529630', '$2y$10$bU6P3BTA/YdgpuwBWuWRrO//StWaFq2RmGixf5GDttMdAdQ0nP.wy', 'uploads/profile/1766848711_1766350927_anaya (1).jpg', '2025-12-27 15:19:28'),
(12, 'Anjali', 'ap@gmail.com', 'freelancer', '9876543210', '$2y$10$1MfFrHLK7SBX86B0ygte0eBmS59n//NPfDEgZ.aPdeg9V0X/QQDB.', 'uploads/profile/1766853132_1766351215_isha (1).jpg', '2025-12-27 16:32:47');

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
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `company_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `freelancers`
--
ALTER TABLE `freelancers`
  MODIFY `freelancer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `jobs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `proposal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
