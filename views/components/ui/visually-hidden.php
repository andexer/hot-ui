<?php

declare(strict_types=1);

extract(props($__ctx, [
    'as' => 'span',
    'focusable' => false,
]));

$base = 'sr-only';

if ($focusable) {
    $base .= ' focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-primary focus:px-4 focus:py-2 focus:text-primary-foreground focus:shadow-md';
}
?>
<<?= $as ?>
    data-slot="visually-hidden"
    <?= $attributes->twMerge($base) ?>
><?= $slot ?></<?= $as ?>>
