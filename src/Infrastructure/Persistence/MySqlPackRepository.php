<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Framework\Database;
use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\PackItem;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;

class MySqlPackRepository implements PackRepositoryInterface
{


    public function __construct(private readonly Database $connection)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->connection->query('SELECT * FROM packs ORDER BY name');
        $packs = array_map([$this, 'hydrate'], $stmt->fetchAll());
        return $this->loadItems($packs);
    }

    public function findAllActive(): array
    {
        $stmt = $this->connection->prepare('SELECT * FROM packs WHERE is_active = 1 ORDER BY name');
        $stmt->execute();
        $packs = array_map([$this, 'hydrate'], $stmt->fetchAll());
        return $this->loadItems($packs);
    }

    public function findActiveByCategorySlug(string $slug): array
    {
        $stmt = $this->connection->prepare(
            'SELECT DISTINCT pk.*
               FROM packs pk
               JOIN pack_items pi ON pi.pack_id = pk.id
               JOIN products p ON p.id = pi.product_id
               JOIN categories c ON c.slug = ?
              WHERE pk.is_active = 1
                AND p.is_active  = 1
                AND c.is_active  = 1
                AND (
                    p.category_id = c.id
                    OR EXISTS (
                        SELECT 1 FROM product_categories pc
                        WHERE pc.product_id = p.id AND pc.category_id = c.id
                    )
                )
              ORDER BY pk.name'
        );
        $stmt->execute([$slug]);
        $packs = array_map([$this, 'hydrate'], $stmt->fetchAll());
        return $this->loadItems($packs);
    }

    public function findActiveByTagSlug(string $slug): array
    {
        $stmt = $this->connection->prepare(
            'SELECT DISTINCT pk.*
               FROM packs pk
               JOIN pack_items pi ON pi.pack_id = pk.id
               JOIN product_tags pt ON pt.product_id = pi.product_id
               JOIN tags t ON t.id = pt.tag_id
              WHERE pk.is_active = 1
                AND t.slug = ?
              ORDER BY pk.name'
        );
        $stmt->execute([$slug]);
        $packs = array_map([$this, 'hydrate'], $stmt->fetchAll());
        return $this->loadItems($packs);
    }

    public function findById(int $id): ?Pack
    {
        $stmt = $this->connection->prepare('SELECT * FROM packs WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $pack = $this->hydrate($row);
        $pack->setItems($this->fetchItems($id));
        return $pack;
    }

    public function findBySlug(string $slug): ?Pack
    {
        $stmt = $this->connection->prepare('SELECT * FROM packs WHERE slug = ?');
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
            $stmt = $this->connection->prepare(
                'INSERT INTO packs (name, slug, description, description_short, price_per_day, price_extra_weekend, price_extra_weekday, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $pack->getName(),
                $pack->getSlug(),
                $pack->getDescription(),
                $pack->getDescriptionShort(),
                $pack->getPricePerDay(),
                $pack->getPriceExtraWeekend(),
                $pack->getPriceExtraWeekday(),
                (int) $pack->isActive(),
                $pack->getCreatedAt()->format('Y-m-d H:i:s'),
                $pack->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]);
            $id = (int) $this->connection->lastInsertId();
        } else {
            $stmt = $this->connection->prepare(
                'UPDATE packs
                    SET name = ?, slug = ?, description = ?, description_short = ?, price_per_day = ?, price_extra_weekend = ?, price_extra_weekday = ?, is_active = ?, updated_at = ?
                  WHERE id = ?'
            );
            $stmt->execute([
                $pack->getName(),
                $pack->getSlug(),
                $pack->getDescription(),
                $pack->getDescriptionShort(),
                $pack->getPricePerDay(),
                $pack->getPriceExtraWeekend(),
                $pack->getPriceExtraWeekday(),
                (int) $pack->isActive(),
                $pack->getUpdatedAt()->format('Y-m-d H:i:s'),
                $pack->getId(),
            ]);
            $id = $pack->getId();
        }

        // Sync pack_items
        $this->connection->prepare('DELETE FROM pack_items WHERE pack_id = ?')->execute([$id]);
        $stmtFixed = $this->connection->prepare(
            'INSERT INTO pack_items (pack_id, product_id, category_id, quantity) VALUES (?, ?, NULL, ?)'
        );
        $stmtSlot = $this->connection->prepare(
            'INSERT INTO pack_items (pack_id, product_id, category_id, quantity) VALUES (?, NULL, ?, ?)'
        );
        foreach ($pack->getItems() as $item) {
            if ($item->isFixed()) {
                $stmtFixed->execute([$id, $item->getProductId(), $item->getQuantity()]);
            } else {
                $stmtSlot->execute([$id, $item->getCategoryId(), $item->getQuantity()]);
            }
        }

        return $id;
    }

    public function delete(int $id): void
    {
        $this->connection->prepare('DELETE FROM packs WHERE id = ?')->execute([$id]);
    }

    // --- Private helpers -------------------------------------------------

    /** @param Pack[] $packs */
    private function loadItems(array $packs): array
    {
        if (empty($packs)) return $packs;

        $ids = implode(',', array_map(fn($p) => $p->getId(), $packs));
        $stmt = $this->connection->query(
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
        $stmt = $this->connection->prepare('SELECT * FROM pack_items WHERE pack_id = ? ORDER BY id');
        $stmt->execute([$packId]);
        return array_map([$this, 'hydrateItem'], $stmt->fetchAll());
    }

    private function hydrate(array $row): Pack
    {
        return new Pack(
            id:                 (int) $row['id'],
            name:               $row['name'],
            slug:               $row['slug'],
            description:        $row['description'],
            descriptionShort:   $row['description_short'] ?? null,
            pricePerDay:        (float) $row['price_per_day'],
            priceExtraWeekend:  (float) ($row['price_extra_weekend'] ?? 0),
            priceExtraWeekday:  (float) ($row['price_extra_weekday'] ?? 0),
            isActive:           (bool) $row['is_active'],
            createdAt:          new \DateTimeImmutable($row['created_at']),
            updatedAt:          new \DateTimeImmutable($row['updated_at']),
        );
    }

    private function hydrateItem(array $row): PackItem
    {
        return new PackItem(
            id:         (int) $row['id'],
            packId:     (int) $row['pack_id'],
            productId:  isset($row['product_id'])  ? (int) $row['product_id']  : null,
            categoryId: isset($row['category_id']) ? (int) $row['category_id'] : null,
            quantity:   (int) $row['quantity'],
        );
    }
}
