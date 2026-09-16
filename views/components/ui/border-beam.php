<?php

declare(strict_types=1);

extract(props($__ctx, [
    'duration' => 6,
    'color' => null,
    'size' => 2,
]));

$beam = $color ?: 'var(--color-primary)';
$thickness = (int) $size.'px';
$dur = (float) $duration.'s';

static $stylesEmitted = false;
$emitStyles = ! $stylesEmitted;
$stylesEmitted = true;
?>
<?php if ($emitStyles): ?>
<style>
    @keyframes hot-border-beam-spin {
        to { transform: rotate(1turn); }
    }
    [data-slot="border-beam"] .hot-border-beam-ring::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: var(--hot-beam-size, 2px);
        background: conic-gradient(
            from 0deg,
            transparent 0deg,
            transparent 300deg,
            var(--hot-beam-color, currentColor) 345deg,
            #fff 360deg
        );
        -webkit-mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask:
            linear-gradient(#000 0 0) content-box,
            linear-gradient(#000 0 0);
        mask-composite: exclude;
        animation: hot-border-beam-spin var(--hot-beam-duration, 6s) linear infinite;
    }
    @media (prefers-reduced-motion: reduce) {
        [data-slot="border-beam"] .hot-border-beam-ring::before {
            animation: none;
        }
    }
</style>
<?php endif; ?>
<div
    data-slot="border-beam"
    <?= $attributes->twMerge('bg-card text-card-foreground relative overflow-hidden rounded-xl border p-6 shadow-sm') ?>
>
    <span
        class="hot-border-beam-ring pointer-events-none absolute inset-0 rounded-[inherit]"
        style="--hot-beam-size: <?= e($thickness) ?>; --hot-beam-color: <?= e($beam) ?>; --hot-beam-duration: <?= e($dur) ?>;"
        aria-hidden="true"
    ></span>

    <div class="relative"><?= $slot ?></div>
</div>
