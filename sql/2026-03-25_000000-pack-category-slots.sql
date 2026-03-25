-- --------------------------------------------------------
-- Migration : pack_items — support des slots catégorie
--
-- Un item de pack peut désormais être :
--   · un produit fixe  → product_id NOT NULL, category_id NULL
--   · un slot catégorie → product_id NULL,     category_id NOT NULL
--   La contrainte CHECK garantit l'exclusivité mutuelle.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `pack_items`
    MODIFY COLUMN `product_id` INT UNSIGNED NULL DEFAULT NULL,
    ADD COLUMN  `category_id` INT UNSIGNED NULL DEFAULT NULL AFTER `product_id`,
    ADD KEY `idx_pi_category` (`category_id`),
    ADD CONSTRAINT `fk_pi_category`
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
        ON DELETE RESTRICT ON UPDATE CASCADE;

SET FOREIGN_KEY_CHECKS = 1;
