<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\Port;

use Rore\Domain\Catalog\Entity\Pack;

interface PackRepositoryInterface
{
    /** @return Pack[] */
    public function findAll(): array;

    /** @return Pack[] */
    public function findAllActive(): array;

    /** @return Pack[] packs actifs contenant au moins un produit de cette catégorie */
    public function findActiveByCategorySlug(string $slug): array;

    /** @return Pack[] packs actifs contenant au moins un produit avec ce tag */
    public function findActiveByTagSlug(string $slug): array;

    public function findById(int $id): ?Pack;

    public function findBySlug(string $slug): ?Pack;

    public function save(Pack $pack): int;

    public function delete(int $id): void;
}
