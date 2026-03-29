<?php
use RRB\Bootstrap\Config;
use Rore\Presentation\Seo\SlugResolver;

$_config  = Config::cast($tpl->get('config'));
$_slug    = SlugResolver::cast($tpl->get('slug'));
$_meta    = \RRB\View\PageMeta::cast($tpl->get('meta'));

$_siteUrl   = $_slug->siteUrl();
$_name      = $_config->getString('app.name');
$_desc      = $_config->getString('app.description');
$_city       = $_config->getString('seo.city');
$_postal     = $_config->getString('seo.postal_code');
$_nearbyCity = $_config->getString('seo.nearby_city');
$_region     = $_config->getString('seo.region');
$_regionFull = $_config->getString('seo.region_full');

$_schema = [
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'       => 'LocalBusiness',
            '@id'         => $_siteUrl . '/#business',
            'name'        => $_name,
            'url'         => $_siteUrl,
            'description' => $_desc,
            'address'     => [
                '@type'           => 'PostalAddress',
                'addressLocality' => $_city,
                'postalCode'      => $_postal,
                'addressRegion'   => $_region,
                'addressCountry'  => 'FR',
            ],
            'areaServed'  => [
                ['@type' => 'City',                 'name' => $_city],
                ['@type' => 'City',                 'name' => $_nearbyCity],
                ['@type' => 'AdministrativeArea',   'name' => $_region],
                ['@type' => 'AdministrativeArea',   'name' => $_regionFull],
            ],
        ],
        [
            '@type'           => 'WebSite',
            '@id'             => $_siteUrl . '/#website',
            'url'             => $_siteUrl,
            'name'            => $_name,
            'publisher'       => ['@id' => $_siteUrl . '/#business'],
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => [
                    '@type'       => 'EntryPoint',
                    'urlTemplate' => $_siteUrl . '/recherche?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ],
];

echo '<script type="application/ld+json">' . json_encode($_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . PHP_EOL;
