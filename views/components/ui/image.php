<?php

declare(strict_types=1);

extract(props($__ctx, [
    'src' => null,
    'alt' => '',
    'ratio' => null,
    'placeholder' => null,
    'rounded' => 'rounded-lg',
    'fit' => 'cover',
]));

$object = $fit === 'contain' ? 'object-contain' : 'object-cover';

$srcUrl = safe_url(is_string($src) ? $src : null);
$placeholderUrl = safe_url(is_string($placeholder) ? $placeholder : null);
?>
<div
    data-slot="image"
    x-data="{ loaded: false, error: false }"
    <?php if ($ratio): ?>style="aspect-ratio: <?= e($ratio) ?>"
    <?php endif; ?>
    <?= $attributes->twMerge('relative block overflow-hidden '.$rounded) ?>
>
    <?php if ($placeholderUrl !== null): ?>
        <img
            src="<?= e($placeholderUrl) ?>"
            alt=""
            aria-hidden="true"
            x-show="!loaded && !error"
            class="absolute inset-0 size-full scale-110 blur-xl <?= e($object) ?>"
        />
    <?php else: ?>
        <div
            aria-hidden="true"
            x-show="!loaded && !error"
            class="bg-muted absolute inset-0 size-full animate-pulse"
        ></div>
    <?php endif; ?>

    <img
        src="<?= e($srcUrl) ?>"
        alt="<?= e($alt) ?>"
        loading="lazy"
        decoding="async"
        x-show="!error"
        x-on:load="loaded = true"
        x-on:error="error = true"
        x-bind:class="loaded ? 'opacity-100' : 'opacity-0'"
        class="relative size-full transition-opacity duration-500 ease-out <?= e($object) ?>"
    />

    <div
        x-show="error"
        x-cloak
        class="bg-muted text-muted-foreground absolute inset-0 flex flex-col items-center justify-center gap-2 p-4 text-center text-sm"
    >
        <i data-lucide="image-off" class="size-6 opacity-60" aria-hidden="true"></i>
        <?php if ($alt !== ''): ?>
            <span><?= e($alt) ?></span>
        <?php endif; ?>
    </div>
</div>
