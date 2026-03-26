<?php
/** @var \Rore\Framework\Config $config */
$config = \Rore\Framework\Config::cast($this->get('config'));

$gaId       = $config->getString('analytics.ga_measurement_id', '');
$axeptioId  = $config->getString('analytics.axeptio_client_id', '');
$axeptioVer = $config->getString('analytics.axeptio_version', '');

if (!$gaId && !$axeptioId) return;
?>
<?php if ($axeptioId): ?>
<!-- Axeptio -->
<script>
    window.axeptioSettings = {
        clientId: "<?= htmlspecialchars($axeptioId, ENT_QUOTES) ?>",
        <?php if ($axeptioVer): ?>
        cookiesVersion: "<?= htmlspecialchars($axeptioVer, ENT_QUOTES) ?>",
        <?php endif ?>
    };
    (function(d, s) {
        var t = d.getElementsByTagName(s)[0], e = d.createElement(s);
        e.async = true;
        e.src = "//static.axept.io/sdk.js";
        t.parentNode.insertBefore(e, t);
    })(document, "script");
</script>
<?php endif ?>

<?php if ($gaId): ?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($gaId, ENT_QUOTES) ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }

    // Consentement refusé par défaut
    gtag('consent', 'default', {
        analytics_storage: 'denied',
        ad_storage:        'denied',
    });

    gtag('js', new Date());
    gtag('config', '<?= htmlspecialchars($gaId, ENT_QUOTES) ?>');

    <?php if ($axeptioId): ?>
    // Mise à jour du consentement quand l'utilisateur choisit via Axeptio
    void 0 === window._axcb && (window._axcb = []);
    window._axcb.push(function(axeptio) {
        axeptio.on('cookies:complete', function(choices) {
            gtag('consent', 'update', {
                analytics_storage: choices.google_analytics ? 'granted' : 'denied',
            });
        });
    });
    <?php endif ?>
</script>
<?php endif ?>
