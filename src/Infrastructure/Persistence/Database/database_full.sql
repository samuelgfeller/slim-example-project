-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.1.37-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win32
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Dumping database structure for slim-api-example
CREATE DATABASE IF NOT EXISTS `slim-api-example` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `slim-api-example`;

-- Dumping structure for table slim-api-example.user
CREATE TABLE IF NOT EXISTS `user` (
                                      `id` int(11) NOT NULL AUTO_INCREMENT,
                                      `name` varchar(200) NOT NULL DEFAULT '',
                                      `email` varchar(254) NOT NULL DEFAULT '',
                                      `password` varchar(300) NOT NULL DEFAULT '',
                                      `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                                      `deleted_at` datetime DEFAULT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

-- Dumping data for table slim-api-example.user: ~13 rows (approximately)
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `name`, `email`, `password`, `updated_at`, `created_at`, `deleted_at`) VALUES
(1, 'Samuel 2', 'samuelgfeller@bluewin.ch', '$2y$10$RKoLSet3bfPhl2ZwTxzG0uAU/fsLxWlLI/C6OMyOXoYi8j57A9VFa', '2019-09-16 11:17:19', '2019-07-23 03:11:13', NULL),
(2, 'Samuel ', 'samuelgfeller143@gmail.com', '$2y$10$OFqSLOfk/EMWUtwN9lA0POSvXFPOju6e2zy7ZJmdCpHsT/LXHqe9G', '2019-11-08 10:32:42', '2019-07-23 16:16:11', NULL),
(3, 'Samuel Gfeller', 'samuelgfeller144@gmail.com', '$2y$10$FsQZaKD76bHuqHja3ZQvXOxs4ZVmimqLuVZ/e.CEG3r9WdcepWrg.', '2019-09-07 14:49:38', '2019-07-23 16:22:15', NULL),
(4, 'Name 1', 'a@b.ö', '$2y$10$RKoLSet3bfPhl2ZwTxzG0uAU/fsLxWlLI/C6OMyOXoYi8j57A9VFa', '2019-09-19 08:19:33', '2019-07-23 03:11:13', '2019-09-19 08:19:33'),
(5, 'Name 2', 'samuelgfeller143@gmail.com', '$2y$10$OFqSLOfk/EMWUtwN9lA0POSvXFPOju6e2zy7ZJmdCpHsT/LXHqe9G', '2019-09-07 14:49:35', '2019-07-23 16:16:11', NULL),
(6, 'Name 3', 'samuelgfeller144@gmail.com', '$2y$10$FsQZaKD76bHuqHja3ZQvXOxs4ZVmimqLuVZ/e.CEG3r9WdcepWrg.', '2019-09-07 14:49:38', '2019-07-23 16:22:15', NULL),
(7, '1234567', 'test@test.ch', '', '2019-09-19 08:20:03', '2019-09-14 17:54:02', '2019-09-19 08:20:03'),
(8, 'THest', 'thest2@aosdlkfj.coom', '', '2019-09-19 08:22:03', '2019-09-14 18:03:33', '2019-09-19 08:22:03'),
(9, 'seba', 'sebgfe@gmail.com', '', '2019-09-14 18:11:14', '2019-09-14 18:11:14', NULL),
(10, 'jojo', 'sonem@ail.com', '', '2019-09-14 18:12:47', '2019-09-14 18:12:47', NULL),
(11, 'John Doe', 'email@mail.com', '', '2019-09-14 18:40:48', '2019-09-14 18:40:48', NULL),
(12, 'Ricky Günter', 'adkk@ass.ch', '', '2019-11-08 10:32:23', '2019-11-08 10:32:23', NULL),
(13, 'Haarscheidt ', 'test@test.ch2', '', '2019-11-08 15:00:56', '2019-11-08 15:00:56', NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
