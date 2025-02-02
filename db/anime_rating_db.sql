-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2025 at 08:20 PM
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
-- Database: `anime_rating_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'andrea', '$2y$10$BHd6Ou3ZJlcmwLxwwmYy7u1jza0X8l97hiQMMjNRDzFoB8xxjpA1.'),
(2, 'test', '$2y$10$/eTOOCZ2z7KfDyGWWtOzOuflEEJFmMm9T2fre.0dtyajAaHSilwoG'),
(3, 'test1', '$2y$10$fhmmAFBBquJf1JO4PC6jX.IWGsi0MJ8oeS/qExOpdcLh25VS5OCQu'),
(4, 'testing', '$2y$10$Nq4nUnA3QROgiW4jV87dRO08xi4kMrADJ1qU4JYQooKoXAjGJyYbW'),
(5, 'andrea1', '$2y$10$fMMhAwOtb51nvmSWrhGCKOrmWBwCcRwk09Tq1ZrMuwzo0ZGX5egoi');

-- --------------------------------------------------------

--
-- Table structure for table `user_ratings`
--

CREATE TABLE `user_ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `anime_id` int(11) NOT NULL,
  `anime_title` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL,
  `anime_comment` text DEFAULT NULL,
  `anime_genres` text DEFAULT NULL,
  `anime_image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_ratings`
--

INSERT INTO `user_ratings` (`id`, `user_id`, `anime_id`, `anime_title`, `rating`, `anime_comment`, `anime_genres`, `anime_image_url`) VALUES
(170, 1, 52991, 'Sousou no Frieren', 7, 'Top 10 all time', 'Adventure, Drama, Fantasy', 'https://cdn.myanimelist.net/images/anime/1015/138006.jpg'),
(182, 1, 5114, 'Fullmetal Alchemist: Brotherhood', 7, 'Slow burn!', 'Action, Adventure, Drama, Fantasy', 'https://cdn.myanimelist.net/images/anime/1208/94745.jpg'),
(183, 1, 60022, 'One Piece Fan Letter', 9, '', 'Action, Adventure, Fantasy', 'https://cdn.myanimelist.net/images/anime/1455/146229.jpg'),
(188, 2, 38524, 'Shingeki no Kyojin Season 3 Part 2', 0, '', 'Action, Drama, Suspense', 'https://cdn.myanimelist.net/images/anime/1517/100633.jpg'),
(189, 2, 60022, 'One Piece Fan Letter', 0, '', 'Action, Adventure, Fantasy', 'https://cdn.myanimelist.net/images/anime/1455/146229.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_ratings`
--
ALTER TABLE `user_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_ratings`
--
ALTER TABLE `user_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_ratings`
--
ALTER TABLE `user_ratings`
  ADD CONSTRAINT `user_ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
