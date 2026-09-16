<?php

declare(strict_types=1);

extract(props($__ctx, [
    'type' => 'single',
    'value' => null,
    'variant' => 'default',
    'size' => 'default',
    'orientation' => 'horizontal',
]));

?>
<div
    data-slot="toggle-group"
    data-variant="<?= e($variant) ?>"
    data-size="<?= e($size) ?>"
    role="group"
    data-orientation="<?= e($orientation) ?>"
    x-data="{
        type: <?= js($type) ?>,
        _model: $hot.model(<?= js($type === 'multiple' ? (array) ($value ?? []) : $value) ?>),
        get value() { return this._model.value; },
        set value(v) { this._model.value = v; },
        rovingValue: null,
        toggle(v) {
            if (this.type === 'multiple') {
                this.value = this.value.includes(v) ? this.value.filter(x => x !== v) : [...this.value, v];
            } else {
                this.value = this.value === v ? null : v;
            }
        },
        isOn(v) {
            return this.type === 'multiple' ? this.value.includes(v) : this.value === v;
        },
    }"
    x-init="$nextTick(() => { const f = $el.querySelector('[data-slot=toggle-group-item]:not([disabled])'); rovingValue = f?.getAttribute('data-value') ?? null })"
    @keydown="if (['ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End'].includes($event.key)) { $hot.nav($event, { selector: '[data-slot=toggle-group-item]', orientation: 'both' }); }"
    <?= $attributes->twMerge('group/toggle-group flex w-fit items-center rounded-md data-[orientation=vertical]:flex-col data-[orientation=vertical]:items-stretch data-[variant=outline]:shadow-xs') ?>
>
    <?= $slot ?>
</div>
