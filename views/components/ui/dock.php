<?php

declare(strict_types=1);

extract(props($__ctx, [
    'magnify' => 1.6,
    'distance' => 120,
]));

$peak = (float) $magnify;
$falloff = (int) $distance;
?>
<nav
    data-slot="dock"
    aria-label="Dock"
    x-data="{
        mouseX: null,
        peak: <?= js($peak) ?>,
        falloff: <?= js($falloff) ?>,
        reduced: false,
        init() {
            const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
            this.reduced = mq.matches;
            mq.addEventListener?.('change', e => { this.reduced = e.matches; });
        },

        scaleFor(centerX) {
            if (this.reduced || this.mouseX === null) return 1;
            const d = Math.abs(this.mouseX - centerX);
            if (d >= this.falloff) return 1;
            
            const t = (Math.cos((d / this.falloff) * Math.PI) + 1) / 2;
            return 1 + (this.peak - 1) * t;
        },
    }"
    @mousemove="mouseX = $event.clientX"
    @mouseleave="mouseX = null"
    <?= $attributes->twMerge('bg-card/80 supports-[backdrop-filter]:bg-card/60 mx-auto flex w-fit items-end gap-2 rounded-2xl border px-3 py-2 shadow-lg backdrop-blur') ?>
>
    <?= $slot ?>
</nav>
