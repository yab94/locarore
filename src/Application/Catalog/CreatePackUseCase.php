<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Entity\PackItem;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;

class CreatePackUseCase
{
    public function __construct(
        private PackRepositoryInterface $packRepository,
    ) {}

    /**
     * @param array<int, int> $items [productId => quantity]
     */
    public function execute(
        string  $name,
        ?string $description,
        float   $pricePerDay,
        array   $items,
        ?string $customSlug = null,
    ): int {
        $now  = new \DateTimeImmutable();
        $slug = Slug::from($customSlug ?? $name)->getValue();

        $packItems = [];
        foreach ($items as $productId => $qty) {
            if ((int) $qty < 1) continue;
            $packItems[] = new PackItem(null, 0, (int) $productId, (int) $qty);
        }

        $pack = new Pack(
            id:          null,
            name:        $name,
            slug:        $slug,
            description: $description,
            pricePerDay: $pricePerDay,
            isActive:    true,
            createdAt:   $now,
            updatedAt:   $now,
        );
        $pack->setItems($packItems);

        return $this->packRepository->save($pack);
    }
}
