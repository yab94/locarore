<?php

declare(strict_types=1);

namespace Rore\Application\Faq\UseCase;

use Rore\Application\Faq\Port\FaqRepositoryInterface;
use Rore\Domain\Faq\Entity\FaqItem;

final class CreateFaqItemUseCase
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqRepository,
    ) {}

    public function execute(
        string $question,
        string $answer,
        int    $position  = 0,
        bool   $isVisible = true,
    ): void {
        $now  = new \DateTimeImmutable();
        $item = new FaqItem(
            id:        null,
            question:  $question,
            answer:    $answer,
            position:  $position,
            isVisible: $isVisible,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->faqRepository->save($item);
    }
}
