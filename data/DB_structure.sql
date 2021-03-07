-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 19, 2021 at 05:12 PM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hyphenator`
--

-- --------------------------------------------------------

--
-- Table structure for table `hyphenation_pattern`
--

CREATE TABLE IF NOT EXISTS `hyphenation_pattern` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pattern` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patternNoDot` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patternNoNumbers` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patternText` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patternType` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pattern` (`pattern`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `word`
--

CREATE TABLE IF NOT EXISTS `word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `input` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `input` (`input`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `word_to_pattern`
--

CREATE TABLE IF NOT EXISTS `word_to_pattern` (
  `word_id` int(11) NOT NULL,
  `pattern_id` int(11) NOT NULL,
  `position` tinyint(4) NOT NULL,
  PRIMARY KEY (`word_id`,`pattern_id`,`position`) USING BTREE,
  KEY `word_id` (`word_id`),
  KEY `pattern_id` (`pattern_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `word_to_pattern`
--
ALTER TABLE `word_to_pattern`
  ADD CONSTRAINT `fk_wtp_to_pattern` FOREIGN KEY (`pattern_id`) REFERENCES `hyphenation_pattern` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_wtp_to_word` FOREIGN KEY (`word_id`) REFERENCES `word` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
