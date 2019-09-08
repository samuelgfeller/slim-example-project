create database `slim_api_skeleton`;
use slim_api_skeleton;
CREATE TABLE `user`
(
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `name`      VARCHAR(200) NOT NULL DEFAULT '',
    `email`      VARCHAR(254) NOT NULL DEFAULT '',
    `password`   VARCHAR(300) NOT NULL DEFAULT '',
    `updated_at` DATETIME     NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` DATETIME     NULL     DEFAULT CURRENT_TIMESTAMP,
    `deleted_at` DATETIME     NULL     DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    COLLATE = 'utf8_general_ci'
;
