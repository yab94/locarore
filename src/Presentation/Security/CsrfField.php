<?php

declare(strict_types=1);

namespace Rore\Presentation\Security;

use Rore\Infrastructure\Security\CsrfTokenManager;
use Rore\Presentation\Template\Html;

/**
 * Rendu HTML du champ CSRF pour les formulaires.
 * Sépare la responsabilité de présentation de la gestion du token (Infrastructure).
 */
final class CsrfField
{
    public static function render(): string
    {
        return '<input type="hidden" name="_csrf" value="'
            . Html::e(CsrfTokenManager::token())
            . '">';
    }
}
