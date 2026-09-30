-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 29, 2026 at 07:35 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `liepajas_taksometrs`
--

-- --------------------------------------------------------

--
-- Table structure for table `darbinieki`
--

CREATE TABLE `darbinieki` (
  `lietotajs_id` int NOT NULL,
  `lietotajvards` varchar(30) NOT NULL,
  `parole` varchar(60) NOT NULL,
  `loma` enum('Admin','Moder') NOT NULL DEFAULT 'Moder',
  `vards` varchar(100) NOT NULL,
  `uzvards` varchar(100) NOT NULL,
  `epasts` varchar(100) NOT NULL,
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `darbinieki`
--

INSERT INTO `darbinieki` (`lietotajs_id`, `lietotajvards`, `parole`, `loma`, `vards`, `uzvards`, `epasts`, `statuss`) VALUES
(1, 'kristovskis', '$2y$10$gBptcL5oSZOio6O6SA.INu6PHlPdy3C.lanzpSzsLi2VXCMZHfHYG', 'Admin', 'Raimonds', 'Kristovskis', 'kristovskis@kristovskis.com', 'aktīvs'),
(2, 'admin', '$2y$10$cXAwG.GihTITLRLPAnJkeOE6GzHXOvtd7op6Ht1QJYTMySjK8PPWO', 'Admin', 'admin', 'admin', 'admin@gmail.com', 'aktīvs'),
(3, 'moder', '$2y$10$3sWhNjEWSkA077UhgnzRT.Jjn.IfBvi506nO6/xrHZkozl/9Vr3iq', 'Moder', 'moder', 'moder', 'moder@gmail.com', 'aktīvs');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `darbinieki`
--
ALTER TABLE `darbinieki`
  ADD PRIMARY KEY (`lietotajs_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `darbinieki`
--
ALTER TABLE `darbinieki`
  MODIFY `lietotajs_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;