<?php

declare(strict_types=1);

namespace RRB\Http;

use Attribute;

/**
 * Déclare une route HTTP sur une méthode de controller.
 *
 * @example
 *   #[Route('GET', '/panier')]
 *   public function index(): void {}
 *
 *   #[Route('POST', '/panier/ajouter')]
 *   public function add(): void {}
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Route
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
    ) {}
}
