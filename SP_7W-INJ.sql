-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 01, 2026 at 04:46 PM
-- Server version: 10.11.10-MariaDB-cll-lve
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ycahzble_vipm`
--
CREATE DATABASE IF NOT EXISTS `ycahzble_vipm` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `ycahzble_vipm`;

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id_history` int(11) NOT NULL,
  `keys_id` varchar(33) DEFAULT NULL,
  `user_do` varchar(33) DEFAULT NULL,
  `info` mediumtext NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`id_history`, `keys_id`, `user_do`, `info`, `created_at`, `updated_at`) VALUES
(1, '1', 'KRASH', 'PUBG|MQqXK|1|1', '2026-01-01 04:42:41', '2026-01-01 04:42:41'),
(2, '2', 'Fury', 'PUBG|xNwRI|1|1', '2026-01-02 02:16:59', '2026-01-02 02:16:59'),
(3, '3', 'KRASKVIP', 'PUBG|dumFQ|30|1', '2026-01-02 03:01:55', '2026-01-02 03:01:55'),
(4, '4', 'Fury', 'PUBG|59IRt|1|200', '2026-01-02 03:04:14', '2026-01-02 03:04:14');

-- --------------------------------------------------------

--
-- Table structure for table `keys_code`
--

CREATE TABLE `keys_code` (
  `id_keys` int(11) NOT NULL,
  `game` varchar(32) NOT NULL,
  `user_key` varchar(32) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `expired_date` datetime DEFAULT NULL,
  `max_devices` int(11) DEFAULT NULL,
  `devices` mediumtext DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `registrator` varchar(32) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `keys_code`
--

INSERT INTO `keys_code` (`id_keys`, `game`, `user_key`, `duration`, `expired_date`, `max_devices`, `devices`, `status`, `registrator`, `created_at`, `updated_at`) VALUES
(1, 'PUBG', 'MQqXKYo', 1, '2026-01-02 04:43:12', 1, '0f396417-d3a3-3d97-a74f-9297c81fdc1b', 1, 'KRASH', '2026-01-01 04:42:41', '2026-01-01 04:43:12'),
(2, 'PUBG', 'xNwRID1', 1, '2026-01-03 02:17:19', 1, '7902e747-3891-358e-9f85-b86a413ab6e4', 1, 'Fury', '2026-01-02 02:16:59', '2026-01-02 02:17:19'),
(3, 'PUBG', 'dumFQew', 30, NULL, 1, NULL, 1, 'KRASKVIP', '2026-01-02 03:01:55', '2026-01-02 03:01:55'),
(4, 'PUBG', 'FxFURYhex', 1, '2026-01-03 03:06:46', 200, '1e0c50d7-7cd1-339f-8314-fff55e56c6e0,22bfbee7-bbf0-3f6a-a4b9-6bec414ae516,491274ca-ce1c-3ee5-9693-3462f2fac920,b165dd39-cf12-3058-a68a-fbc751b8e9e0,b6d7e2b3-81cc-3808-8b53-8a162b6d0301,3beb1cb6-38cf-38eb-b82c-71641fc81edf,440b8c88-21e3-3d11-866d-8e21a116fd0e,fb014cb8-22c6-3a08-899d-b7864b027406,07b1bd27-262d-3dc1-97ae-adeb66ee2d3f,62ee28df-4146-32db-9f07-1dfd10de11bc,74712dc8-6312-3584-9f2f-ec62834ddb16,db205662-dbac-37eb-a9a6-c23d4ee7f377,2bdde31f-2a3d-321c-b1c4-1b5caf28cc50,b333514d-2172-33cb-bc7b-1898fe82cef9,1a3d47e2-1247-3180-9026-2d7d372ce14a,779cbe37-498b-3abd-ad75-ee82750e4bad,25d81c17-2b9f-3c95-ad22-7e026f3b6a2e,9389531e-eecb-3b53-9239-31494e3e53ad,6910ebd3-5b9a-3567-b019-d6ab9d91267d,ceff3104-3624-3539-9d3b-d8a54d3b992d,73d44d5f-c158-3af7-b97e-f3d6112bca9d,4b7dc27e-8bdd-3b80-a6a6-0258fb1923bb,e5ddeca2-1c4d-3c5c-b7c8-25b82e323654,9130cde7-1ead-36c8-b949-60fa65331529,3e448fdd-ceea-3570-8fd2-1d87dfcfcf93', 1, 'Fury', '2026-01-02 03:04:14', '2026-01-02 04:28:22');

-- --------------------------------------------------------

--
-- Table structure for table `modname`
--

CREATE TABLE `modname` (
  `id` int(11) NOT NULL,
  `modname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `modname`
--

INSERT INTO `modname` (`id`, `modname`) VALUES
(1, 'NOCASHRANDI');

-- --------------------------------------------------------

--
-- Table structure for table `onoff`
--

CREATE TABLE `onoff` (
  `id` int(11) NOT NULL,
  `status` varchar(5) NOT NULL,
  `myinput` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `onoff`
--

INSERT INTO `onoff` (`id`, `status`, `myinput`) VALUES
(11, 'on', 'NOCASHRANDI');

-- --------------------------------------------------------

--
-- Table structure for table `referral_code`
--

CREATE TABLE `referral_code` (
  `id_reff` int(11) NOT NULL,
  `code` varchar(128) DEFAULT NULL,
  `set_saldo` int(11) DEFAULT NULL,
  `used_by` varchar(66) DEFAULT NULL,
  `created_by` varchar(66) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `referral_code`
--

INSERT INTO `referral_code` (`id_reff`, `code`, `set_saldo`, `used_by`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'b371ce56fb5a35de319718db0c53ec80', 576787548, NULL, 'KRASH', '2026-01-02 01:05:51', '2026-01-02 01:05:51'),
(2, 'e6ab163e4ca7223f5386fd67228ed4c8', 2147483647, NULL, 'KRASH', '2026-01-02 03:00:38', '2026-01-02 03:00:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_users` int(11) NOT NULL,
  `fullname` varchar(155) DEFAULT NULL,
  `username` varchar(66) NOT NULL,
  `level` int(11) DEFAULT 2,
  `saldo` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `uplink` varchar(66) DEFAULT NULL,
  `password` varchar(155) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_users`, `fullname`, `username`, `level`, `saldo`, `status`, `uplink`, `password`, `created_at`, `updated_at`) VALUES
(1, 'KRASH', 'KRASH', 1, 2147447645, 1, 'KRASH', '$2y$08$5VpggCoA8Erlb0D.KYZJTuQEyDMMhh7hFa4JwI0ae65CRUQr4tSFa', '2021-07-28 17:43:57', '2026-01-01 04:42:41'),
(3, '', 'Fury', 1, 576787546, 1, 'KRASH', '$2y$08$gojzIMI5Y5l3I8mCW0NEzOcf.Nxm7pVVycueDJ4k8nqBNdkPnuvZy', '2026-01-02 01:07:05', '2026-01-02 03:04:14'),
(4, NULL, 'KRASKVIP', 2, 2147483646, 1, 'KRASH', '$2y$08$K1q17exH/vR/7KP8G5mlH.v4OLf.QHuBv4kt4EmyNb5EcNZvOVER.', '2026-01-02 03:01:30', '2026-01-02 03:01:55');

-- --------------------------------------------------------

--
-- Table structure for table `_ftext`
--

CREATE TABLE `_ftext` (
  `id` int(11) NOT NULL,
  `_status` varchar(100) NOT NULL,
  `_ftext` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `_ftext`
--

INSERT INTO `_ftext` (`id`, `_status`, `_ftext`) VALUES
(1, 'Safe', 'NOCASHRANDI');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`id_history`);

--
-- Indexes for table `keys_code`
--
ALTER TABLE `keys_code`
  ADD PRIMARY KEY (`id_keys`),
  ADD UNIQUE KEY `user_key` (`user_key`);

--
-- Indexes for table `modname`
--
ALTER TABLE `modname`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `onoff`
--
ALTER TABLE `onoff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_code`
--
ALTER TABLE `referral_code`
  ADD PRIMARY KEY (`id_reff`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `_ftext`
--
ALTER TABLE `_ftext`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `id_history` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `keys_code`
--
ALTER TABLE `keys_code`
  MODIFY `id_keys` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `modname`
--
ALTER TABLE `modname`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `onoff`
--
ALTER TABLE `onoff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `referral_code`
--
ALTER TABLE `referral_code`
  MODIFY `id_reff` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_users` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `_ftext`
--
ALTER TABLE `_ftext`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
