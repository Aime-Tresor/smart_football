-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 02, 2025 at 04:47 PM
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
-- Database: `fa_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assistant_referee`
--

CREATE TABLE `assistant_referee` (
  `assistant_id` int(11) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assistant_referee`
--

INSERT INTO `assistant_referee` (`assistant_id`, `fname`, `lname`, `email`, `password`, `image`, `created_at`) VALUES
(2, 'Rodriguez', 'Man', 'treso1r@gmail.com', '$2y$10$n5j9CqQa3vumA0Nki30cd.O7bI8.C43WTgvnlKJay/cSZnAQA4bW.', 'Capture.PNG', '2025-07-01 08:46:18');

-- --------------------------------------------------------

--
-- Table structure for table `calender`
--

CREATE TABLE `calender` (
  `id` int(11) NOT NULL,
  `home` varchar(30) NOT NULL,
  `away` varchar(30) NOT NULL,
  `week` int(11) NOT NULL,
  `stadium` varchar(40) NOT NULL,
  `date` varchar(30) NOT NULL,
  `time` varchar(10) NOT NULL,
  `season` varchar(30) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'future'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calender`
--

INSERT INTO `calender` (`id`, `home`, `away`, `week`, `stadium`, `date`, `time`, `season`, `status`) VALUES
(1, 'Mukura ', 'As kigali', 1, 'Huye Stadium', '12/8/2022', '3:45:00 PM', '2021 - 2022', 'terminated'),
(2, 'Kiyovu fc', 'Gasogi united', 1, 'Kigali Stadium', '12/8/2022', '4:45:00 PM', '2021 - 2022', 'terminated'),
(3, 'Police fc', 'Rayon sport fc', 1, 'Nyanza Stadium', '13/8/2022', '5:45:00 PM', '2021 - 2022', 'terminated'),
(4, 'Apr fc', 'Marine fc', 1, 'Amahoro Stadium', '13/8/2022', '6:45:00 PM', '2021 - 2022', 'terminated'),
(5, 'Gasogi united', 'As kigali', 2, 'Huye Stadium', '20/8/2022', '7:45:00 PM', '2021 - 2022', 'next'),
(6, 'Apr fc', 'Mukura ', 2, 'Kigali Stadium', '20/8/2022', '3:45:00 PM', '2021 - 2022', 'next'),
(7, 'Kiyovu fc', 'Rayon sport fc', 2, 'Nyanza Stadium', '21/8/2022', '4:45:00 PM', '2021 - 2022', 'next'),
(8, 'Police fc', 'Marine fc', 2, 'Amahoro Stadium', '21/8/2022', '5:45:00 PM', '2021 - 2022', 'next'),
(9, 'Rayon sport fc', 'As kigali', 3, 'Huye Stadium', '30/8/2022', '6:45:00 PM', '2021 - 2022', 'future'),
(10, 'Gasogi united', 'Mukura ', 3, 'Huye Stadium', '31/8/2022', '3:45:00 PM', '2021 - 2022', 'future'),
(11, 'Kiyovu fc', 'Marine fc', 3, 'Kigali Stadium', '1/9/2022', '4:45:00 PM', '2021 - 2022', 'future'),
(12, 'Apr fc', 'Police fc', 3, 'Nyanza Stadium', '1/9/2022', '5:45:00 PM', '2021 - 2022', 'future'),
(13, 'Marine fc', 'As kigali', 4, 'Amahoro Stadium', '10/9/2022', '6:45:00 PM', '2021 - 2022', 'future'),
(14, 'Rayon sport fc', 'Mukura ', 4, 'Huye Stadium', '10/9/2022', '3:45:00 PM', '2021 - 2022', 'future'),
(15, 'Apr fc', 'Gasogi united', 4, 'Kigali Stadium', '10/10/2022', '4:45:00 PM', '2021 - 2022', 'future'),
(16, 'Kiyovu fc', 'Police fc', 4, 'Nyanza Stadium', '10/10/2022', '5:45:00 PM', '2021 - 2022', 'future'),
(17, 'Police fc', 'As kigali', 5, 'Amahoro Stadium', '1/10/1900', '6:45:00 PM', '2021 - 2022', 'future'),
(18, 'Marine fc', 'Mukura ', 5, 'Huye Stadium', '10/9/2022', '3:45:00 PM', '2021 - 2022', 'future'),
(19, 'Rayon sport fc', 'Gasogi united', 5, 'Huye Stadium', '12/8/2022', '4:45:00 PM', '2021 - 2022', 'future'),
(20, 'Apr fc', 'Kiyovu fc', 5, 'Kigali Stadium', '12/8/2022', '5:45:00 PM', '2021 - 2022', 'future'),
(21, 'Kiyovu fc', 'As kigali', 6, 'Huye Stadium', '13/8/2022', '6:45:00 PM', '2021 - 2022', 'future'),
(22, 'Police fc', 'Mukura ', 6, 'Kigali Stadium', '13/8/2022', '3:45:00 PM', '2021 - 2022', 'future'),
(23, 'Marine fc', 'Gasogi united', 6, 'Nyanza Stadium', '20/8/2022', '4:45:00 PM', '2021 - 2022', 'future'),
(24, 'Apr fc', 'Rayon sport fc', 6, 'Amahoro Stadium', '20/8/2022', '5:45:00 PM', '2021 - 2022', 'future'),
(25, 'Apr fc', 'As kigali', 7, 'Huye Stadium', '21/8/2022', '6:45:00 PM', '2021 - 2022', 'future'),
(26, 'Kiyovu fc', 'Mukura ', 7, 'Kigali Stadium', '21/8/2022', '3:45:00 PM', '2021 - 2022', 'future'),
(27, 'Police fc', 'Gasogi united', 7, 'Nyanza Stadium', '30/8/2022', '4:45:00 PM', '2021 - 2022', 'future'),
(28, 'Marine fc', 'Rayon sport fc', 7, 'Amahoro Stadium', '31/8/2022', '5:45:00 PM', '2021 - 2022', 'future');

-- --------------------------------------------------------

--
-- Table structure for table `cards`
--

CREATE TABLE `cards` (
  `card_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `card_type` enum('yellow','double_yellow','red') NOT NULL,
  `match_id` int(11) NOT NULL,
  `card_time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cards`
--

INSERT INTO `cards` (`card_id`, `member_id`, `card_type`, `match_id`, `card_time`, `created_at`) VALUES
(1, 1, 'yellow', 2, NULL, '2025-07-01 06:40:54');

-- --------------------------------------------------------

--
-- Table structure for table `fa_user`
--

CREATE TABLE `fa_user` (
  `id` int(11) NOT NULL,
  `names` varchar(30) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fa_user`
--

INSERT INTO `fa_user` (`id`, `names`, `username`, `password`) VALUES
(1, 'tresor', 'tresor', '123');

-- --------------------------------------------------------

--
-- Table structure for table `match`
--

CREATE TABLE `match` (
  `id` int(11) NOT NULL,
  `team1_id` int(11) NOT NULL,
  `team2_id` int(11) NOT NULL,
  `week` int(11) NOT NULL,
  `stadium` varchar(100) NOT NULL,
  `match_date` date NOT NULL,
  `match_time` time NOT NULL,
  `season` varchar(20) NOT NULL,
  `status` enum('upcoming','live','completed') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `team1_goal` int(11) DEFAULT NULL,
  `team2_goal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `match`
--

INSERT INTO `match` (`id`, `team1_id`, `team2_id`, `week`, `stadium`, `match_date`, `match_time`, `season`, `status`, `created_at`, `team1_goal`, `team2_goal`) VALUES
(1, 4, 9, 24, '', '2025-07-02', '09:00:00', '2024', 'live', '2025-06-06 11:22:49', 0, 0),
(2, 4, 9, 23, '', '2025-05-05', '13:22:00', '2024', 'completed', '2025-06-03 11:23:54', 1, 2),
(5, 4, 6, 26, 'Stade Regional', '2025-06-24', '19:03:00', '2024 - 2025', 'upcoming', '2025-06-30 23:00:09', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `match_day_reports`
--

CREATE TABLE `match_day_reports` (
  `report_id` int(11) NOT NULL,
  `team_member` int(11) NOT NULL,
  `team` int(11) NOT NULL,
  `goal` varchar(2) NOT NULL,
  `goal_min` varchar(3) NOT NULL,
  `card` varchar(10) NOT NULL,
  `card_min` varchar(3) NOT NULL,
  `week` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referee`
--

CREATE TABLE `referee` (
  `referee_id` int(11) NOT NULL,
  `fname` varchar(40) NOT NULL,
  `lname` varchar(40) NOT NULL,
  `image` varchar(255) NOT NULL,
  `email` varchar(40) NOT NULL,
  `password` varchar(50) NOT NULL,
  `status` varchar(11) NOT NULL DEFAULT 'inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referee`
--

INSERT INTO `referee` (`referee_id`, `fname`, `lname`, `image`, `email`, `password`, `status`) VALUES
(1, 'Joel M', '', '', '57joel39@gmail.com', '123', '1'),
(2, 'Fistor', '', '', '57joel38@gmail.com', '123', '1');

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `team_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `logon` varchar(255) NOT NULL,
  `stadium` varchar(30) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`team_id`, `name`, `logon`, `stadium`, `username`, `password`) VALUES
(4, 'Kiyovu fc', 'Kiyovu.jpg', 'Stade Regional', 'Kiyovufc', 'Kiyovufc'),
(6, 'Police fc', 'Police.jpg', 'Bugesera Stadium', 'Policefc', 'Policefc'),
(9, 'Marine fc', 'Marine.jpg', 'Umuganda Stadium', 'marinefc', 'marinefc');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `member_id` int(11) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `number` int(11) DEFAULT NULL,
  `role_in_team` varchar(100) DEFAULT NULL,
  `post` varchar(255) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `team` varchar(100) DEFAULT NULL,
  `yellow` int(11) DEFAULT 0,
  `double_yellow` int(11) DEFAULT 0,
  `red` int(11) DEFAULT 0,
  `contract_duration` varchar(50) DEFAULT NULL,
  `contract_value` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`member_id`, `fname`, `lname`, `number`, `role_in_team`, `post`, `position`, `team`, `yellow`, `double_yellow`, `red`, `contract_duration`, `contract_value`, `created_at`) VALUES
(1, 'MINANI', 'Joel', 2, 'player', NULL, 'Goal Keeper', '4', 0, 1, 0, '2025-06-04', 200000.00, '2025-06-03 11:57:37'),
(2, 'mbaza', 'Bosco', NULL, 'staff', 'HC', NULL, '9', 0, 0, 1, '2025-06-17', 2000.00, '2025-06-03 11:59:14'),
(3, 'Rodriguez', 'Man', 8, 'player', NULL, 'Attacker', '4', 0, 0, 0, '2025-07-01', 400000.00, '2025-06-30 23:05:52');

-- --------------------------------------------------------

--
-- Table structure for table `transfer`
--

CREATE TABLE `transfer` (
  `id` int(11) NOT NULL,
  `team_from` int(11) NOT NULL,
  `team_to` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `requestDate` date NOT NULL DEFAULT current_timestamp(),
  `aprovalDate` date DEFAULT NULL,
  `rejectDate` date DEFAULT NULL,
  `completeDate` date DEFAULT NULL,
  `member_id` int(11) NOT NULL,
  `post` enum('player','staff') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transfer`
--

INSERT INTO `transfer` (`id`, `team_from`, `team_to`, `status`, `requestDate`, `aprovalDate`, `rejectDate`, `completeDate`, `member_id`, `post`) VALUES
(16, 4, 6, 1, '2025-07-01', NULL, NULL, NULL, 3, 'player');

-- --------------------------------------------------------

--
-- Table structure for table `weekly_fixtures`
--

CREATE TABLE `weekly_fixtures` (
  `fixture_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `referee` int(11) NOT NULL,
  `assistant1` int(11) NOT NULL,
  `assistant2` int(11) NOT NULL,
  `official` int(11) NOT NULL,
  `access_code` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `weekly_fixtures`
--

INSERT INTO `weekly_fixtures` (`fixture_id`, `match_id`, `referee`, `assistant1`, `assistant2`, `official`, `access_code`) VALUES
(4, 5, 1, 2, 2, 1, '883777'),
(5, 1, 1, 2, 2, 2, '827394');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assistant_referee`
--
ALTER TABLE `assistant_referee`
  ADD PRIMARY KEY (`assistant_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `calender`
--
ALTER TABLE `calender`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cards`
--
ALTER TABLE `cards`
  ADD PRIMARY KEY (`card_id`),
  ADD KEY `fk_cards_member` (`member_id`),
  ADD KEY `fk_cards_match` (`match_id`);

--
-- Indexes for table `fa_user`
--
ALTER TABLE `fa_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `match`
--
ALTER TABLE `match`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team1_id` (`team1_id`),
  ADD KEY `team2_id` (`team2_id`);

--
-- Indexes for table `match_day_reports`
--
ALTER TABLE `match_day_reports`
  ADD PRIMARY KEY (`report_id`);

--
-- Indexes for table `referee`
--
ALTER TABLE `referee`
  ADD PRIMARY KEY (`referee_id`),
  ADD UNIQUE KEY `username` (`email`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`team_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `transfer`
--
ALTER TABLE `transfer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `team_from` (`team_from`),
  ADD KEY `team_to` (`team_to`);

--
-- Indexes for table `weekly_fixtures`
--
ALTER TABLE `weekly_fixtures`
  ADD PRIMARY KEY (`fixture_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assistant_referee`
--
ALTER TABLE `assistant_referee`
  MODIFY `assistant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `calender`
--
ALTER TABLE `calender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `cards`
--
ALTER TABLE `cards`
  MODIFY `card_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `fa_user`
--
ALTER TABLE `fa_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `match`
--
ALTER TABLE `match`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `match_day_reports`
--
ALTER TABLE `match_day_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referee`
--
ALTER TABLE `referee`
  MODIFY `referee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transfer`
--
ALTER TABLE `transfer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `weekly_fixtures`
--
ALTER TABLE `weekly_fixtures`
  MODIFY `fixture_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cards`
--
ALTER TABLE `cards`
  ADD CONSTRAINT `fk_cards_match` FOREIGN KEY (`match_id`) REFERENCES `match` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cards_member` FOREIGN KEY (`member_id`) REFERENCES `team_members` (`member_id`) ON DELETE CASCADE;

--
-- Constraints for table `match`
--
ALTER TABLE `match`
  ADD CONSTRAINT `match_ibfk_1` FOREIGN KEY (`team1_id`) REFERENCES `team` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `match_ibfk_2` FOREIGN KEY (`team2_id`) REFERENCES `team` (`team_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
