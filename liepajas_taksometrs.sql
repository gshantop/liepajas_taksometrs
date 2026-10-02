DROP DATABASE IF EXISTS `liepajas_taksometrs`;

CREATE DATABASE `liepajas_taksometrs`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE `liepajas_taksometrs`;

SET NAMES utf8mb4;


CREATE TABLE `darbinieki` (
  `lietotajs_id` int NOT NULL AUTO_INCREMENT,
  `lietotajvards` varchar(30) NOT NULL,
  `parole` varchar(60) NOT NULL,
  `loma` enum('Admin','Moder') NOT NULL DEFAULT 'Moder',
  `vards` varchar(100) NOT NULL,
  `uzvards` varchar(100) NOT NULL,
  `epasts` varchar(100) NOT NULL,
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`lietotajs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `darbinieki` (`lietotajs_id`, `lietotajvards`, `parole`, `loma`, `vards`, `uzvards`, `epasts`, `statuss`) VALUES
(1, 'kristovskis', '$2y$10$gBptcL5oSZOio6O6SA.INu6PHlPdy3C.lanzpSzsLi2VXCMZHfHYG', 'Admin', 'Raimonds', 'Kristovskis', 'kristovskis@kristovskis.com', 'aktīvs'),
(2, 'admin', '$2y$10$cXAwG.GihTITLRLPAnJkeOE6GzHXOvtd7op6Ht1QJYTMySjK8PPWO', 'Admin', 'admin', 'admin', 'admin@gmail.com', 'aktīvs'),
(3, 'moder', '$2y$10$3sWhNjEWSkA077UhgnzRT.Jjn.IfBvi506nO6/xrHZkozl/9Vr3iq', 'Moder', 'moder', 'moder', 'moder@gmail.com', 'aktīvs');



CREATE TABLE `tarifi` (
  `tarifs_id` int NOT NULL AUTO_INCREMENT,
  `kods` varchar(10) NOT NULL,
  `nosaukums` varchar(50) NOT NULL,
  `auto_id` int NULL,
  `automasina` varchar(100) NOT NULL,
  `vietu_skaits` tinyint NOT NULL DEFAULT 4,
  `iekrausanas_maksa` decimal(6,2) NOT NULL,
  `cena_km` decimal(6,2) NOT NULL,
  `cena_min` decimal(6,2) NOT NULL,
  `birka` varchar(30) NOT NULL DEFAULT '',
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`tarifs_id`),
  UNIQUE KEY `uq_tarifi_kods` (`kods`),
  CONSTRAINT `fk_tarifi_auto` FOREIGN KEY (`auto_id`) REFERENCES `autoparks` (`auto_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `tarifi` (`kods`, `nosaukums`, `auto_id`, `automasina`, `vietu_skaits`, `iekrausanas_maksa`, `cena_km`, `cena_min`, `birka`) VALUES
('eko', 'Ekonomiskā',    12, 'Volkswagen Polo',     4, 1.50, 0.75, 0.10, ''),
('kom', 'Komforta',      2,  'Skoda Octavia',       4, 2.00, 0.95, 0.12, 'Populārākā'),
('uni', 'Universālis',   11, 'Skoda Superb',        4, 2.50, 1.05, 0.15, ''),
('bus', 'Mikroautobuss', 9,  'Volkswagen Caravelle', 8, 4.00, 1.35, 0.20, 'Grupām');


CREATE TABLE `autoparks` (
  `auto_id` int NOT NULL AUTO_INCREMENT,
  `numurs` varchar(10) NOT NULL,
  `marka` varchar(50) NOT NULL,
  `modelis` varchar(50) NOT NULL,
  `gads` smallint NOT NULL,
  `krasa` varchar(30) NOT NULL DEFAULT '',
  `vietu_skaits` tinyint NOT NULL DEFAULT 4,
  `foto` varchar(255) NULL,
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`auto_id`),
  UNIQUE KEY `uq_autoparks_numurs` (`numurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `autoparks` (`numurs`, `marka`, `modelis`, `gads`, `krasa`, `vietu_skaits`) VALUES
('AB1234', 'Toyota',        'Prius',     2020, 'Balta',   4),
('CD5678', 'Skoda',         'Octavia',   2019, 'Melna',   4),
('EF9012', 'Volkswagen',    'Passat',    2021, 'Pelēka',  4),
('GH3456', 'Tesla',         'Model 3',   2022, 'Balta',   4),
('JK7890', 'Toyota',        'Corolla',   2018, 'Sudraba', 4),
('LM2345', 'Mercedes-Benz', 'E-Class',   2021, 'Melna',   4),
('NP6789', 'Ford',          'Tourneo',   2020, 'Zila',    7),
('RS0123', 'Mercedes-Benz', 'V-Class',   2022, 'Melna',   7),
('TU4567', 'Volkswagen',    'Caravelle', 2019, 'Balta',   8),
('VW8901', 'Hyundai',       'Ioniq',     2021, 'Zaļa',    4),
('MN2468', 'Skoda',         'Superb',    2021, 'Melna',   4),
('PR1357', 'Volkswagen',    'Polo',      2020, 'Sarkana', 4),
('ST9753', 'Toyota',        'Camry',     2022, 'Pelēka',  4),
('UV8642', 'Tesla',         'Model Y',   2023, 'Balta',   4),
('KL3579', 'Mercedes-Benz', 'Vito',      2020, 'Melna',   8);


CREATE TABLE `soferi` (
  `sofers_id` int NOT NULL AUTO_INCREMENT,
  `vards` varchar(50) NOT NULL,
  `uzvards` varchar(50) NOT NULL,
  `talrunis` varchar(20) NOT NULL,
  `licences_nr` varchar(20) NOT NULL,
  `auto_id` int NULL,
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`sofers_id`),
  UNIQUE KEY `uq_soferi_licence` (`licences_nr`),
  CONSTRAINT `fk_soferi_auto` FOREIGN KEY (`auto_id`) REFERENCES `autoparks` (`auto_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `soferi` (`vards`, `uzvards`, `talrunis`, `licences_nr`, `auto_id`) VALUES
('Jānis',    'Bērziņš',  '+37120000001', 'LV1234567', 1),
('Andris',   'Ozols',    '+37120000002', 'LV2345678', 2),
('Mārtiņš',  'Kalniņš',  '+37120000003', 'LV3456789', 3),
('Edgars',   'Liepa',    '+37120000004', 'LV4567890', NULL),
('Kristaps', 'Krūmiņš',  '+37120000005', 'LV5678901', 4),
('Roberts',  'Vītols',   '+37120000006', 'LV6789012', 5),
('Aivars',   'Zariņš',   '+37120000007', 'LV7890123', 6),
('Normunds', 'Jansons',  '+37120000008', 'LV8901234', 7),
('Ilgvars',  'Ozoliņš',  '+37120000009', 'LV9012345', 8),
('Dainis',   'Siliņš',   '+37120000010', 'LV0123456', 9),
('Gatis',    'Balodis',  '+37120000011', 'LV1122334', 10),
('Raivis',   'Eglītis',  '+37120000012', 'LV2233445', 11),
('Uldis',    'Pētersons','+37120000013', 'LV3344556', 12),
('Valdis',   'Kļaviņš',  '+37120000014', 'LV4455667', 13),
('Artūrs',   'Mednis',   '+37120000015', 'LV5566778', 14);



CREATE TABLE `klienti` (
  `klients_id` int NOT NULL AUTO_INCREMENT,
  `vards` varchar(100) NOT NULL,
  `talrunis` varchar(20) NOT NULL,
  `epasts` varchar(100) NULL,
  `izveidots` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`klients_id`),
  UNIQUE KEY `uq_klienti_talrunis` (`talrunis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `klienti` (`vards`, `talrunis`, `epasts`, `izveidots`) VALUES
('Anna Siliņa',      '+37125000001', 'anna@example.com',   '2026-09-01 10:12:00'),
('Pēteris Krūmiņš',  '+37125000002', NULL,                 '2026-09-02 14:30:00'),
('Līga Vītola',      '+37125000003', 'liga@example.com',   '2026-09-03 09:05:00'),
('Jānis Ozoliņš',    '+37125000004', 'janis.o@example.com','2026-09-04 18:40:00'),
('Ieva Kalniņa',     '+37125000005', 'ieva.k@example.com', '2026-09-05 11:20:00'),
('Mārcis Zariņš',    '+37125000006', NULL,                 '2026-09-06 20:15:00'),
('Laura Bērziņa',    '+37125000007', 'laura.b@example.com','2026-09-07 08:45:00'),
('Andrejs Petrovs',  '+37125000008', NULL,                 '2026-09-08 16:00:00'),
('Dace Liepiņa',     '+37125000009', 'dace.l@example.com', '2026-09-09 12:10:00'),
('Roberts Eglītis',  '+37125000010', NULL,                 '2026-09-10 19:25:00'),
('Sandra Jansone',   '+37125000011', 'sandra.j@example.com','2026-09-11 07:55:00'),
('Oskars Balodis',   '+37125000012', NULL,                 '2026-09-12 15:35:00'),
('Zane Krastiņa',    '+37125000013', 'zane.k@example.com', '2026-09-13 13:00:00'),
('Viktors Ivanovs',  '+37125000014', NULL,                 '2026-09-14 21:10:00'),
('Kristīne Rozīte',  '+37125000015', 'kristine.r@example.com','2026-09-15 10:50:00');


CREATE TABLE `braucieni` (
  `brauciens_id` int NOT NULL AUTO_INCREMENT,
  `klients_id` int NOT NULL,
  `sofers_id` int NULL,
  `tarifs_id` int NOT NULL,
  `no_adrese` varchar(150) NOT NULL,
  `uz_adrese` varchar(150) NOT NULL,
  `km` decimal(6,2) NULL,
  `minutes` smallint unsigned NULL,
  `cena` decimal(8,2) NULL,
  `izveidots` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statuss` enum('jauns','procesā','pabeigts','atcelts','dzēsts') NOT NULL DEFAULT 'jauns',
  PRIMARY KEY (`brauciens_id`),
  CONSTRAINT `fk_braucieni_klients` FOREIGN KEY (`klients_id`) REFERENCES `klienti` (`klients_id`),
  CONSTRAINT `fk_braucieni_sofers` FOREIGN KEY (`sofers_id`) REFERENCES `soferi` (`sofers_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_braucieni_tarifs` FOREIGN KEY (`tarifs_id`) REFERENCES `tarifi` (`tarifs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP DATABASE IF EXISTS `liepajas_taksometrs`;

CREATE DATABASE `liepajas_taksometrs`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE `liepajas_taksometrs`;

SET NAMES utf8mb4;


CREATE TABLE `darbinieki` (
  `lietotajs_id` int NOT NULL AUTO_INCREMENT,
  `lietotajvards` varchar(30) NOT NULL,
  `parole` varchar(60) NOT NULL,
  `loma` enum('Admin','Moder') NOT NULL DEFAULT 'Moder',
  `vards` varchar(100) NOT NULL,
  `uzvards` varchar(100) NOT NULL,
  `epasts` varchar(100) NOT NULL,
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`lietotajs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `darbinieki` (`lietotajs_id`, `lietotajvards`, `parole`, `loma`, `vards`, `uzvards`, `epasts`, `statuss`) VALUES
(1, 'kristovskis', '$2y$10$gBptcL5oSZOio6O6SA.INu6PHlPdy3C.lanzpSzsLi2VXCMZHfHYG', 'Admin', 'Raimonds', 'Kristovskis', 'kristovskis@kristovskis.com', 'aktīvs'),
(2, 'admin', '$2y$10$cXAwG.GihTITLRLPAnJkeOE6GzHXOvtd7op6Ht1QJYTMySjK8PPWO', 'Admin', 'admin', 'admin', 'admin@gmail.com', 'aktīvs'),
(3, 'moder', '$2y$10$3sWhNjEWSkA077UhgnzRT.Jjn.IfBvi506nO6/xrHZkozl/9Vr3iq', 'Moder', 'moder', 'moder', 'moder@gmail.com', 'aktīvs');



CREATE TABLE `autoparks` (
  `auto_id` int NOT NULL AUTO_INCREMENT,
  `numurs` varchar(10) NOT NULL,
  `marka` varchar(50) NOT NULL,
  `modelis` varchar(50) NOT NULL,
  `gads` smallint NOT NULL,
  `krasa` varchar(30) NOT NULL DEFAULT '',
  `vietu_skaits` tinyint NOT NULL DEFAULT 4,
  `foto` varchar(255) NULL,
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`auto_id`),
  UNIQUE KEY `uq_autoparks_numurs` (`numurs`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `autoparks` (`numurs`, `marka`, `modelis`, `gads`, `krasa`, `vietu_skaits`, `foto`) VALUES
('AB1234', 'Toyota',        'Prius',     2020, 'Balta',   4, '736de1488a2f7bd1.jpg'),
('CD5678', 'Skoda',         'Octavia',   2019, 'Melna',   4, '887e77b8a7e492ae.jpg'),
('EF9012', 'Volkswagen',    'Passat',    2021, 'Pelēka',  4, 'f3ef532bd5b8e8d6.webp'),
('GH3456', 'Tesla',         'Model 3',   2022, 'Balta',   4, 'c4f781ff2d716e79.jpg'),
('JK7890', 'Toyota',        'Corolla',   2018, 'Sudraba', 4, 'bcd001f0ccf06c27.webp'),
('LM2345', 'Mercedes-Benz', 'E-Class',   2021, 'Melna',   4, '84694d26e0c18717.jpg'),
('NP6789', 'Ford',          'Tourneo',   2020, 'Zila',    7, 'ee031633f6925468.jpg'),
('RS0123', 'Mercedes-Benz', 'V-Class',   2022, 'Melna',   7, 'edc02658dd32e18e.jpg'),
('TU4567', 'Volkswagen',    'Caravelle', 2019, 'Balta',   8, '1ccd78c0b9833196.jpg'),
('VW8901', 'Hyundai',       'Ioniq',     2021, 'Zaļa',    4, '2454b30bd4d79ae6.jpg'),
('MN2468', 'Skoda',         'Superb',    2021, 'Melna',   4, 'e01c354372f13ff5.jpg'),
('PR1357', 'Volkswagen',    'Polo',      2020, 'Sarkana', 4, '729ac9d672153f50.jpg'),
('ST9753', 'Toyota',        'Camry',     2022, 'Pelēka',  4, 'ec416cbe582e7c3c.jpg'),
('UV8642', 'Tesla',         'Model Y',   2023, 'Balta',   4, '56b88fc59715ca46.jpg'),
('KL3579', 'Mercedes-Benz', 'Vito',      2020, 'Melna',   8, 'f67be6aa791fc0c7.jpg');


CREATE TABLE `tarifi` (
  `tarifs_id` int NOT NULL AUTO_INCREMENT,
  `kods` varchar(10) NOT NULL,
  `nosaukums` varchar(50) NOT NULL,
  `auto_id` int NULL,
  `automasina` varchar(100) NOT NULL,
  `vietu_skaits` tinyint NOT NULL DEFAULT 4,
  `iekrausanas_maksa` decimal(6,2) NOT NULL,
  `cena_km` decimal(6,2) NOT NULL,
  `cena_min` decimal(6,2) NOT NULL,
  `birka` varchar(30) NOT NULL DEFAULT '',
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`tarifs_id`),
  UNIQUE KEY `uq_tarifi_kods` (`kods`),
  CONSTRAINT `fk_tarifi_auto` FOREIGN KEY (`auto_id`) REFERENCES `autoparks` (`auto_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `tarifi` (`kods`, `nosaukums`, `auto_id`, `automasina`, `vietu_skaits`, `iekrausanas_maksa`, `cena_km`, `cena_min`, `birka`) VALUES
('eko', 'Ekonomiskā',    12, 'Volkswagen Polo',     4, 1.50, 0.75, 0.10, ''),
('kom', 'Komforta',      2,  'Skoda Octavia',       4, 2.00, 0.95, 0.12, 'Populārākā'),
('uni', 'Universālis',   11, 'Skoda Superb',        4, 2.50, 1.05, 0.15, ''),
('bus', 'Mikroautobuss', 9,  'Volkswagen Caravelle', 8, 4.00, 1.35, 0.20, 'Grupām');


CREATE TABLE `soferi` (
  `sofers_id` int NOT NULL AUTO_INCREMENT,
  `vards` varchar(50) NOT NULL,
  `uzvards` varchar(50) NOT NULL,
  `talrunis` varchar(8) NOT NULL,
  `licences_nr` varchar(20) NOT NULL,
  `auto_id` int NULL,
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`sofers_id`),
  UNIQUE KEY `uq_soferi_licence` (`licences_nr`),
  CONSTRAINT `fk_soferi_auto` FOREIGN KEY (`auto_id`) REFERENCES `autoparks` (`auto_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `soferi` (`vards`, `uzvards`, `talrunis`, `licences_nr`, `auto_id`) VALUES
('Jānis',    'Bērziņš',  '20000001', 'LV1234567', 1),
('Andris',   'Ozols',    '20000002', 'LV2345678', 2),
('Mārtiņš',  'Kalniņš',  '20000003', 'LV3456789', 3),
('Edgars',   'Liepa',    '20000004', 'LV4567890', NULL),
('Kristaps', 'Krūmiņš',  '20000005', 'LV5678901', 4),
('Roberts',  'Vītols',   '20000006', 'LV6789012', 5),
('Aivars',   'Zariņš',   '20000007', 'LV7890123', 6),
('Normunds', 'Jansons',  '20000008', 'LV8901234', 7),
('Ilgvars',  'Ozoliņš',  '20000009', 'LV9012345', 8),
('Dainis',   'Siliņš',   '20000010', 'LV0123456', 9),
('Gatis',    'Balodis',  '20000011', 'LV1122334', 10),
('Raivis',   'Eglītis',  '20000012', 'LV2233445', 11),
('Uldis',    'Pētersons','20000013', 'LV3344556', 12),
('Valdis',   'Kļaviņš',  '20000014', 'LV4455667', 13),
('Artūrs',   'Mednis',   '20000015', 'LV5566778', 14);



CREATE TABLE `klienti` (
  `klients_id` int NOT NULL AUTO_INCREMENT,
  `vards` varchar(100) NOT NULL,
  `talrunis` varchar(8) NOT NULL,
  `epasts` varchar(100) NULL,
  `izveidots` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statuss` enum('aktīvs','dzēsts') NOT NULL DEFAULT 'aktīvs',
  PRIMARY KEY (`klients_id`),
  UNIQUE KEY `uq_klienti_talrunis` (`talrunis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `klienti` (`vards`, `talrunis`, `epasts`, `izveidots`) VALUES
('Anna Siliņa',      '25000001', 'anna@example.com',   '2026-09-01 10:12:00'),
('Pēteris Krūmiņš',  '25000002', NULL,                 '2026-09-02 14:30:00'),
('Līga Vītola',      '25000003', 'liga@example.com',   '2026-09-03 09:05:00'),
('Jānis Ozoliņš',    '25000004', 'janis.o@example.com','2026-09-04 18:40:00'),
('Ieva Kalniņa',     '25000005', 'ieva.k@example.com', '2026-09-05 11:20:00'),
('Mārcis Zariņš',    '25000006', NULL,                 '2026-09-06 20:15:00'),
('Laura Bērziņa',    '25000007', 'laura.b@example.com','2026-09-07 08:45:00'),
('Andrejs Petrovs',  '25000008', NULL,                 '2026-09-08 16:00:00'),
('Dace Liepiņa',     '25000009', 'dace.l@example.com', '2026-09-09 12:10:00'),
('Roberts Eglītis',  '25000010', NULL,                 '2026-09-10 19:25:00'),
('Sandra Jansone',   '25000011', 'sandra.j@example.com','2026-09-11 07:55:00'),
('Oskars Balodis',   '25000012', NULL,                 '2026-09-12 15:35:00'),
('Zane Krastiņa',    '25000013', 'zane.k@example.com', '2026-09-13 13:00:00'),
('Viktors Ivanovs',  '25000014', NULL,                 '2026-09-14 21:10:00'),
('Kristīne Rozīte',  '25000015', 'kristine.r@example.com','2026-09-15 10:50:00');


CREATE TABLE `braucieni` (
  `brauciens_id` int NOT NULL AUTO_INCREMENT,
  `klients_id` int NOT NULL,
  `sofers_id` int NULL,
  `tarifs_id` int NOT NULL,
  `no_adrese` varchar(150) NOT NULL,
  `uz_adrese` varchar(150) NOT NULL,
  `km` decimal(6,2) NULL,
  `minutes` smallint unsigned NULL,
  `cena` decimal(8,2) NULL,
  `izveidots` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statuss` enum('jauns','procesā','pabeigts','atcelts','dzēsts') NOT NULL DEFAULT 'jauns',
  PRIMARY KEY (`brauciens_id`),
  CONSTRAINT `fk_braucieni_klients` FOREIGN KEY (`klients_id`) REFERENCES `klienti` (`klients_id`),
  CONSTRAINT `fk_braucieni_sofers` FOREIGN KEY (`sofers_id`) REFERENCES `soferi` (`sofers_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_braucieni_tarifs` FOREIGN KEY (`tarifs_id`) REFERENCES `tarifi` (`tarifs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `braucieni` (`klients_id`, `sofers_id`, `tarifs_id`, `no_adrese`, `uz_adrese`, `km`, `minutes`, `cena`, `izveidots`, `statuss`)
SELECT d.klients_id, d.sofers_id, d.tarifs_id, d.no_adrese, d.uz_adrese, d.km, d.minutes,
       ROUND(t.iekrausanas_maksa + d.km * t.cena_km + d.minutes * t.cena_min, 2),
       d.izveidots, d.statuss
FROM (
    SELECT 1 AS n, 1 AS klients_id, 1 AS sofers_id, 1 AS tarifs_id, 'Graudu iela 10' AS no_adrese, 'Liepājas dzelzceļa stacija' AS uz_adrese, 4.50 AS km, 12 AS minutes, '2026-09-18 08:15:00' AS izveidots, 'pabeigts' AS statuss
    UNION ALL SELECT 2,  2,  2,    2, 'Kūrmājas prospekts 5',        'Karostas cietums',            7.80, 18, '2026-09-18 09:40:00', 'pabeigts'
    UNION ALL SELECT 3,  3,  3,    2, 'Rožu laukums 1',              'Liepājas koncertzāle',        2.10,  7, '2026-09-18 12:05:00', 'pabeigts'
    UNION ALL SELECT 4,  4,  5,    1, 'Lielā iela 14',               'T/C Kurzeme',                 3.40, 10, '2026-09-19 07:50:00', 'pabeigts'
    UNION ALL SELECT 5,  5,  6,    3, 'Liepājas autoosta',           'Liepājas lidosta',            9.60, 16, '2026-09-19 10:30:00', 'pabeigts'
    UNION ALL SELECT 6,  6,  7,    2, 'Liepājas Olimpiskais centrs', 'Jūrmalas parks',              5.20, 14, '2026-09-19 23:10:00', 'pabeigts'
    UNION ALL SELECT 7,  7,  8,    4, 'Liepājas dzelzceļa stacija',  'Pāvilosta, Dzintaru iela 3', 48.00, 55, '2026-09-20 14:20:00', 'pabeigts'
    UNION ALL SELECT 8,  8,  9,    3, 'Jūras iela 24',               'Liepājas koncertzāle',        1.80,  6, '2026-09-20 18:45:00', 'pabeigts'
    UNION ALL SELECT 9,  9,  10,   1, 'Ganību iela 9',               'Liepājas Universitāte',       3.90, 11, '2026-09-21 08:05:00', 'pabeigts'
    UNION ALL SELECT 10, 10, 11,   2, 'Tirgoņu iela 3',              'Liepājas Reģionālā slimnīca', 4.70, 13, '2026-09-21 11:25:00', 'pabeigts'
    UNION ALL SELECT 11, 11, 12,   4, 'Ostas iela 7',                'Liepājas autoosta',           2.90,  9, '2026-09-22 09:15:00', 'pabeigts'
    UNION ALL SELECT 12, 12, 13,   3, 'Liepājas autoosta',           'Cimdenieku iela 12',          NULL, NULL, '2026-09-22 16:40:00', 'procesā'
    UNION ALL SELECT 13, 13, 14,   2, 'T/C Kurzeme',                 'Kūrmājas prospekts 18',       NULL, NULL, '2026-09-23 17:10:00', 'procesā'
    UNION ALL SELECT 14, 14, NULL, 1, 'Lielā iela 20',               'Liepājas dzelzceļa stacija',  NULL, NULL, '2026-09-24 07:30:00', 'jauns'
    UNION ALL SELECT 15, 15, NULL, 3, 'Rožu laukums 2',              'Liepājas lidosta',            NULL, NULL, '2026-09-24 08:00:00', 'jauns'
    UNION ALL SELECT 16, 1,  NULL, 2, 'Graudu iela 10',              'Jūras iela 24',               NULL, NULL, '2026-09-24 13:00:00', 'atcelts'
) AS d
JOIN `tarifi` t ON t.tarifs_id = d.tarifs_id
ORDER BY d.n;