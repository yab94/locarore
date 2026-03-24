<?php

declare(strict_types=1);

return '<input type="hidden" name="_csrf" value="'
    . ($html ?? new \Rore\Presentation\Template\Html())((string) ($csrfToken ?? ''))
    . '">';
