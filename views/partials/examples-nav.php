<?php

declare(strict_types=1);

/**
 * Navigation card linking the example pages from the showcase.
 */
?>
<div class="mx-auto mt-10 max-w-3xl">
    <?= ui()->card(['variant' => 'sectioned'], function () { ?>
        <?= ui()->cardHeader([], function () { ?>
            <?= ui()->cardTitle([], 'Ejemplos completos') ?>
            <?= ui()->cardDescription([], 'Páginas reales construidas con layouts, partials y componentes.') ?>
        <?php }) ?>
        <?= ui()->cardContent(['class' => 'grid gap-3 sm:grid-cols-3'], function () { ?>
            <?= ui()->button(['as' => 'a', 'href' => '/examples/login', 'variant' => 'outline'], function () { ?>
                Login
            <?php }) ?>
            <?= ui()->button(['as' => 'a', 'href' => '/examples/dashboard', 'variant' => 'outline'], 'Dashboard') ?>
            <?= ui()->button(['as' => 'a', 'href' => '/examples/blog', 'variant' => 'outline'], 'Blog') ?>
        <?php }) ?>
    <?php }) ?>
</div>
