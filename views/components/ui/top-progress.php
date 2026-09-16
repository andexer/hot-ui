<?php

declare(strict_types=1);

extract(props($__ctx, [
    'color' => null,
    'height' => 2,
    'demo' => false,
]));

$barColor = $color ?: 'var(--color-primary)';
$barHeight = (int) $height.'px';

static $styleEmitted = false;
$emitStyle = ! $styleEmitted;
$styleEmitted = true;
?>
<?php if ($emitStyle): ?>
    <style>
        @media (prefers-reduced-motion: reduce) {
            [data-slot="top-progress"] .hot-top-progress-bar {
                transition: none !important;
            }
        }
    </style>
<?php endif; ?>

<div
    data-slot="top-progress"
    x-data="{
        progress: 0,
        active: false,
        visible: false,
        timer: null,
        clamp(v) { return Math.max(0, Math.min(1, v)); },
        start() {
            if (this.active) return;
            this.active = true;
            this.visible = true;
            this.progress = 0.08;
            this.trickle();
            this.timer = setInterval(() => this.trickle(), 400);
        },
        trickle() {
            if (!this.active) return;
            if (this.progress >= 0.9) return;
            
            const remaining = 0.9 - this.progress;
            this.set(this.clamp(this.progress + remaining * (0.1 + Math.random() * 0.15)));
        },
        set(p) {
            this.progress = this.clamp(p);
            this.visible = true;
        },
        done() {
            if (this.timer) { clearInterval(this.timer); this.timer = null; }
            this.active = false;
            this.set(1);
            setTimeout(() => {
                this.visible = false;
                setTimeout(() => { if (!this.active) this.progress = 0; }, 300);
            }, 250);
        },
    }"
    x-on:top-progress:start.window="start()"
    x-on:top-progress:set.window="set($event.detail?.value ?? 0)"
    x-on:top-progress:done.window="done()"
    <?php if (! $demo): ?>x-cloak<?php endif; ?>
    <?= $attributes->twMerge($demo ? 'relative block w-full overflow-hidden' : 'pointer-events-none fixed inset-x-0 top-0 z-[60] overflow-hidden') ?>
    style="height: <?= e($barHeight) ?>;"
>
    <div
        class="hot-top-progress-bar absolute inset-y-0 start-0 origin-left rounded-e-full transition-[width,opacity] duration-200 ease-out"
        role="progressbar"
        aria-label="Page loading"
        aria-valuemin="0"
        aria-valuemax="100"
        x-bind:aria-valuenow="active ? Math.round(progress * 100) : null"
        x-bind:style="`width: ${progress * 100}%; opacity: ${visible ? 1 : 0}; background: <?= e($barColor) ?>;`"
    >
        <span
            aria-hidden="true"
            class="pointer-events-none absolute inset-y-0 end-0 w-16 blur-[2px]"
            style="background: linear-gradient(90deg, transparent, <?= e($barColor) ?>); box-shadow: 0 0 10px <?= e($barColor) ?>, 0 0 5px <?= e($barColor) ?>;"
        ></span>
    </div>
</div>
