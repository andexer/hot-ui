<?php

declare(strict_types=1);

extract(props($__ctx, [
    'size' => 'lg',
]));

$maxW = [
    'sm' => 'max-w-3xl',
    'md' => 'max-w-5xl',
    'lg' => 'max-w-6xl',
    'xl' => 'max-w-7xl',
    'prose' => 'max-w-prose',
    'full' => 'max-w-full',
][$size] ?? 'max-w-6xl';
?>
<div data-slot="container" <?= $attributes->twMerge('mx-auto w-full px-4 sm:px-6 lg:px-8 '.$maxW) ?>>
    <?= $slot ?>
</div>
