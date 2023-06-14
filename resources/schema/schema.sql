CREATE TABLE `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(254) DEFAULT NULL,
  `sex` enum('M','F','O') DEFAULT NULL,
  `client_message` varchar(1000) DEFAULT NULL COMMENT 'Message that client submitted via webform',
  `vigilance_level` enum('moderate','caution','extra_caution') DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `client_status_id` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL COMMENT 'date at which user_id was set',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_client_user` (`user_id`),
  KEY `FK_client_status` (`client_status_id`),
  CONSTRAINT `client_client_status__fk` FOREIGN KEY (`client_status_id`) REFERENCES `client_status` (`id`),
  CONSTRAINT `client_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='Advisors help and consult clients';

CREATE TABLE `client_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '0',
  `deleted_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='Client status';

CREATE TABLE `note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `message` varchar(1000) DEFAULT NULL,
  `is_main` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Bool if it''s the client''s main note',
  `hidden` tinyint(4) DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__user` (`user_id`),
  KEY `FK_note_client` (`client_id`),
  CONSTRAINT `note_client_id_fk` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`),
  CONSTRAINT `note_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(200) NOT NULL,
  `module` varchar(100) NOT NULL COMMENT 'module where action takes place',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) DEFAULT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `user_role_id` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','locked','unverified','suspended') DEFAULT 'unverified',
  `email` varchar(254) NOT NULL,
  `password_hash` varchar(300) NOT NULL,
  `theme` enum('light','dark') DEFAULT 'light',
  `language` enum('en_US','de_CH','fr_CH') DEFAULT 'en_US',
  `updated_at` datetime DEFAULT current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_user_user_role` (`user_role_id`),
  CONSTRAINT `user_user_role_id_fk` FOREIGN KEY (`user_role_id`) REFERENCES `user_role` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `user_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('created','updated','deleted','read') NOT NULL,
  `table` varchar(100) DEFAULT NULL,
  `row_id` int(11) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `datetime` datetime NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_activity_user_id_fk` (`user_id`),
  CONSTRAINT `user_activity_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `user_filter_setting` (
  `user_id` int(11) NOT NULL,
  `filter_id` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `module` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`,`filter_id`,`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `user_request` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  `ip_address` int(11) unsigned DEFAULT NULL,
  `sent_email` tinyint(4) NOT NULL DEFAULT 0,
  `is_login` enum('success','failure') DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_at_index` (`created_at`),
  KEY `request_track_idx_is_login` (`is_login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `user_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `hierarchy` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `user_role_to_permission` (
  `user_role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  PRIMARY KEY (`user_role_id`,`permission_id`),
  KEY `user_role_to_permission_permission_null_fk` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

CREATE TABLE `user_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(300) NOT NULL,
  `expires_at` bigint(20) NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__user_table` (`user_id`),
  CONSTRAINT `user_verification_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;