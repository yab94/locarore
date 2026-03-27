<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\Port;

use Rore\Domain\Catalog\Entity\Category;
use Rore\Infrastructure\Persistence\MySqlCategoryRepository;

interface CategoryRepositoryInterface
{
    /** @return Category[] flat list */
    public function findAll(): array;

    /** @return Category[] flat list */
    public function findAllActive(): array;

    /** @return Category[] racines actives avec children pré-chargés */
    public function findRootsWithChildren(): array;

    public function findById(int $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    public function save(Category $category): void;

    public function delete(int $id): void;
}
