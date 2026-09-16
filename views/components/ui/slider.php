<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'value' => 0,
    'range' => false,
    'orientation' => 'horizontal',
    'disabled' => false,
    'ariaLabel' => 'Value',
]));

if ($range) {
    $vals = is_array($value) ? array_values($value) : [$min, $max];
    $low = $vals[0] ?? $min;
    $high = $vals[1] ?? $max;
}
$vertical = $orientation === 'vertical';
$containerCls = $vertical
    ? 'relative flex h-40 w-fit touch-none flex-col items-center select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50'
    : 'relative flex w-full touch-none items-center select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50';
$trackCls = $vertical
    ? 'bg-muted relative grow overflow-hidden rounded-full w-1.5 h-full'
    : 'bg-muted relative grow overflow-hidden rounded-full h-1.5 w-full';
$thumbCls = 'border-primary bg-background ring-ring/50 absolute block size-4 shrink-0 rounded-full border shadow-sm transition-[color,box-shadow] hover:ring-4 focus-visible:ring-4 focus-visible:outline-hidden'
    .($vertical ? ' left-1/2 -translate-x-1/2 translate-y-1/2' : ' top-1/2 -translate-x-1/2 -translate-y-1/2');

$modelInit = js($range ? [(float) $low, (float) $high] : (float) $value);
?>
<div
    data-slot="slider"
    data-orientation="<?= e($orientation) ?>"
    <?php if ($range): ?>data-range<?php endif; ?>
    <?php if ($disabled): ?>data-disabled<?php endif; ?>
    x-data="{
        min: <?= e((string) $min) ?>,
        max: <?= e((string) $max) ?>,
        step: <?= e((string) $step) ?>,
        disabled: <?= $disabled ? 'true' : 'false' ?>,
        range: <?= $range ? 'true' : 'false' ?>,
        vertical: <?= $vertical ? 'true' : 'false' ?>,
        _model: $hot.model(<?= $modelInit ?>),
        get value() { return this._model.value; },
        set value(v) { this._put(v); },

        
        
        get low() { return this.range ? (this._model.value?.[0] ?? this.min) : 0; },
        set low(v) { this._put([v, this.high]); },
        get high() { return this.range ? (this._model.value?.[1] ?? this.max) : 0; },
        set high(v) { this._put([this.low, v]); },

        
        _put(next) { this.dragging ? this._model.write(next) : (this._model.value = next); },
        dragging: false,
        active: null,
        pct(v) { return ((v - this.min) / (this.max - this.min)) * 100 },
        get percent() { return this.pct(this.value) },
        get lowPercent() { return this.pct(this.low) },
        get highPercent() { return this.pct(this.high) },
        clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)) },

        
        snap(raw) { return this.clamp(this.$hot.number.snap(raw, this.step), this.min, this.max) },
        valAt(e) {
            const r = this.$refs.track.getBoundingClientRect();
            let ratio = this.vertical ? (1 - (e.clientY - r.top) / r.height) : ((e.clientX - r.left) / r.width);
            ratio = Math.max(0, Math.min(1, ratio));
            return this.snap(this.min + ratio * (this.max - this.min));
        },
        nearest(e) {
            const v = this.valAt(e);
            return Math.abs(v - this.low) <= Math.abs(v - this.high) ? 'low' : 'high';
        },
        thumbStyle(p) { return this.vertical ? `bottom: ${p}%` : `left: ${p}%` },
        fillStyle(a, b) { return this.vertical ? `bottom: ${a}%; height: ${b}%` : `left: ${a}%; width: ${b}%` },
        start(e, thumb) {
            if (this.disabled) return;
            this.dragging = true;
            if (this.range) { this.active = thumb || this.nearest(e); this.move(e); }
            else { this.value = this.valAt(e); }
        },
        move(e) {
            if (!this.dragging) return;
            if (!this.range) { this.value = this.valAt(e); return; }
            const v = this.valAt(e);
            if (this.active === 'low') this.low = this.clamp(v, this.min, this.high);
            else this.high = this.clamp(v, this.low, this.max);
        },
        stop() { if (! this.dragging) return; this.dragging = false; this.active = null; this._model.commit(); },
        bump(d) { if (this.disabled) return; this.value = this.clamp(this.$hot.number.step(this.value, d * this.step, this.step), this.min, this.max); },
        bumpLow(d) { if (this.disabled) return; this.low = this.clamp(this.$hot.number.step(this.low, d * this.step, this.step), this.min, this.high); },
        bumpHigh(d) { if (this.disabled) return; this.high = this.clamp(this.$hot.number.step(this.high, d * this.step, this.step), this.low, this.max); },
        page(d) { if (this.disabled) return; const big = Math.max(this.step, (this.max - this.min) / 10); this.value = this.clamp(this.$hot.number.step(this.value, d * big, this.step), this.min, this.max); }
    }"
    @pointermove.window="move($event)"
    @pointerup.window="stop()"
    <?= $attributes->twMerge($containerCls) ?>
