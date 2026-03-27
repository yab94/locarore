<?php

declare(strict_types=1);

use Rore\Application\Catalog\CreateProductUseCase;
use Rore\Domain\Catalog\Entity\Category;
use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\Product;
use Rore\Domain\Catalog\Entity\ProductPhoto;
use Rore\Domain\Catalog\Entity\Tag;
use Rore\Domain\Catalog\Repository\CategoryRepositoryInterface;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\Repository\ProductRepositoryInterface;
use Rore\Domain\Catalog\Repository\TagRepositoryInterface;
use Rore\Domain\Catalog\Service\SlugUniquenessService;

// ─── Stubs ───────────────────────────────────────────────────────────────────

/**
 * Repository produit en mémoire.
 * - save()        : auto-incrémente un ID et stocke le produit
 * - findBySlug()  : retrouve un produit par son slug
 */
final class ProductRepositoryStub implements ProductRepositoryInterface
{
    /** @var Product[] */
    private array $products = [];
    private int   $nextId   = 1;

    /** Slug(s) pré-enregistrés pour simuler un conflit */
    private array $takenSlugs = [];

    public function preloadSlug(string $slug): void
    {
        $this->takenSlugs[] = $slug;
    }

    public function save(Product $product): int
    {
        $id = $this->nextId++;
        $this->products[$id] = $product;
        return $id;
    }

    /** @return Product[] */
    public function findAll(): array          { return array_values($this->products); }
    public function findAllActive(): array    { return []; }
    public function findActiveByCategorySlug(string $slug): array { return []; }
    public function findActiveByTagSlug(string $slug): array      { return []; }
    public function findById(int $id): ?Product                   { return $this->products[$id] ?? null; }

    public function findBySlug(string $slug): ?Product
    {
        if (in_array($slug, $this->takenSlugs, true)) {
            // Retourne un produit fantôme pour simuler le conflit
            return new Product(
                id: 999, categoryId: 1, name: 'Existing', slug: $slug,
                description: null, descriptionShort: null, stock: 1,
                stockOnDemand: 0, fabricationTimeDays: 0.0, priceBase: 80.0,
                priceExtraWeekend: 0.0, priceExtraWeekday: 0.0,
                isActive: true,
                createdAt: new DateTimeImmutable(),
                updatedAt: new DateTimeImmutable(),
            );
        }
        foreach ($this->products as $p) {
            if ($p->getSlug() === $slug) return $p;
        }
        return null;
    }

    public function delete(int $id): void           {}
    public function savePhoto(ProductPhoto $p): void {}
    public function deletePhoto(int $id): void       {}
    public function updatePhotoDescription(int $id, string $d): void {}
    public function findPhotoById(int $id): ?ProductPhoto         { return null; }
    public function findPhotosByProductId(int $id): array         { return []; }
    public function updatePhotoSortOrders(array $orders): void    {}
}

/**
 * Repository tags en mémoire.
 * Enregistre les appels à syncForProduct() pour assertion.
 */
final class TagRepositoryStub implements TagRepositoryInterface
{
    /** @var array<array{productId: int, names: string[]}> */
    public array $syncCalls = [];

    public function findAll(): array                         { return []; }
    public function findByProductId(int $id): array          { return []; }
    public function findBySlug(string $slug): ?Tag           { return null; }

    public function syncForProduct(int $productId, array $names): void
    {
        $this->syncCalls[] = ['productId' => $productId, 'names' => $names];
    }
}

/** Stub catégorie — findBySlug() retourne toujours null (pas de conflit) */
final class CategoryRepositoryStub implements CategoryRepositoryInterface
{
    public function findAll(): array                    { return []; }
    public function findAllActive(): array              { return []; }
    public function findRootsWithChildren(): array      { return []; }
    public function findById(int $id): ?Category        { return null; }
    public function findBySlug(string $slug): ?Category { return null; }
    public function save(Category $c): void             {}
    public function delete(int $id): void               {}
}

/** Stub pack — findBySlug() retourne toujours null (pas de conflit) */
final class PackRepositoryStub implements PackRepositoryInterface
{
    public function findAll(): array                  { return []; }
    public function findAllActive(): array            { return []; }
    public function findActiveByCategorySlug(string $s): array { return []; }
    public function findActiveByTagSlug(string $s): array      { return []; }
    public function findById(int $id): ?Pack          { return null; }
    public function findBySlug(string $slug): ?Pack   { return null; }
    public function save(Pack $p): int                { return 0; }
    public function delete(int $id): void             {}
}

// ─── Test ────────────────────────────────────────────────────────────────────

final class CreateProductUseCaseTest
{
    private ProductRepositoryStub $productRepo;
    private TagRepositoryStub     $tagRepo;
    private CreateProductUseCase  $useCase;

