<?php

declare(strict_types=1);

namespace Rore\Domain\Faq\Entity;

use RRB\Type\Castable;

class FaqItem
{
    use Castable;

    public function __construct(
        private ?int                $id,
        private string              $question,
        private string              $answer,
        private int                 $position,
        private bool                $isVisible,
        private \DateTimeImmutable  $createdAt,
        private \DateTimeImmutable  $updatedAt,
    ) {}

    public function getId(): ?int                      { return $this->id; }
    public function getQuestion(): string              { return $this->question; }
    public function getAnswer(): string                { return $this->answer; }
    public function getPosition(): int                 { return $this->position; }
    public function isVisible(): bool                  { return $this->isVisible; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function setQuestion(string $question): void { $this->question = $question; }
    public function setAnswer(string $answer): void     { $this->answer = $answer; }
    public function setPosition(int $position): void    { $this->position = $position; }
    public function setIsVisible(bool $v): void         { $this->isVisible = $v; }
    public function toggle(): void                      { $this->isVisible = !$this->isVisible; }
    public function setUpdatedAt(\DateTimeImmutable $dt): void { $this->updatedAt = $dt; }
}
