<?php

declare(strict_types=1);

namespace Rore\Domain\Catalog\Entity;

class ProductPhoto
{
    public function __construct(
        private ?int               $id,
        private int                $productId,
        private string             $filename,
        private int                $sortOrder,
        private \DateTimeImmutable $createdAt,
        private ?string            $description = null,
    ) {}

    public function getId(): ?int                       { return $this->id; }
    public function getProductId(): int                 { return $this->productId; }
    public function getFilename(): string               { return $this->filename; }
    public function getSortOrder(): int                 { return $this->sortOrder; }
    public function getCreatedAt(): \DateTimeImmutable  { return $this->createdAt; }
    public function getDescription(): ?string           { return $this->description; }

    public function setSortOrder(int $order): void     { $this->sortOrder = $order; }
    public function setDescription(?string $d): void   { $this->description = $d; }

    /**
     * Retourne le chemin public relatif vers la photo.
     */
    public function getPublicPath(): string
    {
        return '/uploads/products/' . $this->filename;
    }
}
