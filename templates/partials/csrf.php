<?php

declare(strict_types=1);

use Rore\Presentation\Template\Html;

return '<input type="hidden" name="_csrf" value="'
    . Html::e((string) ($csrfToken ?? ''))
    . '">';
