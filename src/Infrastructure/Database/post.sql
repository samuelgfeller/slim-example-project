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
