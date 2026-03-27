<?php

declare(strict_types=1);

namespace Rore\Catalog\Port;

use Rore\Catalog\Entity\Tag;
use Rore\Catalog\Adapter\MySqlTagRepository;

interface TagRepositoryInterface
{
    /** @return Tag[] */
    public function findAll(): array;

    /** @return Tag[] */
    public function findByProductId(int $productId): array;

    public function findBySlug(string $slug): ?Tag;

    /**
     * Crée les tags manquants, puis synchronise la table pivot product_tags.
     * Les tags qui n'apparaissent plus dans $names sont dissociés du produit.
     *
     * @param string[] $names  Noms bruts saisis par l'utilisateur
     */
    public function syncForProduct(int $productId, array $names): void;
}
