-- ============================================================
-- AUTH SYSTEM - Database Schema
-- Compatible with MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS `auth_system`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `auth_system`;

-- ------------------------------------------------------------
-- Table: users
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`                INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name`         VARCHAR(120)     NOT NULL,
  `email`             VARCHAR(255)     NOT NULL,
  `password_hash`     VARCHAR(255)     NOT NULL,
  `is_verified`       TINYINT(1)       NOT NULL DEFAULT 0,
  `is_active`         TINYINT(1)       NOT NULL DEFAULT 1,
  `profile_picture`   VARCHAR(255)     DEFAULT NULL,
  `role`              ENUM('user','admin') NOT NULL DEFAULT 'user',
  `last_login_at`     DATETIME         DEFAULT NULL,
  `last_login_ip`     VARCHAR(45)      DEFAULT NULL,
  `created_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: email_verifications
-- (used for account verification after registration)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11) UNSIGNED NOT NULL,
  `token`       VARCHAR(64)      NOT NULL,
  `expires_at`  DATETIME         NOT NULL,
  `used_at`     DATETIME         DEFAULT NULL,
  `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_ev_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: password_resets
-- (OTP codes sent by email for password recovery)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id`          INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT(11) UNSIGNED NOT NULL,
  `otp_code`    CHAR(6)          NOT NULL,
  `token`       VARCHAR(64)      NOT NULL,
  `expires_at`  DATETIME         NOT NULL,
  `used_at`     DATETIME         DEFAULT NULL,
  `attempts`    TINYINT(1)       NOT NULL DEFAULT 0,
  `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_pr_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: sessions
-- (server-side session tracking – optional extra layer)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id`            INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`       INT(11) UNSIGNED NOT NULL,
  `session_token` VARCHAR(128)     NOT NULL,
  `ip_address`    VARCHAR(45)      DEFAULT NULL,
  `user_agent`    VARCHAR(255)     DEFAULT NULL,
  `last_activity` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at`    DATETIME         NOT NULL,
  `created_at`    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_token` (`session_token`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_us_user` FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: login_attempts  (brute-force protection)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`           INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`        VARCHAR(255)     NOT NULL,
  `ip_address`   VARCHAR(45)      NOT NULL,
  `attempted_at` DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_ip` (`email`, `ip_address`),
  KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- END OF SCHEMA
-- ============================================================
