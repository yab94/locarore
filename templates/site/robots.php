<?php
use RRB\Type\Cast;

$siteUrl = Cast::string($tpl->get('siteUrl'));
?>
User-agent: *
Disallow: /admin/
Disallow: /panier/

Sitemap: <?= $siteUrl ?>/sitemap.xml
