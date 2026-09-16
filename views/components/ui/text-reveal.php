<?php

declare(strict_types=1);

extract(props($__ctx, [
    'as' => 'p',
]));

$text = trim((string) preg_replace('/\s+/', ' ', strip_tags((string) $slot)));
$words = $text === '' ? [] : explode(' ', $text);
$tag = preg_replace('/[^a-z0-9]/i', '', (string) $as) ?: 'p';
?>
<<?= $tag ?>
    data-slot="text-reveal"
    x-data="hotTextReveal({ total: <?= count($words) ?> })"
    <?= $attributes->twMerge('leading-snug') ?>><?php foreach ($words as $w): ?><span data-w class="transition-opacity duration-300 ease-out"><?= e($w) ?></span> <?php endforeach; ?></<?= $tag ?>>
