<?php $tpl->assertString('csrfToken'); ?><input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
