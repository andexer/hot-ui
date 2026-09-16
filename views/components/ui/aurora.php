<?php

declare(strict_types=1);

extract(props($__ctx, [
    'colors' => null,
    'blur' => 60,
    'speed' => 12,
]));

$defaultColors = ['#22d3ee', '#6366f1', '#a855f7', '#34d399', '#ec4899'];

$palette = is_array($colors) && count($colors) > 0
    ? array_values(array_filter($colors, fn ($c) => is_string($c) && $c !== ''))
    : $defaultColors;

if (count($palette) === 0) {
    $palette = $defaultColors;
}

$spots = [
    '20% 25%',
    '80% 20%',
    '65% 75%',
    '30% 80%',
    '50% 50%',
];

$blobs = [];
foreach ($spots as $i => $pos) {
    $color = $palette[$i % count($palette)];

    $blobs[] = "radial-gradient(40% 40% at {$pos}, {$color} 0%, transparent 70%)";
}
$blobBg = implode(', ', $blobs);

$blurPx = (float) $blur.'px';
$speedS = (float) $speed.'s';

static $stylesEmitted = false;
$emitStyles = ! $stylesEmitted;
$stylesEmitted = true;
?>
<?php if ($emitStyles): ?>
<style>
    @keyframes hot-aurora-drift {
        0%   { transform: translate3d(-6%, -4%, 0) rotate(0deg) scale(1.15); }
        33%  { transform: translate3d(6%, 3%, 0) rotate(40deg) scale(1.3); }
        66%  { transform: translate3d(-4%, 5%, 0) rotate(-30deg) scale(1.2); }
        100% { transform: translate3d(-6%, -4%, 0) rotate(0deg) scale(1.15); }
    }
    @media (prefers-reduced-motion: reduce) {
        [data-slot="aurora"] .hot-aurora-layer {
            animation: none !important;
        }
    }
</style>
<?php endif; ?>
<div
    data-slot="aurora"
    <?= $attributes->twMerge('relative isolate overflow-hidden rounded-xl bg-slate-950 text-white') ?>
>
    <!-- Decorative aurora layer: oversized so the rotation never reveals empty corners. -->
    <div
        class="hot-aurora-layer pointer-events-none absolute -inset-[40%] -z-10"
        style="background: <?= e($blobBg) ?>; filter: blur(<?= e($blurPx) ?>) saturate(1.4); animation: hot-aurora-drift <?= e($speedS) ?> ease-in-out infinite;"
        aria-hidden="true"
    ></div>

    <!-- Faint scrim so overlaid light text stays legible (AA) on the default palette. -->
    <div class="pointer-events-none absolute inset-0 -z-10 bg-slate-950/30" aria-hidden="true"></div>

    <!-- Slot content sits above the aurora. -->
    <div class="relative z-0 p-8"><?= $slot ?></div>
</div>
