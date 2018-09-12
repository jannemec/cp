-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 10, 2018 at 11:24 AM
-- Server version: 5.7.22-log
-- PHP Version: 7.1.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `otk`
--

-- --------------------------------------------------------

--
-- Table structure for table `sys_group`
--

CREATE TABLE `sys_group` (
  `id` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `rgdt` datetime NOT NULL,
  `lmdt` datetime NOT NULL,
  `lmchid` varchar(32) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Zavedení skupiny uživatelů';

--
-- Dumping data for table `sys_group`
--

INSERT INTO `sys_group` (`id`, `name`, `description`, `rgdt`, `lmdt`, `lmchid`) VALUES
(2, 'admin', 'administrace systému', '2010-01-21 09:51:45', '2012-11-19 12:19:37', 'nemec'),
(3, 'guest', 'Běžný uživatel', '2016-10-17 00:00:00', '2016-10-17 00:00:00', 'nemec');

-- --------------------------------------------------------

--
-- Table structure for table `sys_group_right`
--

CREATE TABLE `sys_group_right` (
  `id` int(11) NOT NULL,
  `sys_right_id` int(11) NOT NULL,
  `sys_group_id` int(11) NOT NULL,
  `value` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `rgdt` datetime NOT NULL,
  `lmdt` datetime NOT NULL,
  `lmchid` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Oprávnění skupin';

--
-- Dumping data for table `sys_group_right`
--

INSERT INTO `sys_group_right` (`id`, `sys_right_id`, `sys_group_id`, `value`, `rgdt`, `lmdt`, `lmchid`, `description`) VALUES
(7, 2, 2, '', '2010-09-21 20:31:17', '2010-09-21 20:31:17', 'system', '');

-- --------------------------------------------------------

--
-- Table structure for table `sys_param`
--

CREATE TABLE `sys_param` (
  `id` int(11) NOT NULL,
  `type` varchar(16) COLLATE utf8_czech_ci NOT NULL,
  `code` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `value` text COLLATE utf8_czech_ci NOT NULL,
  `lmdt` datetime NOT NULL,
  `lmchid` varchar(16) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Dumping data for table `sys_param`
--

/*INSERT INTO `sys_param` (`id`, `type`, `code`, `name`, `value`, `lmdt`, `lmchid`) VALUES
*/

-- --------------------------------------------------------

--
-- Table structure for table `sys_paramdoc`
--

CREATE TABLE `sys_paramdoc` (
  `id` int(11) NOT NULL,
  `type` varchar(16) COLLATE utf8_czech_ci NOT NULL,
  `code` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `code2` varchar(16) COLLATE utf8_czech_ci DEFAULT NULL,
  `code3` varchar(16) COLLATE utf8_czech_ci DEFAULT NULL,
  `name` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `filename` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `value` longblob NOT NULL,
  `enctype` text COLLATE utf8_czech_ci NOT NULL,
  `lmdt` datetime NOT NULL,
  `lmchid` varchar(16) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

--
-- Dumping data for table `sys_paramdoc`
--
--
-- --------------------------------------------------------

--
-- Table structure for table `sys_right`
--

CREATE TABLE `sys_right` (
  `id` int(11) NOT NULL,
  `name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `rgdt` datetime NOT NULL,
  `lmdt` datetime NOT NULL,
  `lmchid` varchar(32) COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Zavedená oprávnění';

--
-- Dumping data for table `sys_right`
--

INSERT INTO `sys_right` (`id`, `name`, `description`, `rgdt`, `lmdt`, `lmchid`) VALUES
(2, 'Admin', 'Administrace systému - obecné', '2010-03-24 07:59:05', '2012-11-19 12:22:26', 'nemec'),
(4, 'Home', 'Úvodní stránka a její sekce', '2010-03-24 07:59:05', '2012-11-19 12:22:54', 'nemec'),
(7, 'Error', 'Chybová stránka', '2010-03-24 07:59:05', '2012-11-19 12:23:12', 'nemec');

-- --------------------------------------------------------

--
-- Table structure for table `sys_user`
--

CREATE TABLE `sys_user` (
  `id` int(11) NOT NULL,
  `username` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `hash` varchar(64) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL,
  `ss_contact_id` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `rgdt` datetime NOT NULL,
  `lmdt` datetime NOT NULL,
  `lmchid` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `email` varchar(40) COLLATE utf8_czech_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Seznam uživatelů';

--
-- Dumping data for table `sys_user`
--

INSERT INTO `sys_user` (`id`, `username`, `name`, `hash`, `description`, `ss_contact_id`, `status`, `rgdt`, `lmdt`, `lmchid`, `email`) VALUES
(6, 'nemec', 'Jan Němec', 'a39401275d1b300aa789fb22aea4148a', 'Administrátor systému', NULL, 0, '1970-01-01 01:00:00', '2010-10-12 20:02:40', 'jan', NULL)
;

-- --------------------------------------------------------

--
-- Table structure for table `sys_user_data`
--

CREATE TABLE `sys_user_data` (
  `id` int(11) NOT NULL,
  `sys_user_id` int(11) NOT NULL,
  `name` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `value` text COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='proměnné uživatele';

--
-- Dumping data for table `sys_user_data`
--

/*INSERT INTO `sys_user_data` (`id`, `sys_user_id`, `name`, `value`) VALUES
(1, 12, 'agreement_name2', 'IT');*/

-- --------------------------------------------------------

--
-- Table structure for table `sys_user_group`
--

CREATE TABLE `sys_user_group` (
  `id` int(11) NOT NULL,
  `sys_group_id` int(11) NOT NULL,
  `sys_user_id` int(11) NOT NULL,
  `value` varchar(16) COLLATE utf8_czech_ci NOT NULL,
  `rgdt` datetime NOT NULL,
  `lmdt` datetime NOT NULL,
  `lmchid` varchar(32) COLLATE utf8_czech_ci NOT NULL,
  `description` text COLLATE utf8_czech_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Role uživatelů';

--
-- Dumping data for table `sys_user_group`
--

INSERT INTO `sys_user_group` (`id`, `sys_group_id`, `sys_user_id`, `value`, `rgdt`, `lmdt`, `lmchid`, `description`) VALUES
(3, 2, 6, 'jan admin link', '1970-01-01 01:00:00', '1970-01-01 01:00:00', '', 'standardní nastavení admina jan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sys_group`
--
ALTER TABLE `sys_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `sys_group_right`
--
ALTER TABLE `sys_group_right`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ss_right_id` (`sys_right_id`),
  ADD KEY `ss_group_id` (`sys_group_id`);

--
-- Indexes for table `sys_param`
--
ALTER TABLE `sys_param`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receiverow_2` (`type`,`code`,`name`);

--
-- Indexes for table `sys_paramdoc`
--
ALTER TABLE `sys_paramdoc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type` (`type`,`code`,`name`),
  ADD UNIQUE KEY `type_2` (`type`,`code`,`code2`,`name`);

--
-- Indexes for table `sys_right`
--
ALTER TABLE `sys_right`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `sys_user`
--
ALTER TABLE `sys_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userName` (`username`),
  ADD KEY `sys_ss_contact_id` (`ss_contact_id`);

--
-- Indexes for table `sys_user_data`
--
ALTER TABLE `sys_user_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`sys_user_id`,`name`);

--
-- Indexes for table `sys_user_group`
--
ALTER TABLE `sys_user_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ss_group_id` (`sys_group_id`),
  ADD KEY `ss_user_id` (`sys_user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sys_group`
--
ALTER TABLE `sys_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sys_group_right`
--
ALTER TABLE `sys_group_right`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sys_param`
--
ALTER TABLE `sys_param`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4437;

--
-- AUTO_INCREMENT for table `sys_paramdoc`
--
ALTER TABLE `sys_paramdoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `sys_right`
--
ALTER TABLE `sys_right`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `sys_user`
--
ALTER TABLE `sys_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `sys_user_data`
--
ALTER TABLE `sys_user_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sys_user_group`
--
ALTER TABLE `sys_user_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sys_group_right`
--
ALTER TABLE `sys_group_right`
  ADD CONSTRAINT `sys_group_right_ibfk_1` FOREIGN KEY (`sys_right_id`) REFERENCES `sys_right` (`id`),
  ADD CONSTRAINT `sys_group_right_ibfk_2` FOREIGN KEY (`sys_group_id`) REFERENCES `sys_group` (`id`);

--
-- Constraints for table `sys_user_data`
--
ALTER TABLE `sys_user_data`
  ADD CONSTRAINT `sys_user_data_ibfk_1` FOREIGN KEY (`sys_user_id`) REFERENCES `sys_user` (`id`);

--
-- Constraints for table `sys_user_group`
--
ALTER TABLE `sys_user_group`
  ADD CONSTRAINT `sys_user_group_ibfk_1` FOREIGN KEY (`sys_group_id`) REFERENCES `sys_group` (`id`),
  ADD CONSTRAINT `sys_user_group_ibfk_2` FOREIGN KEY (`sys_user_id`) REFERENCES `sys_user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
