<?php
use RRB\Bootstrap\Config;
use Rore\Presentation\Seo\SlugResolver;

$_config  = Config::cast($tpl->get('config'));
$_slug    = SlugResolver::cast($tpl->get('slug'));
$_meta    = \RRB\View\PageMeta::cast($tpl->get('meta'));

$_siteUrl = $_slug->siteUrl();
$_name    = $_config->getString('app.name');
$_desc    = $_config->getString('app.description');

$_schema = [
    '@context' => 'https://schema.org',
    '@graph'   => [
        [
            '@type'       => 'LocalBusiness',
            '@id'         => $_siteUrl . '/#business',
            'name'        => $_name,
            'url'         => $_siteUrl,
            'description' => $_desc,
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
