<?php

declare(strict_types=1);

/**
 * Master application layout (authenticated shell with sidebar).
 *
 * Expected variables:
 *
 * @var string        $content  Pre-rendered page body (HTML).
 * @var string|null   $title    Document title.
 * @var string|null   $meta     Pre-rendered head fragment (partials/meta).
 * @var string|null   $analytics Pre-rendered analytics fragment.
 * @var array<string,string> $nav Main navigation items: label => href.
 */
$content ??= '';
$title ??= 'Hot-UI';
$meta ??= null;
$analytics ??= null;
$nav ??= [];
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
<?= $meta ?? '' ?>
<link rel="stylesheet" href="/css/hot-ui.min.css">
</head>
<body class="bg-background text-foreground min-h-screen antialiased">
<header class="border-b" data-slot="app-header">
    <div class="mx-auto flex h-14 max-w-6xl items-center gap-6 px-4">
        <a href="/" class="font-semibold">Hot<span class="text-primary">UI</span></a>
        <nav class="flex items-center gap-4 text-sm">
            <?php foreach ($nav as $label => $href) { ?>
            <a href="<?= e(safe_url($href)) ?>" class="text-muted-foreground hover:text-foreground transition-colors"><?= e((string) $label) ?></a>
            <?php } ?>
        </nav>
    </div>
</header>
<main class="mx-auto max-w-6xl px-4 py-8"><?= $content ?></main>
<footer class="border-t py-6 text-center text-muted-foreground text-xs">Hecho con Hot-UI</footer>
<script type="module" src="/js/app.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script>if (window.lucide) lucide.createIcons();</script>
<?= $analytics ?? '' ?>
</body>
</html>
