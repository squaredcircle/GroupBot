<?php

/*

        CREATE TABLE IF NOT EXISTS `users` (
        `user_id` INTEGER PRIMARY KEY,
        `user_name` varchar(64),
        `user_password_hash` varchar(255),
        `user_money` decimal(19,4));
        CREATE UNIQUE INDEX `user_name_UNIQUE` ON `users` (`user_name` ASC);
        GO


        CREATE TABLE IF NOT EXISTS `transactions` (
        `id` INTEGER PRIMARY KEY,
        `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `user_sending` varchar(64),
        `user_receiving` varchar(64),
        `amount` decimal(19,4));
        CREATE UNIQUE INDEX `user_sending_UNIQUE` ON `transactions` (`user_sending` ASC);

*/