<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Entity;

use Rore\Framework\Castable;

class Tag
{
    use Castable;
    public function __construct(
        private ?int   $id,
        private string $name,
        private string $slug,
    ) {}

    public function getId(): ?int    { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
}
