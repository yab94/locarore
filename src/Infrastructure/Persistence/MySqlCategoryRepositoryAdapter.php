<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Infrastructure\Database\MysqlDatabase;
use Rore\Domain\Catalog\Entity\Category;
use Rore\Application\Catalog\Port\CategoryRepositoryInterface;

class MySqlCategoryRepositoryAdapter implements CategoryRepositoryInterface
{
    public function __construct(
        private readonly MysqlDatabase $connection
    ) {}

    public function findAll(): array
    {
        $stmt = $this->connection->query('SELECT * FROM categories ORDER BY name');
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findAllActive(): array
    {
        $stmt = $this->connection->prepare('SELECT * FROM categories WHERE is_active = 1 ORDER BY name');
        $stmt->execute();
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /**
     * Retourne les catégories racines (parent_id IS NULL) actives,
     * avec leurs sous-catégories actives dans $cat->getChildren().
     */
    public function findRootsWithChildren(): array
    {
        $stmt = $this->connection->prepare(
            'SELECT * FROM categories WHERE is_active = 1 ORDER BY parent_id IS NOT NULL, name'
        );
        $stmt->execute();
        $all = array_map([$this, 'hydrate'], $stmt->fetchAll());

        // Indexer par ID
        $byId = [];
        foreach ($all as $cat) {
            $byId[$cat->getId()] = $cat;
        }

        // Construire l'arborescence
        $roots = [];
        foreach ($all as $cat) {
            if ($cat->getParentId() === null) {
                $roots[] = $cat;
            } else {
                $parent = $byId[$cat->getParentId()] ?? null;
                if ($parent) {
                    $parent->setChildren([...$parent->getChildren(), $cat]);
                }
            }
        }

        return $roots;
    }

    public function findById(int $id): ?Category
    {
        $stmt = $this->connection->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findBySlug(string $slug): ?Category
    {
        $stmt = $this->connection->prepare('SELECT * FROM categories WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function save(Category $category): void
    {
        if ($category->getId() === null) {
            $stmt = $this->connection->prepare(
                'INSERT INTO categories (parent_id, name, slug, description_short, description, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $category->getParentId(),
                $category->getName(),
                $category->getSlug(),
                $category->getDescriptionShort(),
                $category->getDescription(),
                (int) $category->isActive(),
                $category->getCreatedAt()->format('Y-m-d H:i:s'),
                $category->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]);
        } else {
            $stmt = $this->connection->prepare(
                'UPDATE categories
                    SET parent_id = ?, name = ?, slug = ?, description_short = ?, description = ?, is_active = ?, updated_at = ?
                  WHERE id = ?'
            );
            $stmt->execute([
                $category->getParentId(),
                $category->getName(),
                $category->getSlug(),
                $category->getDescriptionShort(),
                $category->getDescription(),
                (int) $category->isActive(),
                $category->getUpdatedAt()->format('Y-m-d H:i:s'),
                $category->getId(),
            ]);
        }
    }

    public function delete(int $id): void
    {
        $this->connection->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
    }

    private function hydrate(array $row): Category
    {
        return new Category(
            id:               (int) $row['id'],
            parentId:         isset($row['parent_id']) && $row['parent_id'] !== null ? (int) $row['parent_id'] : null,
            name:             $row['name'],
            slug:             $row['slug'],
            descriptionShort: $row['description_short'] ?? null,
            description:      $row['description'],
            isActive:         (bool) $row['is_active'],
            createdAt:        new \DateTimeImmutable($row['created_at']),
            updatedAt:        new \DateTimeImmutable($row['updated_at']),
        );
    }
}
