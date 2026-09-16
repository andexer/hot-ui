<?php

declare(strict_types=1);

extract(props($__ctx, [
    'color' => null,
    'size' => 350,
]));

$glow = $color ?? 'color-mix(in oklab, var(--foreground) 14%, transparent)';

$radius = (int) $size;
?>
<div
    data-slot="spotlight-card"
    x-data="{
        hover: false,
        track(e) {
            const r = this.$el.getBoundingClientRect();
            this.$el.style.setProperty('--x', (e.clientX - r.left) + 'px');
            this.$el.style.setProperty('--y', (e.clientY - r.top) + 'px');
        },
    }"
    @mousemove="track($event)"
    @mouseenter="hover = true"
    @mouseleave="hover = false"
    <?= $attributes->twMerge('bg-card text-card-foreground relative overflow-hidden rounded-xl border p-6 shadow-sm') ?>
>
    <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-0 rounded-[inherit] transition-opacity duration-300 motion-reduce:transition-none"
        :style="{ opacity: hover ? 1 : 0 }"
        style="background: radial-gradient(circle <?= e((string) $radius) ?>px at var(--x, 50%) var(--y, 50%), <?= e((string) $glow) ?>, transparent 80%);"
    ></div>

    <div class="relative z-10">
        <?= $slot ?>
    </div>
</div>
