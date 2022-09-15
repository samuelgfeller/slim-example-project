CREATE TABLE `user`
(
    `id`            INT(11) UNSIGNED                                    NOT NULL AUTO_INCREMENT,
    `first_name`    VARCHAR(100)                                        NOT NULL COLLATE utf8mb4_unicode_ci,
    `surname`       VARCHAR(100)                                        NOT NULL COLLATE utf8mb4_unicode_ci,
    `email`         VARCHAR(254)                                        NOT NULL COLLATE utf8mb4_unicode_ci,
    `password_hash` VARCHAR(300)                                        NOT NULL COLLATE utf8mb4_unicode_ci,
    `role`          VARCHAR(50)                                         NOT NULL DEFAULT 'user' COLLATE utf8mb4_unicode_ci,
    `status`        ENUM ('active','locked', 'unverified', 'suspended') NULL     DEFAULT 'unverified' COLLATE utf8mb4_unicode_ci,
    `updated_at`    DATETIME                                            NULL     DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at`    DATETIME                                            NOT NULL DEFAULT current_timestamp(),
    `deleted_at`    DATETIME                                            NULL     DEFAULT NULL,
    PRIMARY KEY (`id`)
) COLLATE = utf8mb4_unicode_ci
  ENGINE = InnoDB
;

-- User token verification used for email validation at registration AND for password reset
CREATE TABLE `user_verification`
(
    `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(11) UNSIGNED NOT NULL,
    `token`      VARCHAR(300)     NOT NULL COLLATE utf8mb4_unicode_ci,
    `expires_at` BIGINT(20)       NOT NULL,
    `used_at`    DATETIME         NULL DEFAULT NULL,
    `created_at` DATETIME         NULL DEFAULT current_timestamp(),
    `deleted_at` DATETIME         NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `FK__user_issuer` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) COLLATE = utf8mb4_unicode_ci
  ENGINE = InnoDB
;

-- Requests that should be limited like failed logins, password recovery, registration etc. for security
CREATE TABLE `request_track`
(
    `id`         INT(11) UNSIGNED           NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(254)               NOT NULL COLLATE utf8mb4_unicode_ci, -- cannot use user_id as it's not known for failed logins
    `ip_address` INT(11) UNSIGNED           NULL     DEFAULT NULL,
    `sent_email` TINYINT UNSIGNED           NOT NULL DEFAULT 0,
    `is_login`   ENUM ('success','failure') NULL     DEFAULT NULL COLLATE utf8mb4_unicode_ci,
    `created_at` DATETIME                   NULL     DEFAULT current_timestamp(),
    INDEX `request_track_idx_created_at` (`created_at`),
    INDEX `request_track_idx_is_login` (`is_login`),
    PRIMARY KEY (`id`)
) COLLATE = utf8mb4_unicode_ci
  ENGINE = InnoDB
;

CREATE TABLE `client` (
                          `id` INT(11) NOT NULL AUTO_INCREMENT,
                          `first_name` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                          `last_name` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                          `birthdate` DATE NULL DEFAULT NULL,
                          `location` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                          `phone` VARCHAR(15) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                          `email` VARCHAR(254) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                          `sex` ENUM('M','F','O') NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                          `user_id` INT(11) NULL DEFAULT NULL,
                          `client_status_id` INT(11) NULL DEFAULT NULL,
                          `main_note_id` INT(11) NULL DEFAULT NULL COMMENT 'Main note',
                          `updated_at` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                          `created_at` DATETIME NOT NULL DEFAULT current_timestamp(),
                          `deleted_at` DATETIME NULL DEFAULT NULL,
                          PRIMARY KEY (`id`) USING BTREE,
                          INDEX `FK_client_user` (`user_id`) USING BTREE,
                          INDEX `FK_client_status` (`client_status_id`) USING BTREE,
                          INDEX `FK_client_note` (`main_note_id`) USING BTREE,
                          CONSTRAINT `FK_client_note` FOREIGN KEY (`main_note_id`) REFERENCES `note` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
                          CONSTRAINT `FK_client_status` FOREIGN KEY (`client_status_id`) REFERENCES `client_status` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION,
                          CONSTRAINT `FK_client_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
    COMMENT='Advisors help and consult clients'
    COLLATE='utf8mb4_unicode_ci'
    ENGINE=InnoDB
    AUTO_INCREMENT=4
;


CREATE TABLE `note`
(
    `id`         INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`    INT(11)      NOT NULL,
    `client_id`  INT(11)      NOT NULL,
    `message`    VARCHAR(500) NULL     DEFAULT NULL COLLATE 'utf8_general_ci',
    `updated_at` DATETIME     NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at` DATETIME     NOT NULL DEFAULT current_timestamp(),
    `deleted_at` DATETIME     NULL     DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `FK__user` (`user_id`) USING BTREE,
    INDEX `FK_note_client` (`client_id`) USING BTREE,
    CONSTRAINT `FK__user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `FK_note_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
    COLLATE = 'utf8_general_ci'
    ENGINE = InnoDB
    AUTO_INCREMENT = 9
;
