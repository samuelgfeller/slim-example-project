create database `slim-api-example`;

use slim-api-example;

CREATE TABLE `user` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(200) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`email` VARCHAR(254) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`password_hash` VARCHAR(300) NOT NULL DEFAULT '' COLLATE 'utf8_general_ci',
	`role` VARCHAR(50) NOT NULL DEFAULT 'user' COLLATE 'utf8_general_ci',
	`updated_at` DATETIME NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
	`created_at` DATETIME NULL DEFAULT current_timestamp(),
	`deleted_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;

CREATE TABLE `post` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `message` VARCHAR(500) NULL DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `deleted_at` DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `FK__user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
