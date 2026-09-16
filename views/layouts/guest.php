<?php

declare(strict_types=1);

/**
 * Guest layout — centered card shell for login/registro screens.
 *
 * Expected variables:
 *
 * @var string      $content Pre-rendered page body (HTML).
 * @var string|null $title   Document title.
 * @var string|null $meta    Pre-rendered head fragment.
 * @var string|null $analytics Pre-rendered analytics fragment.
 */
$content ??= '';
$title ??= 'Acceso';
$meta ??= null;
$analytics ??= null;
?>
<!DOCTYPE html>
<html lang="es" class="dark">
<head>
<?= $meta ?? '' ?>
<link rel="stylesheet" href="/css/hot-ui.min.css">
<style>body { display: grid; place-items: center; min-height: 100vh; }</style>
</head>
<body class="bg-background text-foreground antialiased p-6">
<main class="w-full max-w-sm"><?= $content ?></main>
<script type="module" src="/js/app.js"></script>
<?= $analytics ?? '' ?>
</body>
</html>
