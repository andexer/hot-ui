<?php

declare(strict_types=1);

/**
 * Admin layout — dense toolbar shell for management screens.
 *
 * Expected variables:
 *
 * @var string              $content Pre-rendered page body (HTML).
 * @var string|null         $title   Document title.
 * @var string|null         $meta    Pre-rendered head fragment.
 * @var string|null         $analytics Pre-rendered analytics fragment.
 * @var array<string,mixed> $user    Authenticated user summary (name, role).
 */
$content ??= '';
$title ??= 'Administración';
$meta ??= null;
$analytics ??= null;
$user ??= [];
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
<?= $meta ?? '' ?>
<link rel="stylesheet" href="/css/hot-ui.min.css">
</head>
<body class="bg-background text-foreground min-h-screen antialiased">
<div class="flex min-h-screen">
    <aside class="hidden w-56 shrink-0 border-e md:block" data-slot="admin-sidebar">
        <div class="p-4 text-sm font-semibold">Panel</div>
    </aside>
    <div class="flex-1">
        <header class="flex h-12 items-center justify-between border-b px-4">
            <span class="text-sm font-medium"><?= e($title) ?></span>
            <?php if (($user['name'] ?? null) !== null) { ?>
            <span class="text-muted-foreground text-xs"><?= e((string) $user['name']) ?> · <?= e((string) ($user['role'] ?? '')) ?></span>
            <?php } ?>
        </header>
        <main class="p-4"><?= $content ?></main>
    </div>
</div>
<script type="module" src="/js/app.js"></script>
<?= $analytics ?? '' ?>
</body>
</html>
