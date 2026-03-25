<?php
/**
 * Partial pour générer le script LD+JSON avec breadcrumb
 * 
 * Variables attendues :
 * - $meta : PageMeta object
 * - $breadcrumb : array des éléments du breadcrumb  
 * - $allCategories : array de toutes les catégories (pour les URLs)
 * - $urlResolver : UrlResolver
 * - $item : (optionnel) Product ou Pack object - si fourni, génère aussi les données Product
 * - $type : (optionnel) 'product' ou 'pack' - requis si $item est fourni
 * - $mainPhoto : (optionnel) Photo object pour les packs
 */

$_meta = \Rore\Presentation\Seo\PageMeta::cast($tpl->get('meta'));
$_crumbs = array_values(\Rore\Support\Cast::array($tpl->get('breadcrumb')));
$_item = $tpl->get('item'); // Product ou Pack (optionnel)
$_type = $tpl->get('type', 'product'); // 'product' ou 'pack'
$_mainPhoto = $tpl->get('mainPhoto'); // Pour les packs

// Construction du breadcrumb LD+JSON
$_ldItems = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => $urlResolver->siteUrl() . '/']];
foreach ($_crumbs as $_i => $_crumb) {
    $_ldItems[] = [
        '@type'    => 'ListItem',
        'position' => $_i + 2,
        'name'     => $_crumb->getName(),
        'item'     => ($_i === count($_crumbs) - 1)
            ? $_meta->canonicalUrl
            : $urlResolver->siteUrl() . $urlResolver->categoryUrl($_crumb, $allCategories),
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
            $_ldProduct['image'] = $urlResolver->siteUrl() . $_mainPhoto->getPublicPath();
        }
    } else {
        // Pour product: utilise getMainPhoto() directement
        if ($_mainPhoto = $_item->getMainPhoto()) {
            $_ldProduct['image'] = $urlResolver->siteUrl() . $_mainPhoto->getPublicPath();
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