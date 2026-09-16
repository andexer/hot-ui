<?php

declare(strict_types=1);

extract(props($__ctx, [
    'id' => null,
    'name' => null,
    'value' => 'on',
    'checked' => false,
    'disabled' => false,
    'size' => 'default',
]));

$track = match ($size) {
    'sm' => 'h-4 w-7',
    'lg' => 'h-6 w-10',

    default => 'h-[calc(var(--spacing)*4.6)] w-8',
};
$thumb = match ($size) {
    'sm' => 'size-3.5',
    'lg' => 'size-5',
    default => 'size-4',
};
?>
<button
    type="button"
    role="switch"
    <?php if ($id): ?>id="<?= e($id) ?>"<?php endif; ?>
    x-data="{ _model: $hot.model(<?= js((bool) $checked) ?>), get checked() { return this._model.value; }, set checked(v) { this._model.value = v; }, }"
    :data-state="checked ? 'checked' : 'unchecked'"
    :aria-checked="checked"
    @click="checked = !checked"
    <?php if ($disabled): ?>disabled<?php endif; ?>
    data-slot="switch"
    <?= $attributes->twMerge("peer data-[state=checked]:bg-primary data-[state=unchecked]:bg-input focus-visible:border-ring focus-visible:ring-ring/50 dark:data-[state=unchecked]:bg-input/80 inline-flex {$track} shrink-0 items-center rounded-full border border-transparent shadow-xs transition-all outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50") ?>
>
    <span
        data-slot="switch-thumb"
        :data-state="checked ? 'checked' : 'unchecked'"
        class="bg-background dark:data-[state=unchecked]:bg-foreground dark:data-[state=checked]:bg-primary-foreground pointer-events-none block <?= e($thumb) ?> rounded-full ring-0 transition-transform data-[state=checked]:translate-x-[calc(100%-2px)] data-[state=unchecked]:translate-x-0"
    ></span>
    <?php if ($name): ?>
        <input type="hidden" :name="checked ? <?= js($name) ?> : null" value="<?= e((string) $value) ?>">
    <?php endif; ?>
</button>
