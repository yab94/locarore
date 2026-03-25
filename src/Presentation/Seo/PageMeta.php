<?php

declare(strict_types=1);

namespace Rore\Presentation\Seo;

use Rore\Support\Castable;

/**
 * Value Object immuable regroupant toutes les métadonnées SEO d'une page.
 */
final class PageMeta
{
    use Castable;
    public function __construct(
        public readonly string  $title,
        public readonly string  $description  = '',
        public readonly string  $keywords     = '',
        public readonly string  $robots       = 'index, follow',
        public readonly string  $canonicalUrl = '',
    ) {}
}
