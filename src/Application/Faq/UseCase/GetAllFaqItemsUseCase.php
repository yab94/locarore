<?php

declare(strict_types=1);

namespace Rore\Application\Faq\UseCase;

use Rore\Application\Faq\Port\FaqRepositoryInterface;

final class GetAllFaqItemsUseCase
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqRepository,
    ) {}

    /** @return \Rore\Domain\Faq\Entity\FaqItem[] */
    public function execute(): array
    {
        return $this->faqRepository->findAll();
    }
}
