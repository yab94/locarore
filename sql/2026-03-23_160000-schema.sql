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
    `description_short` TEXT    NULL,
    `description` LONGTEXT        NULL,
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
    `stock_on_demand` INT          NOT NULL DEFAULT 0,
    `fabrication_time_days` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `price_base`      DECIMAL(10,2)  NOT NULL DEFAULT 80.00,
    `price_extra_weekend` DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `price_extra_weekday` DECIMAL(10,2)  NOT NULL DEFAULT 15.00,
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
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `product_id`  INT UNSIGNED  NOT NULL,
    `filename`    VARCHAR(255)  NOT NULL,
    `sort_order`  INT           NOT NULL DEFAULT 0,
    `description` VARCHAR(255)  NULL DEFAULT NULL,
    `created_at`  DATETIME      NOT NULL,
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
    `product_id`     INT UNSIGNED NULL DEFAULT NULL,
    `pack_id`        INT UNSIGNED NULL DEFAULT NULL,
    `quantity`       INT          NOT NULL,
    `unit_price_snapshot` DECIMAL(10,2) NULL DEFAULT NULL,
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
    `price_per_day`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `price_extra_weekend` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `price_extra_weekday` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
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

-- --------------------------------------------------------
-- settings (mini CMS : clés de personnalisation + blocs éditables)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `key`   VARCHAR(100)                    NOT NULL,
    `value` LONGTEXT                        NULL,
    `label` VARCHAR(200)                    NOT NULL,
    `type`  ENUM('text','richtext')         NOT NULL DEFAULT 'text',
    `group` VARCHAR(50)                     NOT NULL DEFAULT 'general',
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`key`, `value`, `label`, `type`, `group`) VALUES
('site.name',              'Locarore',                                         'Nom du site (logo)',               'text',     'general'),
('site.tagline',           'Location de décoration événementielle',            'Slogan (footer)',                  'text',     'general'),
('hero.title',             'Location de décoration événementielle',            'Hero — titre',                     'text',     'home'),
('hero.subtitle',          'Lettres géantes, arches, vases lumineux et bien plus.', 'Hero — sous-titre',           'text',     'home'),
('hero.cta',               'Découvrir le catalogue',                           'Hero — bouton CTA',                'text',     'home'),
('home.intro',             '',                                                 'Home — bloc intro (Markdown)',     'richtext', 'home'),
('home.categories_title',  'Nos catégories',                                   'Home — titre section catégories',  'text',     'home'),
('home.featured_title',    'Produits populaires',                              'Home — titre section populaires',  'text',     'home'),
('confirmation.title',     'Demande envoyée !',                                'Confirmation — titre',             'text',     'reservation'),
('confirmation.message',   'Nous vous contacterons par email pour confirmer la disponibilité.', 'Message après réservation', 'text', 'reservation'),
('cart.footer_note',       'Votre demande sera confirmée par notre équipe.',   'Panier — note bas de récap',       'text',     'reservation'),
('reservation.status.label.pending',   'En attente',    'Réservation — statut “pending” (badge)',     'text', 'reservation'),
('reservation.status.label.quoted',    'Devis envoyé',  'Réservation — statut “quoted” (badge)',      'text', 'reservation'),
('reservation.status.label.confirmed', 'Confirmée',     'Réservation — statut “confirmed” (badge)',   'text', 'reservation'),
('reservation.status.label.cancelled', 'Annulée',       'Réservation — statut “cancelled” (badge)',   'text', 'reservation'),

('reservation.status.filter.all',       'Tous',         'Réservations — filtre “all” (label)',        'text', 'reservation'),
('reservation.status.filter.pending',   'En attente',   'Réservations — filtre “pending” (label)',     'text', 'reservation'),
('reservation.status.filter.quoted',    'Devis envoyé', 'Réservations — filtre “quoted” (label)',      'text', 'reservation'),
('reservation.status.filter.confirmed', 'Confirmée',    'Réservations — filtre “confirmed” (label)',   'text', 'reservation'),
('reservation.status.filter.cancelled', 'Annulée',      'Réservations — filtre “cancelled” (label)',   'text', 'reservation'),

('mentions.content',       '<h2>Mentions légales</h2><p><strong>Raison sociale :</strong> Locarore SAS<br><strong>SIRET :</strong> 000 000 000 00000<br><strong>Siège social :</strong> 1 rue de la Fête, 75001 Paris<br><strong>Directeur de la publication :</strong> Prénom Nom<br><strong>Contact :</strong> contact@locarore.fr</p><h2>Hébergement</h2><p>Site hébergé par OVHcloud — 2 rue Kellermann, 59100 Roubaix.</p><h2>Données personnelles</h2><p>Conformément au RGPD, vous disposez d\'un droit d\'accès, de rectification et de suppression de vos données. Pour exercer ce droit, contactez-nous à contact@locarore.fr.</p><h2>Cookies</h2><p>Ce site n\'utilise pas de cookies de traçage ou publicitaires.</p>', 'Mentions légales — contenu (WYSIWYG)', 'richtext', 'legal');
