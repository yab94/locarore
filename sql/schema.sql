-- Locarore — Schema SQL
-- Encodage : utf8mb4

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories` (
    `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `parent_id`   INT UNSIGNED    NULL DEFAULT NULL,
    `name`        VARCHAR(100)    NOT NULL,
    `slug`        VARCHAR(120)    NOT NULL,
    `description` TEXT            NULL,
    `is_active`   TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`  DATETIME        NOT NULL,
    `updated_at`  DATETIME        NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_categories_slug` (`slug`),
    KEY `idx_categories_parent` (`parent_id`),
    CONSTRAINT `fk_categories_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- products
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
    `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `category_id`  INT UNSIGNED    NOT NULL,
    `name`         VARCHAR(150)    NOT NULL,
    `slug`         VARCHAR(170)    NOT NULL,
    `description`  TEXT            NULL,
    `stock`        INT             NOT NULL DEFAULT 0,
    `price_per_day` DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `is_active`    TINYINT(1)      NOT NULL DEFAULT 1,
    `created_at`   DATETIME        NOT NULL,
    `updated_at`   DATETIME        NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_products_slug` (`slug`),
    KEY `idx_products_category` (`category_id`),
    CONSTRAINT `fk_products_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- product_categories (pivot many-to-many)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_categories` (
    `product_id`  INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`product_id`, `category_id`),
    KEY `idx_pc_category` (`category_id`),
    CONSTRAINT `fk_pc_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pc_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- product_photos
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_photos` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `filename`   VARCHAR(255) NOT NULL,
    `sort_order` INT          NOT NULL DEFAULT 0,
    `created_at` DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_photos_product` (`product_id`),
    CONSTRAINT `fk_photos_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- reservations
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reservations` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `customer_name`    VARCHAR(150) NOT NULL,
    `customer_email`   VARCHAR(200) NOT NULL,
    `customer_phone`   VARCHAR(20)  NULL,
    `customer_address` TEXT         NULL,
    `event_address`    TEXT         NULL,
    `start_date`       DATE         NOT NULL,
    `end_date`         DATE         NOT NULL,
    `status`           ENUM('pending','quoted','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    `notes`            TEXT         NULL,
    `created_at`       DATETIME     NOT NULL,
    `updated_at`       DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_reservations_status` (`status`),
    KEY `idx_reservations_dates`  (`start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- reservation_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reservation_items` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reservation_id` INT UNSIGNED NOT NULL,
    `product_id`     INT UNSIGNED NOT NULL,
    `pack_id`        INT UNSIGNED NULL DEFAULT NULL,
    `quantity`       INT          NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_ri_reservation` (`reservation_id`),
    KEY `idx_ri_product`     (`product_id`),
    KEY `idx_ri_pack`        (`pack_id`),
    CONSTRAINT `fk_ri_reservation`
        FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ri_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_ri_pack`
        FOREIGN KEY (`pack_id`) REFERENCES `packs` (`id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- packs
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `packs` (
    `id`           INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(150)   NOT NULL,
    `slug`         VARCHAR(170)   NOT NULL,
    `description`  TEXT           NULL,
    `price_per_day` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `is_active`    TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`   DATETIME       NOT NULL,
    `updated_at`   DATETIME       NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_packs_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- pack_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pack_items` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `pack_id`    INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity`   INT          NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_pi_pack`    (`pack_id`),
    KEY `idx_pi_product` (`product_id`),
    CONSTRAINT `fk_pi_pack`
        FOREIGN KEY (`pack_id`) REFERENCES `packs` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pi_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
