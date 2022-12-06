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
    user_role_id  int unsigned                                         default 0                   not null,
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
CREATE TABLE `user_request`
(
    `id`         INT(11) UNSIGNED           NOT NULL AUTO_INCREMENT,
    `email`      VARCHAR(254)               NOT NULL COLLATE utf8mb4_unicode_ci, -- cannot use user_id as it's not known for failed logins
    `ip_address` INT(11) UNSIGNED           NULL     DEFAULT NULL,
    `sent_email` TINYINT UNSIGNED           NOT NULL DEFAULT 0,
    `is_login`   ENUM ('success','failure') NULL     DEFAULT NULL COLLATE utf8mb4_unicode_ci,
    `created_at` DATETIME                   NULL     DEFAULT current_timestamp(),
    INDEX `user_request_idx_created_at` (`created_at`),
    INDEX `user_request_idx_is_login` (`is_login`),
    PRIMARY KEY (`id`)
) COLLATE = utf8mb4_unicode_ci
  ENGINE = InnoDB
;

create table user_activity
(
    id         int unsigned auto_increment
        primary key,
    user_id    int unsigned                                   not null,
    action     enum ('created', 'updated', 'deleted', 'read') not null,
    `table`    varchar(100)                                   null,
    row_id     int                                            null,
    data       text                                           null,
    datetime   datetime default current_timestamp()           not null,
    ip_address varchar(50)                                    null,
    user_agent varchar(255)                                   null,
    constraint user_activity_user_null_fk
        foreign key (user_id) references user (id)
);


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


create table client
(
    id               int unsigned auto_increment
        primary key,
    first_name       varchar(100)                                  null,
    last_name        varchar(100)                                  null,
    birthdate        date                                          null,
    location         varchar(100)                                  null,
    phone            varchar(20)                                   null,
    email            varchar(254)                                  null,
    sex              enum ('M', 'F', 'O')                          null,
    client_message   varchar(1000)                                 null comment 'Message that client submitted via webform',
    vigilance_level  enum ('moderate', 'caution', 'extra_caution') null,
    user_id          int unsigned                                  null,
    client_status_id int unsigned                                  null,
    updated_at       datetime default current_timestamp()          not null on update current_timestamp(),
    created_at       datetime default current_timestamp()          not null,
    deleted_at       datetime                                      null,
    constraint FK_client_status
        foreign key (client_status_id) references client_status (id),
    constraint FK_client_user
        foreign key (user_id) references user (id)
)
    comment 'Advisors help and consult clients';



create table note
(
    id         int unsigned auto_increment primary key,
    user_id    int unsigned                           not null,
    client_id  int unsigned                           not null,
    message    varchar(1000)                          null,
    is_main    tinyint(1) default 0                   not null comment 'Bool if it''s the client''s main note',
    hidden     tinyint                                null,
    updated_at datetime   default current_timestamp() not null on update current_timestamp(),
    created_at datetime   default current_timestamp() not null,
    deleted_at datetime                               null,
    constraint FK__user
        foreign key (user_id) references user (id),
    constraint FK_note_client
        foreign key (client_id) references client (id)
)
    charset = utf8;



create table user_client_list_filter
(
    user_id   int          not null,
    filter_id varchar(100) not null,
    primary key (user_id, filter_id)
);

