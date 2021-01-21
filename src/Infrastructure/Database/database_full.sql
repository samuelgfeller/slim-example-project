-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.11-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             11.1.0.6116
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for slim-example-project
CREATE DATABASE IF NOT EXISTS `slim-example-project` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `slim-example-project`;

-- Dumping structure for table slim-example-project.post
CREATE TABLE IF NOT EXISTS `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `message` varchar(500) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__user` (`user_id`),
  CONSTRAINT `FK__user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table slim-example-project.post: ~15 rows (approximately)
/*!40000 ALTER TABLE `post` DISABLE KEYS */;
INSERT INTO `post` (`id`, `user_id`, `message`, `updated_at`, `created_at`, `deleted_at`) VALUES
	(1, 1, 'Test post from user samuelgfeller@bluewin.ch edit', '2020-10-09 11:55:02', '2020-03-18 10:18:32', '2020-10-09 11:55:02'),
	(2, 1, 'Second post from Samuel 8', '2020-10-09 11:55:02', '2020-03-18 10:18:56', '2020-10-09 11:55:02'),
	(7, 18, 'Test post für die Präsentation', '2020-10-09 11:59:00', '2020-03-19 13:31:55', '2020-10-09 11:59:00'),
	(8, 18, '2. Post für den Test ; edit', '2020-10-09 11:59:00', '2020-03-19 13:33:15', '2020-10-09 11:59:00'),
	(9, 1, 'test post ', '2020-10-09 11:55:02', '2020-03-23 11:50:59', '2020-10-09 11:55:02'),
	(10, 1, 'asdf', '2020-10-09 11:55:02', '2020-03-25 12:32:15', '2020-10-09 11:55:02'),
	(11, 1, 'New post', '2020-10-09 11:55:02', '2020-03-25 17:15:39', '2020-10-09 11:55:02'),
	(12, 1, 'New 2', '2020-10-09 11:55:02', '2020-03-25 17:18:08', '2020-10-09 11:55:02'),
	(13, 1, 'asdf', '2020-10-09 11:55:02', '2020-03-25 17:18:31', '2020-10-09 11:55:02'),
	(14, 1, 'new 3', '2020-10-09 11:55:02', '2020-03-25 17:19:06', '2020-10-09 11:55:02'),
	(15, 1, 'asdfadsf', '2020-10-09 11:55:02', '2020-03-25 17:19:12', '2020-10-09 11:55:02'),
	(16, 1, 'asdfasdf', '2020-10-09 11:55:02', '2020-03-25 17:19:17', '2020-10-09 11:55:02'),
	(17, 1, 'new from all', '2020-10-09 11:55:02', '2020-03-25 17:20:03', '2020-10-09 11:55:02'),
	(18, 1, 'dadff', '2020-10-09 11:55:02', '2020-03-25 17:22:33', '2020-10-09 11:55:02'),
	(19, 1, 'New authservice\n', '2020-10-09 11:55:02', '2020-05-01 17:12:29', '2020-10-09 11:55:02');
/*!40000 ALTER TABLE `post` ENABLE KEYS */;

-- Dumping structure for table slim-example-project.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL DEFAULT '',
  `email` varchar(254) NOT NULL DEFAULT '',
  `password_hash` varchar(300) NOT NULL DEFAULT '',
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table slim-example-project.user: ~26 rows (approximately)
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id`, `name`, `email`, `password_hash`, `role`, `updated_at`, `created_at`, `deleted_at`) VALUES
	(1, 'Samuel', 'samuelgfeller@bluewin.ch', '$2y$10$RKoLSet3bfPhl2ZwTxzG0uAU/fsLxWlLI/C6OMyOXoYi8j57A9VFa', 'admin', '2020-03-27 12:54:42', '2019-07-23 03:11:13', NULL),
	(2, 'Samuel 143', 'samuelgfeller143@gmail.com', '$2y$10$OFqSLOfk/EMWUtwN9lA0POSvXFPOju6e2zy7ZJmdCpHsT/LXHqe9G', 'user', '2020-03-27 12:54:58', '2019-07-23 16:16:11', NULL),
	(3, 'Samuel Gfeller', 'samuelgfeller144@gmail.com', '$2y$10$FsQZaKD76bHuqHja3ZQvXOxs4ZVmimqLuVZ/e.CEG3r9WdcepWrg.', 'user', '2019-09-07 14:49:38', '2019-07-23 16:22:15', NULL),
	(4, 'Name 1', 'a@b.ö', '$2y$10$RKoLSet3bfPhl2ZwTxzG0uAU/fsLxWlLI/C6OMyOXoYi8j57A9VFa', 'user', '2019-09-19 08:19:33', '2019-07-23 03:11:13', '2019-09-19 08:19:33'),
	(5, 'Name 2', 'samuelgfeller143@gmail.com', '$2y$10$OFqSLOfk/EMWUtwN9lA0POSvXFPOju6e2zy7ZJmdCpHsT/LXHqe9G', 'user', '2019-09-07 14:49:35', '2019-07-23 16:16:11', NULL),
	(6, 'Name 3', 'samuelgfeller144@gmail.com', '$2y$10$FsQZaKD76bHuqHja3ZQvXOxs4ZVmimqLuVZ/e.CEG3r9WdcepWrg.', 'user', '2020-03-19 13:36:01', '2019-07-23 16:22:15', '2020-03-19 13:36:01'),
	(7, '1234567', 'test@test.ch', '$2y$12$hY7UjJCVgDiZrXI28FeAeuiA.5yytrOFDFqk9bmpPPADjl7d8QDR2\r\n', 'user', '2020-04-02 10:54:14', '2019-09-14 17:54:02', '2019-09-19 08:20:03'),
	(8, 'THest', 'thest2@aosdlkfj.coom', '', 'user', '2019-09-19 08:22:03', '2019-09-14 18:03:33', '2019-09-19 08:22:03'),
	(9, 'seba', 'sebgfe@gmail.com', '', 'user', '2019-09-14 18:11:14', '2019-09-14 18:11:14', NULL),
	(10, 'jojo edit', 'sonem@ail.com', '', 'user', '2020-03-18 19:52:31', '2019-09-14 18:12:47', NULL),
	(11, 'John Doe', 'email@mail.com', '', 'user', '2019-09-14 18:40:48', '2019-09-14 18:40:48', NULL),
	(12, 'Ricky Günter', 'adkk@ass.ch', '', 'user', '2019-11-08 10:32:23', '2019-11-08 10:32:23', NULL),
	(13, 'Haarscheidt ', 'test@test.ch2', '', 'user', '2019-11-08 15:00:56', '2019-11-08 15:00:56', NULL),
	(18, 'Aprentas edit 2', 'test@aprentas.ch3', '$2y$10$RKoLSet3bfPhl2ZwTxzG0uAU/fsLxWlLI/C6OMyOXoYi8j57A9VFa', 'admin', '2020-10-09 11:59:00', '2020-03-19 13:30:25', '2020-10-09 11:59:00'),
	(19, 'Rudolf', 'hu.rudolf@bluewin.ch', '123', 'user', '2020-03-25 11:54:06', '2020-03-25 11:54:06', NULL),
	(20, 'Rudolf 2', 'hu.rudolf@bluewin.ch2', '123', 'user', '2020-03-25 11:57:15', '2020-03-25 11:57:15', NULL),
	(21, 'Rudolf 3', 'hu.rudolf3@bluewin.ch', '$2y$10$NjpxIa4dpB3tshHm0UxDl.JSfNZ28yb7k8GzLtgxx8PWGG.6b6mKG', 'user', '2020-03-25 12:01:58', '2020-03-25 12:01:58', NULL),
	(22, 'Rudolf 4', 'hu.rudolf4@bluewin.ch', '$2y$10$XzlhyBK/Nf6rR9G39HVgCOcufZGP2tK4bJBymNGDPk5wGOHkWZZ72', 'user', '2020-03-25 12:02:51', '2020-03-25 12:02:51', NULL),
	(23, 'Rudolf 5', 'hu.rudolf5@bluewin.ch', '$2y$10$U.syiytq3LWu9UlqiJcVruR0s7AWjO0R2SAtbE.NQySftylGHKF1i', 'user', '2020-03-25 12:03:47', '2020-03-25 12:03:47', NULL),
	(24, 'Rudolf 6', 'hu.rudolf6@bluewin.ch', '$2y$10$zmEA/hmJvGZo17kXkz7cRuyUkxjX/0FSTXtYLX13EP.TqdlRtEAT6', 'user', '2020-03-25 12:06:33', '2020-03-25 12:06:33', NULL),
	(25, 'Rudolf 7', 'hu.rudolf7@bluewin.ch', '$2y$10$d5Ia.h6TfrCJ2PAK0UZhPeEh6y8kmv1eX/idXzVMYh3t6045ax1ae', 'user', '2020-03-25 12:08:09', '2020-03-25 12:08:09', NULL),
	(26, 'Rudolf 8', 'hu.rudolf8@bluewin.ch', '$2y$10$pNr2v68dBBg/3VRDkSNkqeDYJerdNgiN2xpTFnuAwiLWAO2qpJ8g.', 'user', '2020-03-25 12:13:48', '2020-03-25 12:13:48', NULL),
	(27, 'Rudolf 9', 'hu.rudolf9@bluewin.ch', '$2y$10$OyG6aSmcx5aK6dLsXyWehuGH8WO54s45fqANLHNudlLZhaaWXM4Py', 'user', '2020-03-25 12:14:29', '2020-03-25 12:14:29', NULL),
	(28, 'Rudi', 'testmail@gmail.com', '$2y$10$bpGRvuVfW8kyi5fJsK9W/uxPOy7MZk4ztlcdZD9O/Z1XXhjp5s0ES', 'user', '2020-04-02 10:55:12', '2020-04-02 10:55:12', NULL),
	(29, 'Rudi 2', 'testmail2@gmail.com', '$2y$10$6/uMN3j4N2p6bIV1ZWf7Eu3OWxFEYETHt4.EzoLEOPXxgng2qu2kC', 'user', '2020-04-02 10:55:51', '2020-04-02 10:55:51', NULL),
	(30, 'Rudi 3', 'admin@test.ch', '$2y$10$ZSCoDJkDCSlbl1/8fScmS.0jiCIiCV6oGjqWZloCGWzTYV6rd1WoS', 'user', '2020-04-02 10:56:49', '2020-04-02 10:56:49', NULL),
	(35, 'Samuel Biz', 'contact@samuel-gfeller.ch', '$2y$10$yoaIVZjxcrDNSSxB7JfYaOX94nM2YnxqiMkgRLAHuKuOXZnCghY/K', 'user', '2021-01-20 10:18:15', '2021-01-20 10:18:15', NULL);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
