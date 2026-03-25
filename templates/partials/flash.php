<?php
$html  = Rore\Presentation\Template\HtmlHelper::cast($tpl->get('html'));
$flash = \Rore\Framework\Cast::array($tpl->tryGet('flash', []));
?>
<?php if (!empty($flash)): ?>
    <?php foreach ($flash as $type => $msg): ?>
        <?php $cls = $type === 'error'
            ? 'bg-red-100 border border-red-300 text-red-800'
            : 'bg-green-100 border border-green-300 text-green-800'; ?>
        <div class="<?= $cls ?> rounded-lg px-4 py-3 mb-6 text-sm">
            <?= $html($msg) ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
