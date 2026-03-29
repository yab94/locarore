<?php

declare(strict_types=1);

namespace Rore\Application\Faq\UseCase;

use Rore\Application\Faq\Port\FaqRepositoryInterface;

final class ToggleFaqItemUseCase
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqRepository,
    ) {}

    public function execute(int $id): void
    {
        $item = $this->faqRepository->findById($id);
        if ($item === null) {
            throw new \RuntimeException("FAQ item introuvable ($id).");
        }

        $item->toggle();
        $item->setUpdatedAt(new \DateTimeImmutable());

        $this->faqRepository->save($item);
    }
}
