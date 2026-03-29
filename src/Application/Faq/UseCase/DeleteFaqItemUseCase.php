<?php

declare(strict_types=1);

namespace Rore\Application\Faq\UseCase;

use Rore\Application\Faq\Port\FaqRepositoryInterface;

final class DeleteFaqItemUseCase
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqRepository,
    ) {}

    public function execute(int $id): void
    {
        $this->faqRepository->delete($id);
    }
}
