<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Domain\Catalog\Entity\Tag;
use Rore\Domain\Catalog\Repository\TagRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;
use Rore\Framework\Database;

class MySqlTagRepository implements TagRepositoryInterface
{
    public function __construct(private readonly Database $connection) {}

    /** @return Tag[] */
    public function findAll(): array
    {
        $stmt = $this->connection->query('SELECT * FROM tags ORDER BY name');
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    /** @return Tag[] */
    public function findByProductId(int $productId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT t.*
               FROM tags t
               JOIN product_tags pt ON pt.tag_id = t.id
              WHERE pt.product_id = ?
              ORDER BY t.name'
        );
        $stmt->execute([$productId]);
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    public function findBySlug(string $slug): ?Tag
    {
        $stmt = $this->connection->prepare('SELECT * FROM tags WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    /**
     * Trouve ou crée chaque tag par son nom, puis synchronise product_tags.
     *
     * @param string[] $names
     */
    public function syncForProduct(int $productId, array $names): void
    {
        // Supprimer toutes les associations existantes
        $stmt = $this->connection->prepare(
            'DELETE FROM product_tags WHERE product_id = ?'
        );
        $stmt->execute([$productId]);

        $names = array_values(array_unique(array_filter(array_map('trim', $names))));
        if (empty($names)) {
            return;
        }

        foreach ($names as $name) {
            $slug = Slug::from($name)->getValue();

            // Créer le tag si inexistant
            $stmt = $this->connection->prepare(
                'INSERT IGNORE INTO tags (name, slug) VALUES (?, ?)'
            );
            $stmt->execute([$name, $slug]);

            // Récupérer l'ID (existant ou fraîchement créé)
            $stmt = $this->connection->prepare(
                'SELECT id FROM tags WHERE slug = ?'
            );
            $stmt->execute([$slug]);
            $tagId = (int) $stmt->fetchColumn();

            if ($tagId === 0) {
                continue;
            }

            $stmt = $this->connection->prepare(
                'INSERT IGNORE INTO product_tags (product_id, tag_id) VALUES (?, ?)'
            );
            $stmt->execute([$productId, $tagId]);
        }
    }

    private function hydrate(array $row): Tag
    {
        return new Tag(
            id:   (int) $row['id'],
            name: $row['name'],
            slug: $row['slug'],
        );
    }
}
