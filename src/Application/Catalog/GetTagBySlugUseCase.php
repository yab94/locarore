<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Entity\Tag;
use Rore\Domain\Catalog\Repository\TagRepositoryInterface;

/**
 * Récupère un tag par son slug.
 */
final class GetTagBySlugUseCase
{
    public function __construct(
        private readonly TagRepositoryInterface $tagRepo,
    ) {}

    public function execute(string $slug): ?Tag
    {
        return $this->tagRepo->findBySlug($slug);
    }
}
