# 17.12.2019
ALTER TABLE `user`
    ADD COLUMN `role` VARCHAR(50) NOT NULL DEFAULT 'user' AFTER `password`;