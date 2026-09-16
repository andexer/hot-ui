<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'maxlength' => 6,
    'value' => '',
    'disabled' => false,
    'alphanumeric' => false,
    'ariaLabel' => 'One-time password',
]));

$inputmode = $alphanumeric ? 'text' : 'numeric';
$pattern = $alphanumeric ? '[a-zA-Z0-9]*' : '[0-9]*';

?>
<div
    data-slot="input-otp"
    x-data="{
        _model: $hot.model(<?= js((string) $value) ?>),
        get value() { return this._model.value; },
        set value(v) { this._model.value = v; },
        max: <?= (int) $maxlength ?>,
        focused: false,
        get active() { return this.focused ? Math.min(this.value.length, this.max - 1) : -1 }
    }"
    <?= $attributes->twMerge('relative flex items-center gap-2 has-disabled:opacity-50') ?>
>
    <input
        x-ref="input"
        x-model="value"
        :maxlength="max"
        @focus="focused = true"
        @blur="focused = false"
        inputmode="<?= e($inputmode) ?>"
        autocomplete="one-time-code"
        pattern="<?= e($pattern) ?>"
        aria-label="<?= e($ariaLabel) ?>"
        <?php if ($name): ?>name="<?= e($name) ?>"
        <?php endif; ?>
        <?php if ($disabled): ?>disabled<?php endif; ?>
        class="absolute inset-0 z-10 h-full w-full cursor-default opacity-0 disabled:cursor-not-allowed"
    >
    <?= $slot ?>
</div>
