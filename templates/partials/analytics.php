<?php
/** @var \RRB\Bootstrap\Config $config */
$config = \RRB\Bootstrap\Config::cast($this->get('config'));

$gaId = $config->getString('analytics.ga_measurement_id', '');

if (!$gaId) return;
?>
<!-- CookieConsent v3 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.1.0/dist/cookieconsent.css">
<script defer src="https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.1.0/dist/cookieconsent.umd.js"></script>

<!-- Google Analytics (bloqué par défaut) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gaId, ENT_QUOTES) ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }

    gtag('consent', 'default', {
        analytics_storage: 'denied',
        ad_storage:        'denied',
        wait_for_update:   500,
    });

    gtag('js', new Date());
    gtag('config', '<?= htmlspecialchars($gaId, ENT_QUOTES) ?>');
</script>

<script>
    window.addEventListener('load', function () {
        CookieConsent.run({
            guiOptions: {
                consentModal: {
                    layout: 'bar inline',
                    position: 'bottom center',
                }
            },
            categories: {
                necessary: {
                    enabled: true,
                    readOnly: true,
                },
                analytics: {
                    autoClear: {
                        cookies: [
                            { name: /^(_ga|_gid)/ },
                        ]
                    },
                },
            },
            onConsent: function () {
                if (CookieConsent.acceptedCategory('analytics')) {
                    gtag('consent', 'update', { analytics_storage: 'granted' });
                }
            },
            onChange: function ({ changedCategories }) {
                if (changedCategories.includes('analytics')) {
                    gtag('consent', 'update', {
                        analytics_storage: CookieConsent.acceptedCategory('analytics')
                            ? 'granted'
                            : 'denied',
                    });
                }
            },
            language: {
                default: 'fr',
                translations: {
                    fr: {
                        consentModal: {
                            title: 'Nous utilisons des cookies',
                            description: 'Nous utilisons des cookies analytiques pour mesurer notre audience et améliorer notre site.',
                            acceptAllBtn: 'Tout accepter',
                            acceptNecessaryBtn: 'Tout refuser',
                            showPreferencesBtn: 'Gérer mes préférences',
                            footer: '<a href="/mentions-legales">Mentions légales</a>',
                        },
                        preferencesModal: {
                            title: 'Préférences de cookies',
                            acceptAllBtn: 'Tout accepter',
                            acceptNecessaryBtn: 'Tout refuser',
                            savePreferencesBtn: 'Enregistrer',
                            closeIconLabel: 'Fermer',
                            sections: [
                                {
                                    title: 'Cookies strictement nécessaires',
                                    description: 'Ces cookies sont indispensables au fonctionnement du site.',
                                    linkedCategory: 'necessary',
                                },
                                {
                                    title: 'Cookies analytiques',
                                    description: "Google Analytics nous aide à comprendre comment vous utilisez le site. Aucune donnée personnelle identifiable n'est collectée.",
                                    linkedCategory: 'analytics',
                                    cookieTable: {
                                        headers: { name: 'Cookie', duree: 'Durée', description: 'Description' },
                                        body: [
                                            { name: '_ga',  duree: '2 ans',     description: 'Identifiant de session GA4' },
                                            { name: '_gid', duree: '24 heures', description: 'Identifiant de visite' },
                                        ],
                                    },
                                },
                            ],
                        },
                    },
                },
            },
        });
    });
</script>
