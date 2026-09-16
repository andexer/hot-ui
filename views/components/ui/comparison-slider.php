<?php

declare(strict_types=1);

extract(props($__ctx, [
    'before' => null,
    'after' => null,
    'beforeLabel' => null,
    'afterLabel' => null,
    'beforeAlt' => '',
    'afterAlt' => '',
    'value' => 50,
]));

$beforeSrc = safe_url(is_string($before) ? $before : null);
$afterSrc = safe_url(is_string($after) ? $after : null);
?>
<div
    data-slot="comparison-slider"
    x-data="{
        pos: <?= js(max(0, min(100, (float) $value))) ?>,
        dragging: false,
        setFromPointer(e) {
            const r = this.$refs.frame.getBoundingClientRect();
            if (!r.width) return;
            let ratio = (e.clientX - r.left) / r.width;
            if (getComputedStyle(this.$refs.frame).direction === 'rtl') ratio = 1 - ratio;
            this.pos = Math.max(0, Math.min(100, ratio * 100));
        },
        start(e) { this.dragging = true; this.setFromPointer(e); },
        move(e) { if (this.dragging) this.setFromPointer(e); },
        stop() { this.dragging = false; },
    }"
    @pointermove.window="move($event)"
    @pointerup.window="stop()"
    <?= $attributes->twMerge('w-full max-w-xl') ?>
>
    <div
        x-ref="frame"
        @pointerdown.prevent="start($event)"
        class="relative aspect-[3/2] w-full touch-none overflow-hidden rounded-lg border border-border bg-background select-none"
    >
        <img
            src="<?= e((string) $afterSrc) ?>"
            alt="<?= e($afterAlt) ?>"
            draggable="false"
            class="pointer-events-none absolute inset-0 size-full object-cover"
        />

        <img
            src="<?= e((string) $beforeSrc) ?>"
            alt="<?= e($beforeAlt) ?>"
            draggable="false"
            :style="'clip-path: inset(0 ' + (100 - pos) + '% 0 0);'"
            class="pointer-events-none absolute inset-0 size-full object-cover"
        />

        <?php if ($beforeLabel): ?>
            <span class="pointer-events-none absolute start-2 top-2 rounded-md bg-foreground/70 px-2 py-0.5 text-xs font-medium text-background"><?= e($beforeLabel) ?></span>
        <?php endif; ?>
        <?php if ($afterLabel): ?>
            <span class="pointer-events-none absolute end-2 top-2 rounded-md bg-foreground/70 px-2 py-0.5 text-xs font-medium text-background"><?= e($afterLabel) ?></span>
        <?php endif; ?>

        <div
            aria-hidden="true"
            class="pointer-events-none absolute inset-y-0 z-10 w-0.5 -translate-x-1/2 bg-background shadow-[0_0_0_1px_rgba(0,0,0,0.12)] rtl:translate-x-1/2"
            :style="'inset-inline-start: ' + pos + '%;'"
        >
            <div class="absolute top-1/2 left-1/2 flex size-9 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border border-border bg-background text-foreground shadow-md">
                <i data-lucide="chevron-left" class="size-4"></i>
                <i data-lucide="chevron-right" class="-ml-1 size-4"></i>
            </div>
        </div>

        <input
            type="range"
            min="0"
            max="100"
            x-model.number="pos"
            aria-label="Comparison position"
            class="absolute inset-0 z-20 size-full cursor-ew-resize appearance-none bg-transparent opacity-0 focus-visible:opacity-100 focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50"
        />
    </div>
</div>
