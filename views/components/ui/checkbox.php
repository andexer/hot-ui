<?php

declare(strict_types=1);

extract(props($__ctx, [
    'id' => null,
    'name' => null,
    'value' => 'on',
    'checked' => false,
    'disabled' => false,
    'indeterminate' => false,
    'native' => false,
]));

?>
<?php if ($native): ?>
<input
        type="checkbox"
        <?php if ($id): ?>id="<?= e($id) ?>"
        <?php endif; ?>
        <?php if ($name): ?>name="<?= e($name) ?>"
        <?php endif; ?>
        value="<?= e($value) ?>"
        <?php if ($checked): ?>checked<?php endif; ?>
        <?php if ($disabled): ?>disabled<?php endif; ?>
        data-slot="checkbox"
        <?= $attributes->twMerge('hot-checkbox') ?>
    />
<?php else: ?>
    <button
        type="button"
        role="checkbox"
        <?php if ($id): ?>id="<?= e($id) ?>"
        <?php endif; ?>
        x-data="{ _model: $hot.model(<?= js((bool) $checked) ?>), get checked() { return this._model.value; }, set checked(v) { this._model.value = v; }, indeterminate: <?= js((bool) $indeterminate) ?> }"
        :data-state="indeterminate ? 'indeterminate' : (checked ? 'checked' : 'unchecked')"
        :aria-checked="indeterminate ? 'mixed' : checked.toString()"
        @click="indeterminate ? (indeterminate = false, checked = true) : (checked = !checked)"
        <?php if ($disabled): ?>disabled<?php endif; ?>
        data-slot="checkbox"
        <?= $attributes->twMerge('peer border-input dark:bg-input/30 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground dark:data-[state=checked]:bg-primary data-[state=checked]:border-primary data-[state=indeterminate]:bg-primary data-[state=indeterminate]:text-primary-foreground data-[state=indeterminate]:border-primary focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive size-4 shrink-0 rounded-[4px] border shadow-xs transition-shadow outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 flex items-center justify-center') ?>
    >
        <span data-slot="checkbox-indicator" class="flex items-center justify-center text-current transition-none" x-show="checked || indeterminate" x-cloak>
            <i data-lucide="minus" class="size-3.5" x-show="indeterminate" aria-hidden="true"></i>
            <i data-lucide="check" class="size-3.5" x-show="!indeterminate" aria-hidden="true"></i>
        </span>
<?php if ($name): ?>
            <input type="hidden" :name="checked && !indeterminate ? <?= js($name) ?> : null" value="<?= e($value) ?>">
        <?php endif; ?>
    </button>
<?php endif; ?>
