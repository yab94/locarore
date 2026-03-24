-- ============================================================
-- Migrations — à appliquer sur une base existante
-- ============================================================

-- --------------------------------------------------------
-- tags
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tags` (
    `id`   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100)  NOT NULL,
    `slug` VARCHAR(120)  NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_tags_slug` (`slug`),
    UNIQUE KEY `uq_tags_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- product_tags  (pivot)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_tags` (
    `product_id` INT UNSIGNED NOT NULL,
    `tag_id`     INT UNSIGNED NOT NULL,
    PRIMARY KEY (`product_id`, `tag_id`),
    KEY `idx_pt_tag` (`tag_id`),
    CONSTRAINT `fk_pt_product`
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_pt_tag`
        FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- [2026-03-24] Ajout description sur les photos produit
ALTER TABLE `product_photos`
    ADD COLUMN `description` VARCHAR(255) NULL DEFAULT NULL AFTER `sort_order`;
