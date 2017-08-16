-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 15, 2017 at 12:29 PM
-- Server version: 10.0.31-MariaDB-0ubuntu0.16.04.2
-- PHP Version: 7.0.22-0ubuntu0.16.04.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `itlr`
--
CREATE DATABASE IF NOT EXISTS `itlr` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `itlr`;

-- --------------------------------------------------------

--
-- Table structure for table `experiments`
--

CREATE TABLE `experiments` (
  `ID` int(10) UNSIGNED NOT NULL,
  `Name` varchar(150) NOT NULL,
  `DataType` varchar(100) NOT NULL,
  `Organism` varchar(100) NOT NULL,
  `CellType` varchar(100) NOT NULL,
  `TimePoint` int(11) NOT NULL COMMENT '(min)',
  `Experimentalist` text NOT NULL,
  `Replicate` smallint(10) UNSIGNED NOT NULL,
  `Protocol` text NOT NULL,
  `Strain` varchar(20) NOT NULL,
  `Readout` text,
  `Receptor` text,
  `Platform` text,
  `Citation` text,
  `Gender_Age_Other` varchar(50) NOT NULL,
  `GSM_Number` varchar(30) NOT NULL,
  `GSE_Number` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `experiment_gene`
--

CREATE TABLE `experiment_gene` (
  `ID` int(10) UNSIGNED NOT NULL,
  `ExperimentID` int(10) UNSIGNED NOT NULL,
  `GeneID` int(10) UNSIGNED NOT NULL,
  `Value` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `experiment_stimulation`
--

CREATE TABLE `experiment_stimulation` (
  `ID` int(10) UNSIGNED NOT NULL,
  `ExperimentID` int(11) NOT NULL,
  `StimulationID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(10) UNSIGNED NOT NULL,
  `PageSelection` varchar(20) NOT NULL,
  `Type` varchar(20) NOT NULL,
  `Subject` varchar(40) NOT NULL,
  `Body` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE `log` (
  `id` bigint(20) NOT NULL,
  `channel` varchar(255) DEFAULT NULL,
  `level` int(11) DEFAULT NULL,
  `message` longtext,
  `time` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `network`
--

CREATE TABLE `network` (
  `id` int(10) UNSIGNED NOT NULL,
  `Organism` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Gene_A` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Gene_B` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `EntrezID_A` int(11) DEFAULT NULL,
  `EntrezID_B` int(11) DEFAULT NULL,
  `Experimental_System` text COLLATE utf8_unicode_ci NOT NULL,
  `Experimental_System_Type` text COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pathway`
--

CREATE TABLE `pathway` (
  `id` int(10) UNSIGNED NOT NULL,
  `Pathway_ID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Pathway_Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `platform`
--

CREATE TABLE `platform` (
  `id` int(10) UNSIGNED NOT NULL,
  `Gene` varchar(100) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `Alias` varchar(100) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `stimulation`
--

CREATE TABLE `stimulation` (
  `ID` int(11) NOT NULL,
  `Stimulus` varchar(100) NOT NULL,
  `Concentration` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `experiments`
--
ALTER TABLE `experiments`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `experiment_gene`
--
ALTER TABLE `experiment_gene`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `GeneID` (`GeneID`),
  ADD KEY `ExperimentID` (`ExperimentID`);

--
-- Indexes for table `experiment_stimulation`
--
ALTER TABLE `experiment_stimulation`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `ExperimentID` (`ExperimentID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channel` (`channel`(191)) USING HASH,
  ADD KEY `level` (`level`) USING HASH,
  ADD KEY `time` (`time`) USING BTREE;

--
-- Indexes for table `network`
--
ALTER TABLE `network`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pathway`
--
ALTER TABLE `pathway`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `platform`
--
ALTER TABLE `platform`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Alias_Gene` (`Gene`,`Alias`),
  ADD KEY `Gene` (`Gene`),
  ADD KEY `Alias` (`Alias`);

--
-- Indexes for table `stimulation`
--
ALTER TABLE `stimulation`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `experiments`
--
ALTER TABLE `experiments`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=263;
--
-- AUTO_INCREMENT for table `experiment_gene`
--
ALTER TABLE `experiment_gene`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4702424;
--
-- AUTO_INCREMENT for table `experiment_stimulation`
--
ALTER TABLE `experiment_stimulation`
  MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;
--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `log`
--
ALTER TABLE `log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `network`
--
ALTER TABLE `network`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=255587;
--
-- AUTO_INCREMENT for table `pathway`
--
ALTER TABLE `pathway`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=523;
--
-- AUTO_INCREMENT for table `platform`
--
ALTER TABLE `platform`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94006;
--
-- AUTO_INCREMENT for table `stimulation`
--
ALTER TABLE `stimulation`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