    public function setUp(): void
    {
        $this->productRepo = new ProductRepositoryStub();
        $this->tagRepo     = new TagRepositoryStub();

        $slugChecker = new SlugUniquenessService(
            new CategoryRepositoryStub(),
            $this->productRepo,
            new PackRepositoryStub(),
        );

        $this->useCase = new CreateProductUseCase(
            $this->productRepo,
            $slugChecker,
            $this->tagRepo,
        );
    }

    /** execute() retourne l'ID auto-incrémenté par le repository */
    public function testExecuteReturnsSavedId(): void
    {
        $id = $this->useCase->execute(
            categoryId: 1,
            name: 'Arche florale',
            description: null,
            stock: 3,
            priceBase: 120.0,
        );

        Assert::equals(1, $id);
    }

    /** Le slug est dérivé du nom (normalisation, tirets) */
    public function testSlugIsDerivedFromName(): void
    {
        $id = $this->useCase->execute(
            categoryId: 1,
            name: 'Lettre Géante LED',
            description: null,
            stock: 5,
            priceBase: 80.0,
        );

        $saved = $this->productRepo->findById($id);
        Assert::notNull($saved);
        Assert::equals('lettre-geante-led', $saved->getSlug());
    }

    /** Un slug personnalisé prend la priorité sur le nom */
    public function testCustomSlugIsUsed(): void
    {
        $id = $this->useCase->execute(
            categoryId: 1,
            name: 'Arche florale',
            description: null,
            stock: 2,
            priceBase: 150.0,
            customSlug: 'mon-slug-perso',
        );

        $saved = $this->productRepo->findById($id);
        Assert::notNull($saved);
        Assert::equals('mon-slug-perso', $saved->getSlug());
    }

    /** Un slug déjà utilisé lève une DomainException */
    public function testDuplicateSlugThrowsDomainException(): void
    {
        $this->productRepo->preloadSlug('arche-florale');

        Assert::throws(\DomainException::class, function () {
            $this->useCase->execute(
                categoryId: 1,
                name: 'Arche florale',
                description: null,
                stock: 1,
                priceBase: 80.0,
            );
        });
    }

    /** syncForProduct() est appelé avec le bon ID et les bons noms de tags */
    public function testTagsAreSyncedWithReturnedId(): void
    {
        $id = $this->useCase->execute(
            categoryId: 1,
            name: 'Vase lumineux',
            description: null,
            stock: 10,
            priceBase: 60.0,
            tagNames: ['mariage', 'lumineux'],
        );

        Assert::equals(1, count($this->tagRepo->syncCalls), 'syncForProduct appelé une fois');
        Assert::equals($id, $this->tagRepo->syncCalls[0]['productId']);
        Assert::equals(['mariage', 'lumineux'], $this->tagRepo->syncCalls[0]['names']);
    }

    /** La catégorie principale est toujours incluse dans les categoryIds */
    public function testMainCategoryIsAlwaysIncluded(): void
    {
        $id = $this->useCase->execute(
            categoryId: 5,
            name: 'Colonne lumineuse',
            description: null,
            stock: 4,
            priceBase: 90.0,
            extraCategoryIds: [8, 12],
        );

        $saved = $this->productRepo->findById($id);
        Assert::notNull($saved);

        $cats = $saved->getCategoryIds();
        Assert::true(in_array(5, $cats, true), 'catégorie principale présente');
        Assert::true(in_array(8, $cats, true), 'catégorie extra 8 présente');
        Assert::true(in_array(12, $cats, true), 'catégorie extra 12 présente');
    }

    /** Les doublons dans extraCategoryIds + categoryId sont dédupliqués */
    public function testCategoryIdsAreDeduped(): void
    {
        $id = $this->useCase->execute(
            categoryId: 3,
            name: 'Guirlande XXL',
            description: null,
            stock: 20,
            priceBase: 40.0,
            extraCategoryIds: [3, 7],  // 3 = doublon de categoryId
        );

        $saved = $this->productRepo->findById($id);
        Assert::notNull($saved);

        $cats = $saved->getCategoryIds();
        Assert::equals(count($cats), count(array_unique($cats)), 'pas de doublon');
        Assert::equals(2, count($cats)); // 3 et 7 seulement
    }

    /** fabricationTimeDays négatif est ramené à 0 */
    public function testNegativeFabricationTimeIsClampedToZero(): void
    {
        $id = $this->useCase->execute(
            categoryId: 1,
            name: 'Bubble balloon',
            description: null,
            stock: 5,
            priceBase: 30.0,
            fabricationTimeDays: -3.0,
        );

        $saved = $this->productRepo->findById($id);
        Assert::notNull($saved);
        Assert::equals(0.0, $saved->getFabricationTimeDays());
    }
}
