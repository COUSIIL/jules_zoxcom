-- Schéma de la base de données pour le système de notifications
-- Moteur : InnoDB pour le support des transactions et des clés étrangères.
-- Encodage : utf8mb4 pour le support complet d'Unicode.

-- --------------------------------------------------------

--
-- Table pour les tags de notification (catégories)
--
CREATE TABLE `notification_tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `label` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table principale des notifications
--
CREATE TABLE `notifications` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT,
  `tag_id` INT NULL,
  `type` VARCHAR(32) NOT NULL DEFAULT 'info' COMMENT 'Ex: info, success, warning, error, promo, system',
  `priority` TINYINT NOT NULL DEFAULT 2 COMMENT '1=low, 2=normal, 3=high, 4=urgent',
  `channels` JSON NOT NULL COMMENT 'Ex: ["inapp","email","push"]',
  `meta` JSON NULL COMMENT 'Objet libre pour données additionnelles (route, resourceId, etc.)',
  `status` VARCHAR(32) NOT NULL DEFAULT 'draft' COMMENT 'draft, queued, sent, failed, archived',
  `visible_from` DATETIME NULL,
  `expires_at` DATETIME NULL,
  `created_by` INT NULL COMMENT 'ID de l''utilisateur ou système ayant créé la notif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tag_id`) REFERENCES `notification_tags`(id) ON DELETE SET NULL,
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_status` (`status`),
  INDEX `idx_type` (`type`),
  INDEX `idx_visible_from` (`visible_from`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table de liaison pour suivre le statut des notifications par utilisateur
--
CREATE TABLE `user_notifications` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `notification_id` BIGINT NOT NULL,
  `user_id` BIGINT NOT NULL,
  `delivered_at` DATETIME NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME NULL,
  `channel_info` JSON NULL COMMENT 'Stocke les métadonnées par canal (ex: email_id, push_id)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_notification_user` (`notification_id`, `user_id`),
  FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table pour stocker les abonnements Web Push des utilisateurs
--
CREATE TABLE `push_subscriptions` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT NOT NULL,
  `subscription` JSON NOT NULL,
  `label` VARCHAR(255) NULL COMMENT 'Ex: "Chrome sur Windows", "iPhone de John"',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_seen` TIMESTAMP NULL,
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table optionnelle pour l'envoi de notifications à des groupes/rôles
--
CREATE TABLE `notification_targets` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `notification_id` BIGINT NOT NULL,
    `target_type` VARCHAR(50) NOT NULL COMMENT 'Ex: "role", "group", "segment"',
    `target_value` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
    INDEX `idx_target` (`target_type`, `target_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Exemples de tags initiaux
--
INSERT INTO `notification_tags` (`slug`, `label`) VALUES
('general', 'Général'),
('promotions', 'Promotions'),
('system-updates', 'Mises à jour système'),
('security', 'Sécurité');
