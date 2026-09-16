<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => null,
]));

?>
<div
    data-slot="radio-group"
    role="radiogroup"
    x-data="{ _model: $hot.model(<?= js($value) ?>), get value() { return this._model.value; }, set value(v) { this._model.value = v; }, rovingValue: <?= js($value) ?> }"
    x-init="$nextTick(() => { if (rovingValue === null) { const f = $el.querySelector('[role=radio]:not([disabled])'); rovingValue = f?.getAttribute('data-value') ?? null } })"
    @keydown="if (['ArrowUp','ArrowDown','ArrowLeft','ArrowRight','Home','End'].includes($event.key)) { $hot.nav($event, { selector: '[role=radio]', orientation: 'both' }); const v = document.activeElement?.getAttribute('data-value'); if (v != null) { value = v; rovingValue = v; } }"
    <?= $attributes->twMerge('grid gap-3') ?>
>
    <?php if ($name): ?>
        <input type="hidden" name="<?= e($name) ?>" :value="value">
    <?php endif; ?>
    <?= $slot ?>
</div>
