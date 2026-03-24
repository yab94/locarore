<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Pack;
use Rore\Domain\Catalog\Repository\PackRepositoryInterface;

/**
 * Récupère un pack par son slug.
 */
final class GetPackBySlugUseCase
{
    public function __construct(
        private readonly PackRepositoryInterface $packRepo,
    ) {}

    public function execute(string $slug): ?Pack
    {
        return $this->packRepo->findBySlug($slug);
    }
}
