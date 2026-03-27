<?php

declare(strict_types=1);

namespace Rore\Application\Catalog\UseCase;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\PackItem;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;
use Rore\Domain\Catalog\Service\SlugUniquenessService;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use RRB\Di\BindAdapter;

class CreatePackUseCase
{
    public function __construct(
        #[BindAdapter(MySqlPackRepository::class)]
        private PackRepositoryInterface $packRepository,
        private SlugUniquenessService   $slugChecker,
    ) {}

    /**
     * @param array<int, int> $items [productId => quantity]
     * @param array<int, int> $slots [categoryId => quantity]
     */
    public function execute(
        string  $name,
        ?string $description,
        float   $pricePerDay,
        float   $priceExtraWeekend,
        float   $priceExtraWeekday,
        array   $items,
        array   $slots            = [],
        ?string $customSlug       = null,
        ?string $descriptionShort = null,
    ): int {
        $now  = new \DateTimeImmutable();
        $slug = Slug::from($customSlug ?? $name)->getValue();

        if ($this->slugChecker->isTaken($slug, 'pack')) {
            throw new \DomainException("Le slug « $slug » est déjà utilisé.");
        }

        $packItems = [];
        foreach ($items as $productId => $qty) {
            if ((int) $qty < 1) continue;
            $packItems[] = new PackItem(null, 0, (int) $productId, null, (int) $qty);
        }
        foreach ($slots as $categoryId => $qty) {
            if ((int) $qty < 1) continue;
            $packItems[] = new PackItem(null, 0, null, (int) $categoryId, (int) $qty);
        }

        $pack = new Pack(
            id:                null,
            name:              $name,
            slug:              $slug,
            description:       $description,
            descriptionShort:  $descriptionShort,
            pricePerDay:       $pricePerDay,
            priceExtraWeekend: $priceExtraWeekend,
            priceExtraWeekday: $priceExtraWeekday,
            isActive:          true,
            createdAt:         $now,
            updatedAt:         $now,
        );
        $pack->setItems($packItems);

        return $this->packRepository->save($pack);
    }
}
