<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\PackItem;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;
use Rore\Domain\Catalog\ValueObject\Slug;
use Rore\Domain\Catalog\Service\SlugUniquenessService;
use Rore\Infrastructure\Persistence\MySqlPackRepository;
use RRB\Di\BindAdapter;

class UpdatePackUseCase
{
    public function __construct(
        #[BindAdapter(MySqlPackRepository::class)]
        private PackRepositoryInterface $packRepository,
        private SlugUniquenessService   $slugChecker,
    ) {}

    /**
    /**
     * @param array<int, int> $items [productId => quantity]
     * @param array<int, int> $slots [categoryId => quantity]
     */
    public function execute(
        int     $id,
        string  $name,
        ?string $description,
        float   $pricePerDay,
        float   $priceExtraWeekend,
        float   $priceExtraWeekday,
        array   $items,
        array   $slots            = [],
        ?string $customSlug       = null,
        ?string $descriptionShort = null,
    ): void {
        $pack = $this->packRepository->findById($id);
        if (!$pack) {
            throw new \RuntimeException("Pack introuvable.");
        }

        $slug = $customSlug ? Slug::from($customSlug)->getValue()
                            : Slug::from($name)->getValue();

        if ($this->slugChecker->isTaken($slug, 'pack', $id)) {
            throw new \DomainException("Le slug « $slug » est déjà utilisé.");
        }

        $pack->setName($name);
        $pack->setSlug($slug);
        $pack->setDescription($description);
        $pack->setDescriptionShort($descriptionShort);
        $pack->setPricePerDay($pricePerDay);
        $pack->setPriceExtraWeekend($priceExtraWeekend);
        $pack->setPriceExtraWeekday($priceExtraWeekday);
        $pack->setUpdatedAt(new \DateTimeImmutable());

        $packItems = [];
        foreach ($items as $productId => $qty) {
            if ((int) $qty < 1) continue;
            $packItems[] = new PackItem(null, $id, (int) $productId, null, (int) $qty);
        }
        foreach ($slots as $categoryId => $qty) {
            if ((int) $qty < 1) continue;
            $packItems[] = new PackItem(null, $id, null, (int) $categoryId, (int) $qty);
        }
        $pack->setItems($packItems);

        $this->packRepository->save($pack);
    }
}
