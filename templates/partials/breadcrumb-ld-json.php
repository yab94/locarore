<?php

$slug = \Rore\Presentation\Seo\SlugResolver::cast($tpl->get('slug'));
$_meta = \Rore\Framework\PageMeta::cast($tpl->get('meta'));
$_crumbs = array_values(\Rore\Framework\Cast::array($tpl->get('breadcrumb')));
$_item = $tpl->tryGet('item'); // Product ou Pack (optionnel)
$_type = $tpl->tryGet('type', 'product'); // 'product' ou 'pack'
$_mainPhoto = $tpl->tryGet('mainPhoto'); // Pour les packs

// Construction du breadcrumb LD+JSON
$_ldItems = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => $slug->siteUrl() . '/']];
foreach ($_crumbs as $_i => $_crumb) {
    $_ldItems[] = [
        '@type'    => 'ListItem',
        'position' => $_i + 2,
        'name'     => $_crumb->getName(),
        'item'     => ($_i === count($_crumbs) - 1)
            ? $_meta->canonicalUrl
            : $slug->siteUrl() . $slug->categoryUrl($_crumb, $allCategories),
    ];
}

$_breadcrumbLD = ['@type' => 'BreadcrumbList', 'itemListElement' => $_ldItems];

// Si un item (produit/pack) est fourni, on génère aussi ses données LD+JSON
if ($_item) {
    $_ldProduct = [
        '@type'  => 'Product',
        'name'   => $_item->getName(),
        'offers' => [
            '@type'         => 'Offer',
            'priceCurrency' => 'EUR',
            'price'         => (string) ($_type === 'pack' ? $_item->getPricePerDay() : $_item->getPriceBase()),
            'availability'  => 'https://schema.org/InStock',
        ],
    ];

    // Description (optionnelle)
    if ($_item->getDescription()) {
        $_ldProduct['description'] = strip_tags($_item->getDescription());
    }

    // Image (selon le type)
    if ($_type === 'pack') {
        // Pour pack: utilise la photo fournie en paramètre
        if ($_mainPhoto) {
            $_ldProduct['image'] = $slug->siteUrl() . $_mainPhoto->getPublicPath();
        }
    } else {
        // Pour product: utilise getMainPhoto() directement
        if ($_mainPhoto = $_item->getMainPhoto()) {
            $_ldProduct['image'] = $slug->siteUrl() . $_mainPhoto->getPublicPath();
        }
    }

    // URL canonique
    if ($_meta->canonicalUrl !== '') {
        $_ldProduct['url'] = $_meta->canonicalUrl;
        $_ldProduct['offers']['url'] = $_meta->canonicalUrl;
    }

    // Structure @graph avec produit + breadcrumb
    $_ldSchema = [
        '@context' => 'https://schema.org',
        '@graph'   => [$_ldProduct, $_breadcrumbLD],
    ];
} else {
    // Mode simple : juste le breadcrumb
    $_ldSchema = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList', 
        'itemListElement' => $_ldItems,
    ];
}

echo '<script type="application/ld+json">' . json_encode($_ldSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';