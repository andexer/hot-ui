<?php

declare(strict_types=1);

extract(props($__ctx, [
    'from' => null,
    'to' => null,
    'curvature' => 0,
    'duration' => 3,
]));

$beamDuration = (float) $duration.'s';
$curve = (float) $curvature;

static $stylesEmitted = false;
$emitStyles = ! $stylesEmitted;
$stylesEmitted = true;
?>
<?php if ($emitStyles): ?>
<style>
    /* Sweep a short dash from the start of the path to the end. The dash + gap pattern is
       sized off the measured path length (--hot-beam-len) so one dash crosses the whole
       line, then the offset animates it from -length back to 0. */
    @keyframes hot-animated-beam-sweep {
        from { stroke-dashoffset: var(--hot-beam-len, 0); }
        to   { stroke-dashoffset: 0; }
    }
    [data-slot="animated-beam"] .hot-animated-beam-flow {
        stroke-dasharray: var(--hot-beam-dash, 60) calc(var(--hot-beam-len, 1000) + var(--hot-beam-dash, 60));
        animation: hot-animated-beam-sweep var(--hot-beam-duration, 3s) linear infinite;
    }
    @media (prefers-reduced-motion: reduce) {
        [data-slot="animated-beam"] .hot-animated-beam-flow {
            animation: none;
            /* Hide the travelling dash entirely; the static resting path remains visible. */
            stroke-dasharray: 0 100000;
        }
    }
</style>
<?php endif; ?>
<div
    data-slot="animated-beam"
    x-id="['hot-beam-grad']"
    x-data="{
        from: <?= js($from) ?>,
        to: <?= js($to) ?>,
        curvature: <?= js($curve) ?>,
        d: '',
        w: 0,
        h: 0,
        len: 0,
        ready: false,
        measure() {
            const root = this.$root;
            const fromEl = this.from ? root.querySelector(this.from) : null;
            const toEl = this.to ? root.querySelector(this.to) : null;
            const box = root.getBoundingClientRect();
            this.w = box.width;
            this.h = box.height;
            if (!fromEl || !toEl || !box.width || !box.height) { this.ready = false; return; }
            const a = fromEl.getBoundingClientRect();
            const b = toEl.getBoundingClientRect();
            const ax = a.left - box.left + a.width / 2;
            const ay = a.top - box.top + a.height / 2;
            const bx = b.left - box.left + b.width / 2;
            const by = b.top - box.top + b.height / 2;
            
            const mx = (ax + bx) / 2;
            const my = (ay + by) / 2 - this.curvature;
            this.d = `M ${ax} ${ay} Q ${mx} ${my} ${bx} ${by}`;
            this.ready = true;
            
            this.$nextTick(() => {
                const p = this.$refs.flow;
                if (p && p.getTotalLength) {
                    try { this.len = p.getTotalLength(); } catch (e) { this.len = Math.hypot(bx - ax, by - ay); }
                } else {
                    this.len = Math.hypot(bx - ax, by - ay);
                }
            });
        },
        init() {
            this.$nextTick(() => this.measure());
            
            if (window.ResizeObserver) {
                this._ro = new ResizeObserver(() => this.measure());
                this._ro.observe(this.$root);
            }
        },
        destroy() { if (this._ro) this._ro.disconnect(); },
    }"
    @resize.window="measure()"
    <?= $attributes->twMerge('relative w-full') ?>
>
    <!-- Decorative connecting beam, drawn behind the slot content. -->
    <svg
        aria-hidden="true"
        focusable="false"
        class="pointer-events-none absolute inset-0 h-full w-full overflow-visible"
        x-show="ready"
        x-cloak
        :viewBox="`0 0 ${w} ${h}`"
        preserveAspectRatio="none"
        fill="none"
        :style="`--hot-beam-duration: <?= e($beamDuration) ?>; --hot-beam-len: ${len}; --hot-beam-dash: ${Math.max(24, len * 0.18)};`"
    >
        <defs>
            <!-- Soft beam paint for the travelling dash: a primary glow that fades at both ends. -->
            <linearGradient :id="$id('hot-beam-grad')" gradientUnits="userSpaceOnUse" x1="0" y1="0" :x2="w" y2="0">
                <stop offset="0%" stop-color="var(--color-primary)" stop-opacity="0" />
                <stop offset="50%" stop-color="var(--color-primary)" stop-opacity="1" />
                <stop offset="100%" stop-color="var(--color-primary)" stop-opacity="0" />
            </linearGradient>
        </defs>

        <!-- Resting path: a faint static line so the connection reads even before/without motion. -->
        <path
            :d="d"
            stroke="var(--color-border)"
            stroke-width="2"
            stroke-linecap="round"
            class="opacity-60"
        />

        <!-- Travelling light: a short gradient-painted dash swept along the path via dashoffset. -->
        <path
            x-ref="flow"
            :d="d"
            :stroke="`url(#${$id('hot-beam-grad')})`"
            stroke-width="2"
            stroke-linecap="round"
            class="hot-animated-beam-flow"
        />
    </svg>

    <!-- Slot content (the endpoints) sits above the beam. -->
    <div class="relative"><?= $slot ?></div>
</div>
