<?php

declare(strict_types=1);

namespace Rore\Application\Faq\UseCase;

use Rore\Application\Faq\Port\FaqRepositoryInterface;

final class UpdateFaqItemUseCase
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqRepository,
    ) {}

    public function execute(
        int    $id,
        string $question,
        string $answer,
        int    $position,
        bool   $isVisible,
    ): void {
        $item = $this->faqRepository->findById($id);
        if ($item === null) {
            throw new \RuntimeException("FAQ item introuvable ($id).");
        }

        $item->setQuestion($question);
        $item->setAnswer($answer);
        $item->setPosition($position);
        $item->setIsVisible($isVisible);
        $item->setUpdatedAt(new \DateTimeImmutable());

        $this->faqRepository->save($item);
    }
}
