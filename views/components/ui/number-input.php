<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => 0,
    'min' => null,
    'max' => null,
    'step' => 1,
    'size' => 'default',
    'disabled' => false,
    'id' => null,
    'placeholder' => null,
]));

$sizes = [
    'sm' => 'h-8 text-sm',
    'default' => 'h-9 text-base md:text-sm',
    'lg' => 'h-10 text-base',
];
$field = $sizes[$size] ?? $sizes['default'];

$btnSizes = [
    'sm' => 'w-8 [&_svg]:size-3.5',
    'default' => 'w-9 [&_svg]:size-4',
    'lg' => 'w-10 [&_svg]:size-4',
];
$btn = $btnSizes[$size] ?? $btnSizes['default'];

$ariaLabel = $attributes->get('aria-label');
$ariaLabelledby = $attributes->get('aria-labelledby');
$attributes = $attributes->except('aria-label', 'aria-labelledby');
$inputLabel = $ariaLabel ?? $name;

$modelInitial = ($value === null || $value === '') ? null : (float) $value;
?>
<div
    data-slot="number-input"
    x-data="{
        _model: $hot.model(<?= js($modelInitial) ?>),
        get value() { return this._model.value; },
        set value(v) { this._model.value = v; },
        min: <?= js($min === null ? null : (float) $min) ?>,
        max: <?= js($max === null ? null : (float) $max) ?>,
        step: <?= js((float) $step) ?>,
        disabled: <?= js((bool) $disabled) ?>,
        clamp(v) {
            if (v === null || isNaN(v)) return v;
            if (this.min !== null && v < this.min) v = this.min;
            if (this.max !== null && v > this.max) v = this.max;
            return v;
        },

        
        inc() {
            if (this.disabled || this.atMax) return;
            this.value = this.clamp(this.$hot.number.step(this.value ?? this.min ?? 0, this.step, this.step));
        },
        dec() {
            if (this.disabled || this.atMin) return;
            this.value = this.clamp(this.$hot.number.step(this.value ?? this.max ?? 0, -this.step, this.step));
        },
        onInput(e) {
            const raw = e.target.value;
            this.value = raw === '' ? null : parseFloat(raw);
        },
        onBlur(e) {
            if (this.value === null || isNaN(this.value)) {
                this.value = null;
                e.target.value = '';
                return;
            }
            this.value = this.clamp(this.value);
            e.target.value = this.value;
        },
        get atMin() { return this.value !== null && this.min !== null && this.value <= this.min; },
        get atMax() { return this.value !== null && this.max !== null && this.value >= this.max; },
    }"
    role="group"
    <?= $attributes->twMerge('border-input dark:bg-input/30 inline-flex items-stretch overflow-hidden rounded-md border bg-transparent shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px] has-[input:disabled]:pointer-events-none has-[input:disabled]:opacity-50') ?>
>
    <button
        type="button"
        aria-label="Decrease"
        @click="dec()"
        :disabled="disabled || atMin"
        class="<?= classes([
            'border-input text-muted-foreground hover:bg-accent hover:text-accent-foreground flex shrink-0 items-center justify-center border-e outline-none transition-colors not-disabled:cursor-pointer disabled:pointer-events-none disabled:opacity-50',
            $btn,
        ]) ?>"
    >
        <i data-lucide="minus" aria-hidden="true"></i>
    </button>

    <input
        type="text"
        inputmode="decimal"
        role="spinbutton"
        <?php if ($ariaLabelledby): ?>aria-labelledby="<?= e($ariaLabelledby) ?>"
        <?php elseif ($inputLabel): ?>aria-label="<?= e($inputLabel) ?>"
        <?php else: ?>aria-label="Number"<?php endif; ?>
        <?php if ($name): ?>name="<?= e($name) ?>"
        <?php endif; ?>
        <?php if ($id): ?>id="<?= e($id) ?>"
        <?php endif; ?>
        <?php if ($placeholder): ?>placeholder="<?= e($placeholder) ?>"
        <?php endif; ?>
        <?php if ($disabled): ?>disabled<?php endif; ?>
        :value="value"
        @input="onInput($event)"
        @blur="onBlur($event)"
        :aria-valuenow="value"
        <?php if ($min !== null): ?>aria-valuemin="<?= e($min) ?>"
        <?php endif; ?>
        <?php if ($max !== null): ?>aria-valuemax="<?= e($max) ?>"
        <?php endif; ?>
        class="<?= classes([
            'placeholder:text-muted-foreground text-foreground selection:bg-primary selection:text-primary-foreground w-full min-w-0 border-0 bg-transparent px-3 py-1 text-center tabular-nums outline-none disabled:cursor-not-allowed',
            $field,
        ]) ?>"
    />

    <button
        type="button"
        aria-label="Increase"
        @click="inc()"
        :disabled="disabled || atMax"
        class="<?= classes([
            'border-input text-muted-foreground hover:bg-accent hover:text-accent-foreground flex shrink-0 items-center justify-center border-s outline-none transition-colors not-disabled:cursor-pointer disabled:pointer-events-none disabled:opacity-50',
            $btn,
        ]) ?>"
    >
        <i data-lucide="plus" aria-hidden="true"></i>
    </button>
</div>
