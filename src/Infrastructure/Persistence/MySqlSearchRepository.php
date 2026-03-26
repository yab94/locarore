<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\PackItem;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Entity\ProductPhoto;
use Rore\Domain\Catalog\Entity\Tag;
use Rore\Domain\Catalog\Repository\SearchRepositoryInterface;
use Rore\Infrastructure\Database\Connection;

class MySqlSearchRepository implements SearchRepositoryInterface
{
    public function __construct(private readonly Connection $connection) {}

    /**
     * Recherche LIKE sur produits et packs.
     * Champs indexés : name, description, photos.description, categories.name, tags.name
     *
     * @return array{products: Product[], packs: Pack[]}
     */
    public function search(string $query): array
    {
        $like = '%' . $query . '%';

        // ── Produits ──────────────────────────────────────────────────────────
        $stmt = $this->connection->prepare(
            'SELECT DISTINCT p.*
               FROM products p
               LEFT JOIN product_photos pp ON pp.product_id = p.id
               LEFT JOIN categories c ON c.id = p.category_id
               LEFT JOIN product_tags ptag ON ptag.product_id = p.id
               LEFT JOIN tags t ON t.id = ptag.tag_id
              WHERE p.is_active = 1
                AND (
                    p.name        LIKE ?
                 OR p.description LIKE ?
                 OR pp.description LIKE ?
                 OR c.name        LIKE ?
                 OR t.name        LIKE ?
                )
              ORDER BY p.name'
        );
        $stmt->execute([$like, $like, $like, $like, $like]);
        $products = array_map([$this, 'hydrateProduct'], $stmt->fetchAll());
        $this->loadPhotos($products);
        $this->loadTags($products);

        // ── Packs ─────────────────────────────────────────────────────────────
        $stmt = $this->connection->prepare(
            'SELECT DISTINCT pk.*
               FROM packs pk
              WHERE pk.is_active = 1
                AND (
                    pk.name        LIKE ?
                 OR pk.description LIKE ?
                )
              ORDER BY pk.name'
        );
        $stmt->execute([$like, $like]);
        $packs = array_map([$this, 'hydratePack'], $stmt->fetchAll());
        $this->loadPackItems($packs);

        // Construire productsById : produits de la recherche + produits fixes des packs
        $productsById = [];
        foreach ($products as $p) {
            $productsById[$p->getId()] = $p;
        }
        $this->loadPackProducts($packs, $productsById);

        return ['products' => $products, 'packs' => $packs, 'productsById' => $productsById];
    }

    // ── Hydratation ───────────────────────────────────────────────────────────

    private function hydrateProduct(array $row): Product
    {
        return new Product(
            id:                 (int) $row['id'],
            categoryId:         (int) $row['category_id'],
            name:               $row['name'],
            slug:               $row['slug'],
            description:        $row['description'],
            stock:              (int) $row['stock'],
            stockOnDemand:      (int) ($row['stock_on_demand'] ?? 0),
            fabricationTimeDays:(float) ($row['fabrication_time_days'] ?? 0.0),
            priceBase:          (float) $row['price_base'],
            priceExtraWeekend:  (float) $row['price_extra_weekend'],
            priceExtraWeekday:  (float) $row['price_extra_weekday'],
            isActive:           (bool) $row['is_active'],
            createdAt:          new \DateTimeImmutable($row['created_at']),
            updatedAt:          new \DateTimeImmutable($row['updated_at']),
        );
    }

    private function hydratePack(array $row): Pack
    {
        return new Pack(
            id:                (int) $row['id'],
            name:              $row['name'],
            slug:              $row['slug'],
            description:       $row['description'],
            pricePerDay:       (float) $row['price_per_day'],
            priceExtraWeekend: (float) ($row['price_extra_weekend'] ?? 0),
            priceExtraWeekday: (float) ($row['price_extra_weekday'] ?? 0),
            isActive:          (bool) $row['is_active'],
            createdAt:         new \DateTimeImmutable($row['created_at']),
            updatedAt:         new \DateTimeImmutable($row['updated_at']),
        );
    }

