<?php

declare(strict_types=1);

namespace Rore\Application\Catalog;

use Rore\Domain\Catalog\Repository\TagRepositoryInterface;

/**
 * Récupère tous les tags du catalogue.
 */
final class GetAllTagsUseCase
{
    public function __construct(
        private readonly TagRepositoryInterface $tagRepo,
    ) {}

    /**
     * @return array<int, \Rore\Domain\Catalog\Entity\Tag>
     */
    public function execute(): array
    {
        return $this->tagRepo->findAll();
    }
}
