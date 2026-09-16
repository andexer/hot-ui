<?php

declare(strict_types=1);

extract(props($__ctx, [
    'count' => 20,
    'color' => null,
]));

static $stylesEmitted = false;
$emitStyles = ! $stylesEmitted;
$stylesEmitted = true;

$n = max(1, min(100, (int) $count));

$head = ($color !== null && $color !== '')
    ? $color
    : 'color-mix(in oklab, var(--foreground) 40%, transparent)';

$trail = "linear-gradient(90deg, {$head}, transparent)";

$meteors = [];
for ($i = 0; $i < $n; $i++) {

    $left = -20 + ($i * (140 / $n)) + (($i * 37) % 23);
    $delay = round((($i * 53) % 100) / 100 * 8, 2);
    $duration = round(4 + (($i * 29) % 60) / 10, 2);
    $size = 1 + (($i * 17) % 2);
    $meteors[] = compact('left', 'delay', 'duration', 'size');
}
?>
<?php if ($emitStyles): ?>
<style>
    @keyframes hot-meteors-fall {
        0% {
            transform: translate3d(0, 0, 0) rotate(var(--hot-meteors-angle, 215deg));
            opacity: 1;
        }
        70% {
            opacity: 1;
        }
        100% {
            /* Travel far enough to always clear the container on the diagonal. */
            transform: translate3d(-120vw, 120vh, 0) rotate(var(--hot-meteors-angle, 215deg));
            opacity: 0;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        [data-slot="meteors"] .hot-meteors-layer {
            display: none !important;
        }
    }
</style>
<?php endif; ?>
<div
    data-slot="meteors"
    <?= $attributes->twMerge('relative isolate overflow-hidden') ?>
>
    <div class="hot-meteors-layer pointer-events-none absolute inset-0 -z-10" aria-hidden="true" dir="ltr">
        <?php foreach ($meteors as $m): ?>
            <span
                class="absolute top-0 rounded-full"
                style="
                    left: <?= e($m['left']) ?>%;
                    width: <?= e($m['size']) ?>px;
                    height: <?= e($m['size']) ?>px;
                    background: <?= e($head) ?>;
                    box-shadow: 0 0 0 1px <?= e($head) ?>;
                    animation: hot-meteors-fall <?= e($m['duration']) ?>s linear <?= e($m['delay']) ?>s infinite;
                "
            >
                <span
                    class="absolute top-1/2 -translate-y-1/2 rounded-full"
                    style="
                        right: 0;
                        width: 3rem;
                        height: 1px;
                        background: <?= e($trail) ?>;
                    "
                ></span>
            </span>
        <?php endforeach; ?>
    </div>

    <div class="relative z-0"><?= $slot ?></div>
</div>
