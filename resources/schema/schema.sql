create table user_role
(
    id        int UNSIGNED auto_increment primary key,
    name      varchar(30) not null,
    hierarchy int         null
)
    charset = utf8mb4;

create table `user`
(
    id            int UNSIGNED auto_increment primary key,
    first_name    varchar(100)                                                                     not null,
    surname       varchar(100)                                                                     not null,
    email         varchar(254)                                                                     not null,
    password_hash varchar(300)                                                                     not null,
    user_role_id  int unsigned                                                  default 0                   not null,
    `status`      enum ('active', 'locked', 'unverified', 'suspended') default 'unverified'        null,
    updated_at    datetime                                             default current_timestamp() null on update current_timestamp(),
    created_at    datetime                                             default current_timestamp() null,
    deleted_at    datetime                                                                         null,
    constraint FK_user_user_role
        foreign key (user_role_id) references user_role (id)
);


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

CREATE TABLE `client_status`
(
    `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(50)      NOT NULL DEFAULT '0' COLLATE 'utf8mb4_unicode_ci',
    `deleted_at` DATETIME         NULL     DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    COMMENT ='Client status'
    COLLATE = 'utf8mb4_unicode_ci'
    ENGINE = InnoDB
;


CREATE TABLE `client`
(
    `id`               INT(11) UNSIGNED   NOT NULL AUTO_INCREMENT,
    `first_name`       VARCHAR(100)       NULL     DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    `last_name`        VARCHAR(100)       NULL     DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    `birthdate`        DATE               NULL     DEFAULT NULL,
    `location`         VARCHAR(100)       NULL     DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    `phone`            VARCHAR(20)        NULL     DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    `email`            VARCHAR(254)       NULL     DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    `sex`              ENUM ('M','F','O') NULL     DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
    `client_message`   VARCHAR(1000)      NULL     DEFAULT NULL COMMENT 'Message that client submitted via webform' COLLATE 'utf8mb4_unicode_ci',
    `user_id`          INT(11) unsigned   NULL     DEFAULT NULL,
    `client_status_id` INT(11) unsigned   NULL     DEFAULT NULL,
    `updated_at`       DATETIME           NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at`       DATETIME           NOT NULL DEFAULT current_timestamp(),
    `deleted_at`       DATETIME           NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `FK_client_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
    CONSTRAINT `FK_client_status` FOREIGN KEY (`client_status_id`) REFERENCES `client_status` (`id`)
)
    COMMENT ='Advisors help and consult clients'
    COLLATE = 'utf8mb4_unicode_ci'
    ENGINE = InnoDB
;


CREATE TABLE `note`
(
    `id`         INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT(11) UNSIGNED NOT NULL,
    `client_id`  INT(11) UNSIGNED NOT NULL,
    `message`    VARCHAR(500)     NULL     DEFAULT NULL COLLATE 'utf8_general_ci',
    `is_main`    TINYINT(1)       NOT NULL DEFAULT '0' COMMENT 'Bool if it\'s the client\'s main note',
    `updated_at` DATETIME         NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `created_at` DATETIME         NOT NULL DEFAULT current_timestamp(),
    `deleted_at` DATETIME         NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `FK__user` (`user_id`),
    INDEX `FK_note_client` (`client_id`),
    CONSTRAINT `FK__user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT `FK_note_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`) ON UPDATE NO ACTION ON DELETE NO ACTION
)
    COLLATE = 'utf8mb4_unicode_ci'
    ENGINE = InnoDB
;
