CREATE TABLE `user` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(200) NOT NULL COLLATE utf8mb4_unicode_ci,
	`email` VARCHAR(254) NOT NULL COLLATE utf8mb4_unicode_ci,
	`password_hash` VARCHAR(300) NOT NULL COLLATE utf8mb4_unicode_ci,
	`role` VARCHAR(50) NOT NULL DEFAULT 'user' COLLATE utf8mb4_unicode_ci,
    `status` ENUM('active','locked', 'unverified', 'suspended') NULL DEFAULT 'unverified' COLLATE utf8mb4_unicode_ci,
	`updated_at` DATETIME NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`created_at` DATETIME NULL DEFAULT current_timestamp(),
	`deleted_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE=utf8mb4_unicode_ci
ENGINE=InnoDB
;

-- User token verification used for email validation at registration AND for password reset
CREATE TABLE `user_verification` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED NOT NULL,
	`token` VARCHAR(300) NOT NULL COLLATE utf8mb4_unicode_ci,
	`expires` BIGINT(20) NOT NULL,
	`created_at` DATETIME NULL DEFAULT current_timestamp(),
	`deleted_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
    CONSTRAINT `FK__user_issuer` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
)
COLLATE=utf8mb4_unicode_ci
ENGINE=InnoDB
;

-- Requests that should be limited like failed logins, password recovery, registration etc. for security
CREATE TABLE `request_track` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(254) NOT NULL COLLATE utf8mb4_unicode_ci, -- cannot use user_id as it's not known for failed logins
	`ip_address` INT(11) UNSIGNED NULL DEFAULT NULL,
    `sent_email` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL DEFAULT current_timestamp(),
     INDEX `created_at_index` (`created_at`),
     PRIMARY KEY (`id`)
	)
COLLATE=utf8mb4_unicode_ci
ENGINE=InnoDB
;

CREATE TABLE `post` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) UNSIGNED NOT NULL,
    `message` VARCHAR(500) NULL DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `deleted_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `FK__user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
)
COLLATE=utf8mb4_unicode_ci
ENGINE=InnoDB
;