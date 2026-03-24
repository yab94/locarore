<?php

declare(strict_types=1);

return '<input type="hidden" name="_csrf" value="'
    . htmlspecialchars((string) ($csrfToken ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
    . '">';
