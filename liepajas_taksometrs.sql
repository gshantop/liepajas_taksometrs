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


CREATE TABLE `tarifi` (
  `tarifs_id` int NOT NULL AUTO_INCREMENT,
  `kods` varchar(10) NOT NULL,
  `nosaukums` varchar(50) NOT NULL,
  `automasina` varchar(100) NOT NULL,
  `vietu_skaits` tinyint NOT NULL DEFAULT 4,
  `iekrausanas_maksa` decimal(6,2) NOT NULL,
  `cena_km` decimal(6,2) NOT NULL,
  `cena_min` decimal(6,2) NOT NULL,
  `birka` varchar(30) NOT NULL DEFAULT '',
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`tarifs_id`),
  UNIQUE KEY `uq_tarifi_kods` (`kods`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `tarifi` (`tarifs_id`, `kods`, `nosaukums`, `automasina`, `vietu_skaits`, `iekrausanas_maksa`, `cena_km`, `cena_min`, `birka`, `statuss`) VALUES
(1, 'eko', 'Ekonomiskā',    'Škoda Fabia / VW Polo',        4, 1.50, 0.75, 0.10, '',           'aktīvs'),
(2, 'kom', 'Komforta',      'Škoda Octavia / VW Passat',    4, 2.00, 0.95, 0.12, 'Populārākā', 'aktīvs'),
(3, 'uni', 'Universālis',   'Škoda Superb Combi',           4, 2.50, 1.05, 0.15, '',           'aktīvs'),
(4, 'bus', 'Mikroautobuss', 'VW Caravelle (līdz 8 vietām)', 8, 4.00, 1.35, 0.20, 'Grupām',     'aktīvs');

CREATE TABLE autoparks (
    auto_id INT AUTO_INCREMENT PRIMARY KEY,
    numurs VARCHAR(10) NOT NULL UNIQUE,
    marka VARCHAR(50) NOT NULL,
    modelis VARCHAR(50) NOT NULL,
    gads SMALLINT NOT NULL,
    krasa VARCHAR(30) NOT NULL DEFAULT '',
    vietu_skaits TINYINT NOT NULL DEFAULT 4,
    foto VARCHAR(255) NULL,
    statuss ENUM('aktīvs', 'dzēsts') NOT NULL DEFAULT 'aktīvs'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

INSERT INTO autoparks (numurs, marka, modelis, gads, krasa, vietu_skaits) VALUES
('AB1234', 'Toyota',        'Prius',         2020, 'Balta',      4),
('CD5678', 'Skoda',         'Octavia',       2019, 'Melna',      4),
('EF9012', 'Volkswagen',    'Passat',        2021, 'Pelēka',     4),
('GH3456', 'Tesla',         'Model 3',       2022, 'Balta',      4),
('JK7890', 'Toyota',        'Corolla',       2018, 'Sudraba',    4),
('LM2345', 'Mercedes-Benz', 'E-Class',       2021, 'Melna',      4),
('NP6789', 'Ford',          'Tourneo',       2020, 'Zila',       7),
('RS0123', 'Mercedes-Benz', 'V-Class',       2022, 'Melna',      7),
('TU4567', 'Volkswagen',    'Caravelle',     2019, 'Balta',      8),
('VW8901', 'Hyundai',       'Ioniq',         2021, 'Zaļa',       4);

CREATE TABLE soferi (
    sofers_id INT AUTO_INCREMENT PRIMARY KEY,
    vards VARCHAR(50) NOT NULL,
    uzvards VARCHAR(50) NOT NULL,
    talrunis VARCHAR(20) NOT NULL,
    licences_nr VARCHAR(20) NOT NULL UNIQUE,
    auto_id INT NULL,
    statuss ENUM('aktīvs', 'dzēsts') NOT NULL DEFAULT 'aktīvs',
    FOREIGN KEY (auto_id) REFERENCES autoparks(auto_id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

INSERT INTO soferi (vards, uzvards, talrunis, licences_nr, auto_id) VALUES
('Jānis',  'Bērziņš', '+37120000001', 'LV1234567', 1),
('Andris', 'Ozols',   '+37120000002', 'LV2345678', 2),
('Mārtiņš','Kalniņš', '+37120000003', 'LV3456789', 3),
('Edgars', 'Liepa',   '+37120000004', 'LV4567890', NULL);

CREATE TABLE klienti (
    klients_id INT AUTO_INCREMENT PRIMARY KEY,
    vards VARCHAR(100) NOT NULL,
    talrunis VARCHAR(20) NOT NULL UNIQUE,
    epasts VARCHAR(100) NULL,
    izveidots DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statuss ENUM('aktīvs', 'dzēsts') NOT NULL DEFAULT 'aktīvs'
) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

INSERT INTO klienti (vards, talrunis, epasts) VALUES
('Anna Siliņa',   '+37125000001', 'anna@example.com'),
('Pēteris Krūmiņš','+37125000002', NULL),
('Līga Vītola',   '+37125000003', 'liga@example.com');