>
    <?php if ($range): ?>
        <?php if ($name): ?>
            <input type="hidden" name="<?= e($name) ?>[min]" :value="low">
            <input type="hidden" name="<?= e($name) ?>[max]" :value="high">
        <?php endif; ?>
        <span data-slot="slider-track" x-ref="track" @pointerdown="start($event)" class="<?= e($trackCls) ?>">
            <span data-slot="slider-range" class="bg-primary absolute <?= $vertical ? 'w-full' : 'h-full' ?>" :style="fillStyle(lowPercent, highPercent - lowPercent)"></span>
        </span>
        <span
            data-slot="slider-thumb" role="slider" aria-orientation="<?= e($orientation) ?>" aria-label="<?= e($ariaLabel) ?> minimum"
            :tabindex="disabled ? -1 : 0" :aria-disabled="disabled" :aria-valuemin="min" :aria-valuemax="high" :aria-valuenow="low"
            @pointerdown.stop="start($event, 'low')"
            @keydown.left.prevent="bumpLow(-1)" @keydown.down.prevent="bumpLow(-1)"
            @keydown.right.prevent="bumpLow(1)" @keydown.up.prevent="bumpLow(1)"
            @keydown.home.prevent="low = min" @keydown.end.prevent="low = high"
            :style="thumbStyle(lowPercent)"
            class="<?= e($thumbCls) ?>"
        ></span>
        <span
            data-slot="slider-thumb" role="slider" aria-orientation="<?= e($orientation) ?>" aria-label="<?= e($ariaLabel) ?> maximum"
            :tabindex="disabled ? -1 : 0" :aria-disabled="disabled" :aria-valuemin="low" :aria-valuemax="max" :aria-valuenow="high"
            @pointerdown.stop="start($event, 'high')"
            @keydown.left.prevent="bumpHigh(-1)" @keydown.down.prevent="bumpHigh(-1)"
            @keydown.right.prevent="bumpHigh(1)" @keydown.up.prevent="bumpHigh(1)"
            @keydown.home.prevent="high = low" @keydown.end.prevent="high = max"
            :style="thumbStyle(highPercent)"
            class="<?= e($thumbCls) ?>"
        ></span>
    <?php else: ?>
        <?php if ($name): ?>
            <input type="hidden" name="<?= e($name) ?>" :value="value">
        <?php endif; ?>
        <span data-slot="slider-track" x-ref="track" @pointerdown="start($event)" class="<?= e($trackCls) ?>">
            <span data-slot="slider-range" class="bg-primary absolute <?= $vertical ? 'w-full' : 'h-full' ?>" :style="fillStyle(0, percent)"></span>
        </span>
        <span
            data-slot="slider-thumb" role="slider" aria-orientation="<?= e($orientation) ?>" aria-label="<?= e($ariaLabel) ?>"
            :tabindex="disabled ? -1 : 0" :aria-disabled="disabled" :aria-valuemin="min" :aria-valuemax="max" :aria-valuenow="value"
            @pointerdown="start($event)"
            @keydown.left.prevent="bump(-1)" @keydown.down.prevent="bump(-1)"
            @keydown.right.prevent="bump(1)" @keydown.up.prevent="bump(1)"
            @keydown.home.prevent="value = min" @keydown.end.prevent="value = max"
            @keydown.page-up.prevent="page(1)" @keydown.page-down.prevent="page(-1)"
            :style="thumbStyle(percent)"
            class="<?= e($thumbCls) ?>"
        ></span>
    <?php endif; ?>
</div>
