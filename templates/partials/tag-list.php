<?php
use RRB\View\HtmlEncoder;
use RRB\Bootstrap\Config;
use RRB\Type\Cast;

$html   = HtmlEncoder::cast($tpl->get('html'));
$config = Config::cast($tpl->get('config'));
$tags   = Cast::array($tpl->get('tags'));
?>
<?php if (!empty($tags)): ?>
    <div class="flex flex-wrap gap-2 mb-6">
        <?php foreach ($tags as $tag): ?>
            <a href="<?= $config->getString('seo.tags_base_url') ?>/<?= $html($tag->getSlug()) ?>"
               class="inline-flex items-center gap-1 bg-gray-100 text-gray-600 rounded-full px-3 py-1 text-xs font-medium hover:bg-brand-50 hover:text-brand-700 transition">
                🏷️ <?= $html($tag->getName()) ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
