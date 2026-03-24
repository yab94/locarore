<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $html($meta->title) ?></title>
    <meta name="robots" content="<?= $html($meta->robots) ?>">
    <?php if ($meta->description !== ''): ?>
    <meta name="description" content="<?= $html($meta->description) ?>">
    <?php endif; ?>
    <?php if ($meta->keywords !== ''): ?>
    <meta name="keywords" content="<?= $html($meta->keywords) ?>">
    <?php endif; ?>
    <?php if ($meta->canonicalUrl !== null): ?>
    <link rel="canonical" href="<?= $html($meta->canonicalUrl) ?>">
    <?php endif; ?>
    <!-- Polices Latyana Événements -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto+Slab:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CDN (remplacer par /assets/css/app.css compilé en production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#fff0f4',
                            100: '#ffd6e2',
                            600: '#ff0a52',
                            700: '#d40047',
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        serif: ['Roboto Slab', 'ui-serif', 'Georgia', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        h1, h2, h3, h4 { font-family: 'Roboto Slab', serif; }
        body { color: #3a3a3a; }
    </style>
</head>
<body class="h-full bg-gray-50 flex flex-col font-sans antialiased">

    <?php require BASE_PATH . '/templates/layout/header.php'; ?>

    <main class="flex-1 container mx-auto px-4 py-8 max-w-6xl">

        <?php if (!empty($flash)): ?>
            <?php foreach ($flash as $type => $msg): ?>
                <?php $cls = $type === 'error'
                    ? 'bg-red-100 border border-red-300 text-red-800'
                    : 'bg-green-100 border border-green-300 text-green-800'; ?>
                <div class="<?= $cls ?> rounded-lg px-4 py-3 mb-6 text-sm">
                    <?= $html($msg) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?= $content ?>

    </main>

    <?php require BASE_PATH . '/templates/layout/footer.php'; ?>

    <script src="/assets/js/app.js"></script>
</body>
</html>
