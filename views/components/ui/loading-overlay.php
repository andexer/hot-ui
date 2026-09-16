<?php

declare(strict_types=1);

extract(props($__ctx, [
    'show' => false,
    'message' => null,
    'blur' => true,
]));

$label = $message ?? 'Loading…';
?>
<div
    data-slot="loading-overlay"
    x-data="{ show: <?= js((bool) $show) ?> }"
    :aria-busy="show ? 'true' : 'false'"
    <?= $attributes->twMerge('relative') ?>
>
    <?= $slot ?>

    <div
        x-show="show"
        x-cloak
        x-transition.opacity
        role="status"
        aria-live="polite"
        class="<?= classes([
            'bg-background/70 absolute inset-0 z-10 grid place-items-center',
            'backdrop-blur-sm' => $blur,
        ]) ?>"
    >
        <div class="text-muted-foreground flex flex-col items-center gap-2 text-sm">
            <svg class="size-6 animate-spin" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
            </svg>
            <span><?= e($label) ?></span>
        </div>
    </div>
</div>