    // ── Loaders ──────────────────────────────────────────────────────────────

    /** @param Product[] $products */
    private function loadPhotos(array $products): void
    {
        if (empty($products)) return;
        $ids  = implode(',', array_map(fn($p) => $p->getId(), $products));
        $stmt = $this->connection->query(
            "SELECT * FROM product_photos WHERE product_id IN ($ids) ORDER BY product_id, sort_order"
        );
        $byProduct = [];
        foreach ($stmt->fetchAll() as $row) {
            $byProduct[(int) $row['product_id']][] = new ProductPhoto(
                id:          (int) $row['id'],
                productId:   (int) $row['product_id'],
                filename:    $row['filename'],
                sortOrder:   (int) $row['sort_order'],
                createdAt:   new \DateTimeImmutable($row['created_at']),
                description: $row['description'] ?? null,
            );
        }
        foreach ($products as $product) {
            $product->setPhotos($byProduct[$product->getId()] ?? []);
        }
    }

    /** @param Product[] $products */
    private function loadTags(array $products): void
    {
        if (empty($products)) return;
        $ids  = implode(',', array_map(fn($p) => $p->getId(), $products));
        $stmt = $this->connection->query(
            "SELECT pt.product_id, t.id, t.name, t.slug
               FROM product_tags pt
               JOIN tags t ON t.id = pt.tag_id
              WHERE pt.product_id IN ($ids)
              ORDER BY t.name"
        );
        $byProduct = [];
        foreach ($stmt->fetchAll() as $row) {
            $byProduct[(int) $row['product_id']][] = new Tag(
                id:   (int) $row['id'],
                name: $row['name'],
                slug: $row['slug'],
            );
        }
        foreach ($products as $product) {
            $product->setTags($byProduct[$product->getId()] ?? []);
        }
    }

    /**
     * Charge dans $productsById les produits fixes des packs qui n'y sont pas encore.
     * @param Pack[] $packs
     * @param array<int, Product> $productsById  tableau à compléter (passé par référence)
     */
    private function loadPackProducts(array $packs, array &$productsById): void
    {
        $needed = [];
        foreach ($packs as $pack) {
            foreach ($pack->getItems() as $item) {
                if ($item->isFixed()) {
                    $id = $item->getProductId();
                    if ($id !== null && !isset($productsById[$id])) {
                        $needed[] = $id;
                    }
                }
            }
        }
        if (empty($needed)) return;

        $placeholders = implode(',', array_fill(0, count($needed), '?'));
        $stmt = $this->connection->prepare(
            "SELECT * FROM products WHERE id IN ($placeholders)"
        );
        $stmt->execute($needed);
        $extraProducts = array_map([$this, 'hydrateProduct'], $stmt->fetchAll());
        $this->loadPhotos($extraProducts);
        foreach ($extraProducts as $p) {
            $productsById[$p->getId()] = $p;
        }
    }

    /** @param Pack[] $packs */
    private function loadPackItems(array $packs): void
    {
        if (empty($packs)) return;
        $ids  = implode(',', array_map(fn($p) => $p->getId(), $packs));
        $stmt = $this->connection->query(
            "SELECT * FROM pack_items WHERE pack_id IN ($ids) ORDER BY pack_id, id"
        );
        $byPack = [];
        foreach ($stmt->fetchAll() as $row) {
            $byPack[(int) $row['pack_id']][] = new PackItem(
                id:         (int) $row['id'],
                packId:     (int) $row['pack_id'],
                productId:  isset($row['product_id'])  ? (int) $row['product_id']  : null,
                categoryId: isset($row['category_id']) ? (int) $row['category_id'] : null,
                quantity:   (int) $row['quantity'],
            );
        }
        foreach ($packs as $pack) {
            $pack->setItems($byPack[$pack->getId()] ?? []);
        }
    }
}