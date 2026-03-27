<?php $csrfToken = \RRB\Type\Cast::string($tpl->get('csrfToken')); ?><input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
