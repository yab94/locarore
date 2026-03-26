-- [2026-03-26] Ajout description_short sur products et packs
-- Utilisée dans les cards (texte brut, pas WYSIWYG)

ALTER TABLE `products`
    ADD COLUMN `description_short` TEXT NULL DEFAULT NULL AFTER `description`;

ALTER TABLE `packs`
    ADD COLUMN `description_short` TEXT NULL DEFAULT NULL AFTER `description`;
