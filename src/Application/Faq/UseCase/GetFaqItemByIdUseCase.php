<?php

declare(strict_types=1);

namespace Rore\Application\Faq\UseCase;

use Rore\Application\Faq\Port\FaqRepositoryInterface;
use Rore\Domain\Faq\Entity\FaqItem;

final class GetFaqItemByIdUseCase
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqRepository,
    ) {}

    public function execute(int $id): ?FaqItem
    {
        return $this->faqRepository->findById($id);
    }
}
