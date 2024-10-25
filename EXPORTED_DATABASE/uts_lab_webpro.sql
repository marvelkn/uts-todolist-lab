SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `uts_lab_webpro`;
USE `uts_lab_webpro`;

DROP TABLE IF EXISTS `task`;
DROP TABLE IF EXISTS `todo_lists`;
DROP TABLE IF EXISTS `users`;

DROP TABLESPACE IF EXISTS `task`;
DROP TABLESPACE IF EXISTS `todo_lists`;
DROP TABLESPACE IF EXISTS `users`;

CREATE TABLE `task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_id` int(11) NOT NULL,
  `task_name` varchar(255) NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_list` (`list_id`),
  CONSTRAINT `fk_list` FOREIGN KEY (`list_id`) REFERENCES `todo_lists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `todo_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL DEFAULT 'incomplete',
  PRIMARY KEY (`id`),
  KEY `fk_user` (`user_id`),
  CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `foto` VARCHAR(100),
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

ALTER TABLE users ADD COLUMN status ENUM('verified', 'unverified') NOT NULL DEFAULT 'unverified';

ALTER TABLE users ADD reset_token VARCHAR(6), ADD reset_expires_at DATETIME;

ALTER TABLE `todo_lists`
ADD COLUMN `due_date` DATETIME DEFAULT NULL,
ADD COLUMN `location` VARCHAR(255) DEFAULT NULL,
ADD COLUMN `priority` TINYINT(1) DEFAULT 0;


ALTER TABLE `todo_lists` ADD COLUMN `location` VARCHAR(255) DEFAULT NULL;

ALTER TABLE todo_lists ADD COLUMN category VARCHAR(50);

-- Add OTP verification columns to users table
ALTER TABLE users
ADD COLUMN otp VARCHAR(6) DEFAULT NULL,
ADD COLUMN otp_expires_at DATETIME DEFAULT NULL,
ADD COLUMN otp_used TINYINT(1) DEFAULT 0,
ADD COLUMN verification_type ENUM('email_verification', 'password_reset') DEFAULT NULL;

-- Add indexes for faster OTP verification lookups
ALTER TABLE users
ADD INDEX idx_otp_verification (email, otp, otp_expires_at);
