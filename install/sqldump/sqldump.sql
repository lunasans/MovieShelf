-- ============================================================================
-- MovieShelf - Datenbank-Schema
-- Dein digitales Filmregal
-- ============================================================================
-- Version: 1.5.0
-- Erstellt: 08.02.2026
-- Beschreibung: Vollständiges Produktiv-Schema mit allen Tabellen
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS=0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================================
-- TABELLEN-STRUKTUR
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Tabelle: users (ZUERST - wird von anderen referenziert)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `twofa_secret` varchar(64) DEFAULT NULL,
  `twofa_enabled` tinyint(1) DEFAULT 0,
  `twofa_activated_at` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_twofa_enabled` (`twofa_enabled`),
  KEY `idx_active` (`is_active`),
  KEY `idx_locked` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: dvds (Filme und Serien)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `dvds` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `year` int(11) DEFAULT NULL,
  `genre` varchar(255) DEFAULT NULL,
  `cover_id` varchar(200) DEFAULT NULL,
  `collection_type` varchar(100) DEFAULT NULL,
  `runtime` int(11) DEFAULT NULL,
  `rating_age` int(11) DEFAULT NULL,
  `overview` text DEFAULT NULL,
  `trailer_url` varchar(500) DEFAULT NULL,
  `boxset_parent` bigint(20) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_title` (`title`),
  KEY `idx_year` (`year`),
  KEY `idx_genre` (`genre`),
  KEY `idx_collection_type` (`collection_type`),
  KEY `idx_user` (`user_id`),
  KEY `idx_deleted` (`is_deleted`),
  KEY `idx_boxset_parent` (`boxset_parent`),
  KEY `idx_view_count` (`view_count`),
  FULLTEXT KEY `idx_search` (`title`,`overview`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: actors (Schauspieler)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `actors` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `birth_year` int(11) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`last_name`,`first_name`),
  KEY `idx_birth_year` (`birth_year`),
  FULLTEXT KEY `idx_search` (`first_name`,`last_name`,`bio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: film_actor (Verknüpfung Filme <-> Schauspieler)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `film_actor` (
  `film_id` bigint(20) NOT NULL,
  `actor_id` bigint(20) NOT NULL,
  `role` varchar(255) DEFAULT NULL,
  `is_main_role` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`film_id`,`actor_id`),
  KEY `idx_film` (`film_id`),
  KEY `idx_actor` (`actor_id`),
  KEY `idx_main_role` (`is_main_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: settings (Einstellungen)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  UNIQUE KEY `unique_key` (`key`),
  KEY `idx_key` (`key`),
  KEY `idx_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: counter (Besucherzähler)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `counter` (
  `id` int(11) NOT NULL DEFAULT 1,
  `visits` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `last_visit_date` date DEFAULT NULL,
  `daily_visits` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_last_visit` (`last_visit_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initialen Counter-Eintrag erstellen
INSERT IGNORE INTO `counter` (`id`, `visits`, `last_visit_date`, `daily_visits`) 
VALUES (1, 0, CURDATE(), 0);

-- ----------------------------------------------------------------------------
-- Tabelle: activity_log (Aktivitäts-Protokoll)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: audit_log (Sicherheits-Audit)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: seasons (TV-Serien Staffeln)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `seasons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `series_id` int(11) NOT NULL,
  `season_number` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `overview` text DEFAULT NULL,
  `episode_count` int(11) DEFAULT 0,
  `air_date` date DEFAULT NULL,
  `poster_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_season` (`series_id`,`season_number`),
  KEY `idx_series` (`series_id`),
  KEY `idx_season_number` (`season_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: episodes (TV-Serien Episoden)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `episodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_id` int(11) NOT NULL,
  `episode_number` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `overview` text DEFAULT NULL,
  `air_date` date DEFAULT NULL,
  `runtime` int(11) DEFAULT NULL,
  `still_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_episode` (`season_id`,`episode_number`),
  KEY `idx_season` (`season_id`),
  KEY `idx_episode_number` (`episode_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: github_cache (GitHub API Cache)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `github_cache` (
  `cache_key` varchar(50) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`cache_key`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: user_backup_codes (2FA Backup-Codes)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_backup_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `code` varchar(255) NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: user_ratings (Benutzer-Bewertungen)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `film_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `rating` decimal(2,1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_film` (`user_id`,`film_id`),
  KEY `idx_film_id` (`film_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_rating` (`rating`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: user_watched ("Gesehen"-Status)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_watched` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `film_id` bigint(20) NOT NULL,
  `watched_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_film_watched` (`user_id`,`film_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_film_id` (`film_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Tabelle: user_wishlist (Wunschliste)
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `film_id` bigint(20) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_film_wish` (`user_id`,`film_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_film_id` (`film_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================================
-- FOREIGN KEY CONSTRAINTS
-- ============================================================================

ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `dvds`
  ADD CONSTRAINT `dvds_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `dvds_ibfk_2` FOREIGN KEY (`boxset_parent`) REFERENCES `dvds` (`id`) ON DELETE SET NULL;

ALTER TABLE `film_actor`
  ADD CONSTRAINT `film_actor_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `dvds` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `film_actor_ibfk_2` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_backup_codes`
  ADD CONSTRAINT `user_backup_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_ratings`
  ADD CONSTRAINT `user_ratings_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `dvds` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_ratings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_watched`
  ADD CONSTRAINT `user_watched_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_watched_ibfk_2` FOREIGN KEY (`film_id`) REFERENCES `dvds` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_wishlist`
  ADD CONSTRAINT `user_wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_wishlist_ibfk_2` FOREIGN KEY (`film_id`) REFERENCES `dvds` (`id`) ON DELETE CASCADE;

-- ============================================================================
-- FERTIG
-- ============================================================================

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================================================
-- Schema-Information
-- ============================================================================
-- Tabellen gesamt: 15
-- - users (Benutzer-Verwaltung)
-- - dvds (Filme und Serien)
-- - actors (Schauspieler)
-- - film_actor (Verknüpfung)
-- - settings (Einstellungen)
-- - counter (Besucherzähler)
-- - activity_log (Aktivitäts-Protokoll)
-- - audit_log (Sicherheits-Audit)
-- - seasons (TV-Staffeln)
-- - episodes (TV-Episoden)
-- - github_cache (API-Cache)
-- - user_backup_codes (2FA-Codes)
-- - user_ratings (Bewertungen)
-- - user_watched (Gesehen-Status)
-- - user_wishlist (Wunschliste)
-- ============================================================================
