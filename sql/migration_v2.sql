-- Migration v2 : parent_id catégories, multi-catégories produits,
--                adresses réservation, packs, pack_items

SET foreign_key_checks = 0;

-- 1. Catégories : ajout parent_id
ALTER TABLE `categories`
    ADD COLUMN `parent_id` INT UNSIGNED NULL DEFAULT NULL AFTER `id`,
    ADD CONSTRAINT `fk_categories_parent`
        FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL;

-- 2. Pivot produits <-> catégories
CREATE TABLE `product_categories` (
    `product_id`  INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`product_id`, `category_id`),
    CONSTRAINT `fk_pc_product`  FOREIGN KEY (`product_id`)  REFERENCES `products`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Peupler le pivot avec la catégorie existante de chaque produit
INSERT IGNORE INTO `product_categories` (`product_id`, `category_id`)
SELECT `id`, `category_id` FROM `products` WHERE `category_id` IS NOT NULL;

-- 3. Réservations : adresses
ALTER TABLE `reservations`
    ADD COLUMN `customer_address` TEXT NULL AFTER `customer_phone`,
    ADD COLUMN `event_address`    TEXT NULL AFTER `customer_address`;

-- 4. Packs
CREATE TABLE `packs` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(255) NOT NULL,
    `slug`          VARCHAR(255) NOT NULL UNIQUE,
    `description`   TEXT NULL,
    `price_per_day` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pack_items` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `pack_id`    INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `quantity`   INT UNSIGNED NOT NULL DEFAULT 1,
    CONSTRAINT `fk_pi_pack`    FOREIGN KEY (`pack_id`)    REFERENCES `packs`(`id`)    ON DELETE CASCADE,
    CONSTRAINT `fk_pi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. reservation_items : lien optionnel vers un pack
ALTER TABLE `reservation_items`
    ADD COLUMN `pack_id` INT UNSIGNED NULL DEFAULT NULL AFTER `quantity`,
    ADD CONSTRAINT `fk_ri_pack` FOREIGN KEY (`pack_id`) REFERENCES `packs`(`id`) ON DELETE SET NULL;

SET foreign_key_checks = 1;

SELECT 'Migration v2 terminée avec succès.' AS status;
