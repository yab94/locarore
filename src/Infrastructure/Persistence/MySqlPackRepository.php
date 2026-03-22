<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\PackItem;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Infrastructure\Database\Connection;

class MySqlPackRepository implements PackRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM packs ORDER BY name');
        $packs = array_map([$this, 'hydrate'], $stmt->fetchAll());
        return $this->loadItems($packs);
    }

    public function findAllActive(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM packs WHERE is_active = 1 ORDER BY name');
        $stmt->execute();
        $packs = array_map([$this, 'hydrate'], $stmt->fetchAll());
        return $this->loadItems($packs);
    }

    public function findById(int $id): ?Pack
    {
        $stmt = $this->pdo->prepare('SELECT * FROM packs WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $pack = $this->hydrate($row);
        $pack->setItems($this->fetchItems($id));
        return $pack;
    }

    public function findBySlug(string $slug): ?Pack
    {
        $stmt = $this->pdo->prepare('SELECT * FROM packs WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $pack = $this->hydrate($row);
        $pack->setItems($this->fetchItems($pack->getId()));
        return $pack;
    }

    public function save(Pack $pack): int
    {
        if ($pack->getId() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO packs (name, slug, description, price_per_day, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $pack->getName(),
                $pack->getSlug(),
                $pack->getDescription(),
                $pack->getPricePerDay(),
                (int) $pack->isActive(),
                $pack->getCreatedAt()->format('Y-m-d H:i:s'),
                $pack->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]);
            $id = (int) $this->pdo->lastInsertId();
        } else {
            $stmt = $this->pdo->prepare(
                'UPDATE packs
                    SET name = ?, slug = ?, description = ?, price_per_day = ?, is_active = ?, updated_at = ?
                  WHERE id = ?'
            );
            $stmt->execute([
                $pack->getName(),
                $pack->getSlug(),
                $pack->getDescription(),
                $pack->getPricePerDay(),
                (int) $pack->isActive(),
                $pack->getUpdatedAt()->format('Y-m-d H:i:s'),
                $pack->getId(),
            ]);
            $id = $pack->getId();
        }

        // Sync pack_items
        $this->pdo->prepare('DELETE FROM pack_items WHERE pack_id = ?')->execute([$id]);
        $stmt = $this->pdo->prepare(
            'INSERT INTO pack_items (pack_id, product_id, quantity) VALUES (?, ?, ?)'
        );
        foreach ($pack->getItems() as $item) {
            $stmt->execute([$id, $item->getProductId(), $item->getQuantity()]);
        }

        return $id;
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM packs WHERE id = ?')->execute([$id]);
    }

    // --- Private helpers -------------------------------------------------

    /** @param Pack[] $packs */
    private function loadItems(array $packs): array
    {
        if (empty($packs)) return $packs;

        $ids = implode(',', array_map(fn($p) => $p->getId(), $packs));
        $stmt = $this->pdo->query(
            "SELECT * FROM pack_items WHERE pack_id IN ($ids) ORDER BY pack_id, id"
        );

        $itemsByPack = [];
        foreach ($stmt->fetchAll() as $row) {
            $itemsByPack[(int) $row['pack_id']][] = $this->hydrateItem($row);
        }

        foreach ($packs as $pack) {
            $pack->setItems($itemsByPack[$pack->getId()] ?? []);
        }

        return $packs;
    }

    private function fetchItems(int $packId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pack_items WHERE pack_id = ? ORDER BY id');
        $stmt->execute([$packId]);
        return array_map([$this, 'hydrateItem'], $stmt->fetchAll());
    }

    private function hydrate(array $row): Pack
    {
        return new Pack(
            id:          (int) $row['id'],
            name:        $row['name'],
            slug:        $row['slug'],
            description: $row['description'],
            pricePerDay: (float) $row['price_per_day'],
            isActive:    (bool) $row['is_active'],
            createdAt:   new \DateTimeImmutable($row['created_at']),
            updatedAt:   new \DateTimeImmutable($row['updated_at']),
        );
    }

    private function hydrateItem(array $row): PackItem
    {
        return new PackItem(
            id:        (int) $row['id'],
            packId:    (int) $row['pack_id'],
            productId: (int) $row['product_id'],
            quantity:  (int) $row['quantity'],
        );
    }
}
