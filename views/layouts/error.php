<?php

declare(strict_types=1);

/**
 * Error layout — full-screen status page (404, 500, …).
 *
 * Expected variables:
 *
 * @var int         $code    HTTP status code shown as the big numeral.
 * @var string      $message Short human explanation.
 * @var string|null $backUrl Where the primary action returns to.
 * @var string|null $title   Document title.
 * @var string|null $meta    Pre-rendered head fragment.
 */
$code ??= 500;
$message ??= 'Algo salió mal.';
$backUrl ??= '/';
$title ??= "Error {$code}";
$meta ??= null;
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
<?= $meta ?? '' ?>
<link rel="stylesheet" href="/css/hot-ui.css">
<style>body { display: grid; place-items: center; min-height: 100vh; }</style>
</head>
<body class="bg-background text-foreground antialiased">
<main class="space-y-4 p-8 text-center">
    <p class="text-7xl font-bold tracking-tight"><?= e((string) $code) ?></p>
    <p class="text-muted-foreground"><?= e($message) ?></p>
    <a href="<?= e(safe_url($backUrl)) ?>" class="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90">Volver al inicio</a>
</main>
<script type="module" src="/js/app.js"></script>
</body>
</html>
