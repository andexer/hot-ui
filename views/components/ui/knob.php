<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => 50,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'size' => 'default',
    'label' => 'Value',
    'disabled' => false,
]));

$dims = [
    'sm' => ['box' => 'size-16', 'text' => 'text-sm'],
    'default' => ['box' => 'size-24', 'text' => 'text-base'],
    'lg' => ['box' => 'size-32', 'text' => 'text-lg'],
];
$dim = $dims[$size] ?? $dims['default'];

$vb = 100;
$center = 50;
$thickness = 8;
$radius = ($vb - $thickness) / 2;
$circ = 2 * M_PI * $radius;
$sweep = 0.75;
$arcLen = $circ * $sweep;

?>
<div
    data-slot="knob"
    data-disabled="<?= js((bool) $disabled) ?>"
    x-data="{
        min: <?= js((float) $min) ?>,
        max: <?= js((float) $max) ?>,
        step: <?= js((float) $step) ?>,
        disabled: <?= js((bool) $disabled) ?>,
        _model: $hot.model(<?= js((float) $value) ?>),
        get value() { return this._model.value; },
        set value(v) { this._model.value = v; },
        dragging: false,
        circ: <?= js($circ) ?>,
        arcLen: <?= js($arcLen) ?>,
        startAngle: -135,
        sweepDeg: 270,
        clamp(v) { return Math.max(this.min, Math.min(this.max, v)) },

        
        snap(raw) { return this.clamp(this.$hot.number.snap(raw, this.step, this.min)); },
        get ratio() { return (this.value - this.min) / (this.max - this.min || 1) },
        get dashOffset() { return this.circ - this.arcLen * this.ratio },
        get angle() { return this.startAngle + this.sweepDeg * this.ratio },
        get display() {
            
            return Number.isInteger(this.step) ? Math.round(this.value) : parseFloat(this.value.toFixed(3));
        },
        bump(d) { if (this.disabled) return; this.value = this.clamp(this.$hot.number.step(this.value, d * this.step, this.step)) },
        page(d) {
            if (this.disabled) return;
            const big = Math.max(this.step, (this.max - this.min) / 10);
            this.value = this.clamp(this.$hot.number.step(this.value, d * big, this.step));
        },
        wheel(e) {
            if (this.disabled) return;
            e.preventDefault();
            this.bump(e.deltaY < 0 ? 1 : -1);
        },
        valFromEvent(e) {
            const r = this.$refs.dial.getBoundingClientRect();
            const cx = r.left + r.width / 2;
            const cy = r.top + r.height / 2;
            
            let deg = Math.atan2(e.clientX - cx, cy - e.clientY) * 180 / Math.PI;
            const end = this.startAngle + this.sweepDeg;   
            let ratio;
            if (deg < this.startAngle || deg > end) {
                
                ratio = deg < 0 ? 0 : 1;
            } else {
                ratio = (deg - this.startAngle) / this.sweepDeg;
            }
            return this.snap(this.min + ratio * (this.max - this.min));
        },
        start(e) {
            if (this.disabled) return;
            this.dragging = true;
            this.value = this.valFromEvent(e);
            this.$refs.dial.focus();
        },
        move(e) { if (this.dragging) this.value = this.valFromEvent(e) },
        stop() { this.dragging = false },
    }"
    @pointermove.window="move($event)"
    @pointerup.window="stop()"
    <?= $attributes->twMerge('text-primary inline-flex flex-col items-center gap-2 select-none data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50') ?>
>
<?php if ($name): ?>
        <input type="hidden" name="<?= e($name) ?>" :value="value">
<?php endif; ?>

    <div
        x-ref="dial"
        role="slider"
        tabindex="<?= e($disabled ? -1 : 0) ?>"
        aria-label="<?= e($label) ?>"
        aria-valuemin="<?= e($min) ?>"
        aria-valuemax="<?= e($max) ?>"
        :aria-valuenow="value"
        :aria-disabled="disabled"
        @pointerdown.prevent="start($event)"
        @wheel="wheel($event)"
        @keydown.up.prevent="bump(1)"
        @keydown.right.prevent="bump(1)"
        @keydown.down.prevent="bump(-1)"
        @keydown.left.prevent="bump(-1)"
        @keydown.home.prevent="value = min"
        @keydown.end.prevent="value = max"
        @keydown.page-up.prevent="page(1)"
        @keydown.page-down.prevent="page(-1)"
        class="<?= classes([
            $dim['box'],
            'relative grid touch-none place-items-center rounded-full outline-none not-data-[disabled=true]:cursor-grab focus-visible:ring-ring/50 focus-visible:ring-[3px]',
        ]) ?>"
        :class="dragging && 'cursor-grabbing'"
    >
        <svg viewBox="0 0 <?= e($vb) ?> <?= e($vb) ?>" fill="none" aria-hidden="true" class="size-full -rotate-[135deg]">
            <circle
                cx="<?= e($center) ?>" cy="<?= e($center) ?>" r="<?= e($radius) ?>"
                class="stroke-border" stroke-width="<?= e($thickness) ?>" stroke-linecap="round"
                stroke-dasharray="<?= e($arcLen) ?> <?= e($circ) ?>"
            />
            <circle
                cx="<?= e($center) ?>" cy="<?= e($center) ?>" r="<?= e($radius) ?>"
                stroke="currentColor" stroke-width="<?= e($thickness) ?>" stroke-linecap="round"
                stroke-dasharray="<?= e($circ) ?>" :stroke-dashoffset="dashOffset"
                class="transition-[stroke-dashoffset] duration-100 ease-out"
            />
            <line
                x1="<?= e($center) ?>" y1="<?= e($thickness + 3) ?>" x2="<?= e($center) ?>" y2="<?= e($thickness + 11) ?>"
                stroke="currentColor" stroke-width="<?= e($thickness * 0.5) ?>" stroke-linecap="round"
                :style="`transform: rotate(${angle + 135}deg); transform-origin: <?= e($center) ?>px <?= e($center) ?>px;`"
            />
        </svg>
        <span
            data-slot="knob-value"
            class="<?= classes(['absolute font-semibold tabular-nums', $dim['text']]) ?>"
            x-text="display"
        ></span>
    </div>
</div>
