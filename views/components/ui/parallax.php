<?php

declare(strict_types=1);

extract(props($__ctx, [
    'speed' => 0.3,

    'axis' => 'y',
]));

$speed = (float) $speed;
$axis = $axis === 'x' ? 'x' : 'y';

$range = 80;
?>
<div
    data-slot="parallax"
    x-data="{
        speed: <?= js($speed) ?>,
        axis: <?= js($axis) ?>,
        range: <?= js($range) ?>,
        offset: 0,
        reduced: false,
        ticking: false,
        onScroll: null,
        onResize: null,
        init() {
            const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
            this.reduced = mq.matches;
            mq.addEventListener?.('change', (e) => {
                this.reduced = e.matches;
                if (this.reduced) this.offset = 0;
                else this.measure();
            });

            if (this.reduced || this.speed === 0) return;

            
            
            this.onScroll = () => {
                if (this.ticking) return;
                this.ticking = true;
                requestAnimationFrame(() => {
                    this.measure();
                    this.ticking = false;
                });
            };
            this.onResize = this.onScroll;
            window.addEventListener('scroll', this.onScroll, { passive: true });
            window.addEventListener('resize', this.onResize, { passive: true });
            this.measure();
        },
        measure() {
            if (this.reduced || this.speed === 0) { this.offset = 0; return; }
            const r = this.$el.getBoundingClientRect();
            const vh = window.innerHeight || document.documentElement.clientHeight;

            
            const center = r.top + r.height / 2;
            const denom = vh / 2 + r.height / 2;
            let progress = denom > 0 ? (center - vh / 2) / denom : 0;
            progress = Math.max(-1, Math.min(1, progress));
            this.offset = progress * this.speed * this.range;
        },
        get style() {
            if (this.reduced || this.speed === 0) return 'transform: none;';
            const v = this.offset.toFixed(2);
            return this.axis === 'x'
                ? `transform: translate3d(${v}px, 0, 0); will-change: transform;`
                : `transform: translate3d(0, ${v}px, 0); will-change: transform;`;
        },
        destroy() {
            if (this.onScroll) window.removeEventListener('scroll', this.onScroll);
            if (this.onResize) window.removeEventListener('resize', this.onResize);
        },
    }"
    <?= $attributes->twMerge('block') ?>
>
    <div :style="style" class="motion-reduce:!transform-none">
        <?= $slot ?>
    </div>
</div>
