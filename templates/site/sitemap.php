<?php
use Rore\Presentation\Seo\SlugResolver;
use RRB\Type\Cast;

$baseUrl    = Cast::string($tpl->get('baseUrl'));
$staticUrls = Cast::array($tpl->get('staticUrls'));
$categories = Cast::array($tpl->get('categories'));
$products   = Cast::array($tpl->get('products'));
$packs      = Cast::array($tpl->get('packs'));
$tags       = Cast::array($tpl->get('tags'));
$slug       = SlugResolver::cast($tpl->get('slug'));

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

    <?php foreach ($staticUrls as $entry): ?>
    <url>
        <loc><?= htmlspecialchars($entry['loc']) ?></loc>
        <?php if (isset($entry['lastmod'])): ?><lastmod><?= $entry['lastmod'] ?></lastmod><?php endif ?>
        <priority><?= $entry['priority'] ?></priority>
    </url>
    <?php endforeach ?>

    <?php foreach ($categories as $cat): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . $slug->categoryUrl($cat, $categories)) ?></loc>
        <lastmod><?= $cat->getUpdatedAt()->format('Y-m-d') ?></lastmod>
        <priority>0.8</priority>
    </url>
    <?php endforeach ?>

    <?php foreach ($products as $product): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . $slug->productUrl($product, $categories)) ?></loc>
        <lastmod><?= $product->getUpdatedAt()->format('Y-m-d') ?></lastmod>
        <priority>0.9</priority>
    </url>
    <?php endforeach ?>

    <?php foreach ($packs as $pack): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . $slug->packUrl($pack)) ?></loc>
        <lastmod><?= $pack->getUpdatedAt()->format('Y-m-d') ?></lastmod>
        <priority>0.7</priority>
    </url>
    <?php endforeach ?>

    <?php foreach ($tags as $tag): ?>
    <url>
        <loc><?= htmlspecialchars($baseUrl . $slug->tagUrl($tag)) ?></loc>
        <priority>0.6</priority>
    </url>
    <?php endforeach ?>

</urlset>
