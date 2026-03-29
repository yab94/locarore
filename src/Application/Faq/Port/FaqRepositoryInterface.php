<?php

declare(strict_types=1);

namespace Rore\Application\Faq\Port;

use Rore\Domain\Faq\Entity\FaqItem;

interface FaqRepositoryInterface
{
    /** @return FaqItem[] */
    public function findAll(): array;

    /** @return FaqItem[] visibles uniquement, triés par position */
    public function findAllVisible(): array;

    public function findById(int $id): ?FaqItem;

    public function save(FaqItem $item): void;

    public function delete(int $id): void;
}
