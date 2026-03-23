<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $meta ??= new \Rore\Presentation\Seo\PageMeta(title: $title ?? 'Locarore'); ?>
    <title><?= e($meta->title) ?></title>
    <meta name="robots" content="<?= e($meta->robots) ?>">
    <?php if ($meta->description !== ''): ?>
    <meta name="description" content="<?= e($meta->description) ?>">
    <?php endif; ?>
    <?php if ($meta->keywords !== ''): ?>
    <meta name="keywords" content="<?= e($meta->keywords) ?>">
    <?php endif; ?>
    <?php if ($meta->canonicalUrl !== null): ?>
    <link rel="canonical" href="<?= e($meta->canonicalUrl) ?>">
    <?php endif; ?>
    <!-- Tailwind CDN (remplacer par /assets/css/app.css compilé en production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full bg-gray-50 flex flex-col">

    <?php require BASE_PATH . '/templates/layout/header.php'; ?>

    <main class="flex-1 container mx-auto px-4 py-8 max-w-6xl">

        <?php if (!empty($flash)): ?>
            <?php foreach ($flash as $type => $msg): ?>
                <?php $cls = $type === 'error'
                    ? 'bg-red-100 border border-red-300 text-red-800'
                    : 'bg-green-100 border border-green-300 text-green-800'; ?>
                <div class="<?= $cls ?> rounded-lg px-4 py-3 mb-6 text-sm">
                    <?= e($msg) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?= $content ?>

    </main>

    <?php require BASE_PATH . '/templates/layout/footer.php'; ?>

    <script src="/assets/js/app.js"></script>
</body>
</html>
