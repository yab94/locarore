<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Repository;

use Rore\Domain\Catalog\Entity\Pack;

interface PackRepositoryInterface
{
    /** @return Pack[] */
    public function findAll(): array;

    /** @return Pack[] */
    public function findAllActive(): array;

    public function findById(int $id): ?Pack;

    public function findBySlug(string $slug): ?Pack;

    public function save(Pack $pack): int;

    public function delete(int $id): void;
}
