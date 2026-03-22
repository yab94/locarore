-- Migration : remplacement de price_per_day par le modèle tarifaire WE/SEM
-- Appliquer sur la table products uniquement (packs gardent price_per_day)

ALTER TABLE products
    CHANGE COLUMN `price_per_day` `price_base` DECIMAL(10,2) NOT NULL DEFAULT 80.00,
    ADD COLUMN `price_extra_we`   DECIMAL(10,2) NOT NULL DEFAULT 0.00  AFTER `price_base`,
    ADD COLUMN `price_extra_sem`  DECIMAL(10,2) NOT NULL DEFAULT 15.00 AFTER `price_extra_we`;
