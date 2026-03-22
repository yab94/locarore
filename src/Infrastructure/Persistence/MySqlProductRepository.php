<?php

declare(strict_types=1);

namespace Rore\Infrastructure\Persistence;

use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Entity\ProductPhoto;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Infrastructure\Database\Connection;

class MySqlProductRepository implements ProductRepositoryInterface
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Connection::get();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM products ORDER BY name');
        $products = array_map([$this, 'hydrate'], $stmt->fetchAll());
        $this->loadPhotos($products);
        $this->loadCategoryIds($products);
        return $products;
    }

    public function findAllActive(): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE is_active = 1 ORDER BY name');
        $stmt->execute();
        $products = array_map([$this, 'hydrate'], $stmt->fetchAll());
        $this->loadPhotos($products);
        $this->loadCategoryIds($products);
        return $products;
    }

    /**
     * Produits actifs d'une catégorie (par son slug),
     * en tenant compte du pivot product_categories ET de la catégorie principale.
     */
    public function findActiveByCategorySlug(string $slug): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT DISTINCT p.*
               FROM products p
               JOIN categories c ON c.slug = ?
              WHERE p.is_active = 1
                AND c.is_active = 1
                AND (
                    p.category_id = c.id
                    OR EXISTS (
                        SELECT 1 FROM product_categories pc
                        WHERE pc.product_id = p.id AND pc.category_id = c.id
                    )
                )
              ORDER BY p.name'
        );
        $stmt->execute([$slug]);
        $products = array_map([$this, 'hydrate'], $stmt->fetchAll());
        $this->loadPhotos($products);
        $this->loadCategoryIds($products);
        return $products;
    }

    public function findById(int $id): ?Product
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $product = $this->hydrate($row);
        $product->setPhotos($this->findPhotosByProductId($id));
        $this->loadCategoryIds([$product]);
        return $product;
    }

    public function findBySlug(string $slug): ?Product
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE slug = ?');
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $product = $this->hydrate($row);
        $product->setPhotos($this->findPhotosByProductId($product->getId()));
        $this->loadCategoryIds([$product]);
        return $product;
    }

    public function save(Product $product): int
    {
        if ($product->getId() === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO products
                    (category_id, name, slug, description, stock, price_per_day, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $product->getCategoryId(),
                $product->getName(),
                $product->getSlug(),
                $product->getDescription(),
                $product->getStock(),
                $product->getPricePerDay(),
                (int) $product->isActive(),
                $product->getCreatedAt()->format('Y-m-d H:i:s'),
                $product->getUpdatedAt()->format('Y-m-d H:i:s'),
            ]);
            $id = (int) $this->pdo->lastInsertId();
        } else {
            $stmt = $this->pdo->prepare(
                'UPDATE products
                    SET category_id = ?, name = ?, slug = ?, description = ?,
                        stock = ?, price_per_day = ?, is_active = ?, updated_at = ?
                  WHERE id = ?'
            );
            $stmt->execute([
                $product->getCategoryId(),
                $product->getName(),
                $product->getSlug(),
                $product->getDescription(),
                $product->getStock(),
                $product->getPricePerDay(),
                (int) $product->isActive(),
                $product->getUpdatedAt()->format('Y-m-d H:i:s'),
                $product->getId(),
            ]);
            $id = $product->getId();
        }

        // Sync pivot product_categories (toutes les catégories, principale incluse)
        $categoryIds = $product->getCategoryIds();
        if (!in_array($product->getCategoryId(), $categoryIds)) {
            $categoryIds[] = $product->getCategoryId();
        }
        $this->pdo->prepare('DELETE FROM product_categories WHERE product_id = ?')->execute([$id]);
        $stmt = $this->pdo->prepare('INSERT INTO product_categories (product_id, category_id) VALUES (?, ?)');
        foreach (array_unique($categoryIds) as $catId) {
            $stmt->execute([$id, (int) $catId]);
        }

        return $id;
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
    }

    public function savePhoto(ProductPhoto $photo): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO product_photos (product_id, filename, sort_order, created_at) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([
            $photo->getProductId(),
            $photo->getFilename(),
            $photo->getSortOrder(),
            $photo->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function deletePhoto(int $photoId): void
    {
        $this->pdo->prepare('DELETE FROM product_photos WHERE id = ?')->execute([$photoId]);
    }

    public function findPhotoById(int $photoId): ?ProductPhoto
    {
        $stmt = $this->pdo->prepare('SELECT * FROM product_photos WHERE id = ?');
        $stmt->execute([$photoId]);
        $row = $stmt->fetch();
        return $row ? $this->hydratePhoto($row) : null;
    }

    public function findPhotosByProductId(int $productId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM product_photos WHERE product_id = ? ORDER BY sort_order ASC'
        );
        $stmt->execute([$productId]);
        return array_map([$this, 'hydratePhoto'], $stmt->fetchAll());
    }

    // --- Private helpers ---------------------------------------------------

    /** @param Product[] $products */
    private function loadPhotos(array $products): void
    {
        if (empty($products)) return;
        $ids = implode(',', array_map(fn($p) => $p->getId(), $products));
        $stmt = $this->pdo->query(
            "SELECT * FROM product_photos WHERE product_id IN ($ids) ORDER BY sort_order ASC"
        );
        $photosByProduct = [];
        foreach ($stmt->fetchAll() as $row) {
            $photosByProduct[(int) $row['product_id']][] = $this->hydratePhoto($row);
        }
        foreach ($products as $product) {
            $product->setPhotos($photosByProduct[$product->getId()] ?? []);
        }
    }

    /** @param Product[] $products */
    private function loadCategoryIds(array $products): void
    {
        if (empty($products)) return;
        $ids = implode(',', array_map(fn($p) => $p->getId(), $products));
        $stmt = $this->pdo->query(
            "SELECT product_id, category_id FROM product_categories WHERE product_id IN ($ids)"
        );
        $catsByProduct = [];
        foreach ($stmt->fetchAll() as $row) {
            $catsByProduct[(int) $row['product_id']][] = (int) $row['category_id'];
        }
        foreach ($products as $product) {
            $all = $catsByProduct[$product->getId()] ?? [];
            // S'assurer que la catégorie principale est incluse
            if (!in_array($product->getCategoryId(), $all)) {
                $all[] = $product->getCategoryId();
            }
            $product->setCategoryIds($all);
        }
    }

    private function hydrate(array $row): Product
    {
        return new Product(
            id:          (int) $row['id'],
            categoryId:  (int) $row['category_id'],
            name:        $row['name'],
            slug:        $row['slug'],
            description: $row['description'],
            stock:       (int) $row['stock'],
            pricePerDay: (float) $row['price_per_day'],
            isActive:    (bool) $row['is_active'],
            createdAt:   new \DateTimeImmutable($row['created_at']),
            updatedAt:   new \DateTimeImmutable($row['updated_at']),
        );
    }

    private function hydratePhoto(array $row): ProductPhoto
    {
        return new ProductPhoto(
            id:         (int) $row['id'],
            productId:  (int) $row['product_id'],
            filename:   $row['filename'],
            sortOrder:  (int) $row['sort_order'],
            createdAt:  new \DateTimeImmutable($row['created_at']),
        );
    }
}
