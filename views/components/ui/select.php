<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => '',
    'native' => false,
    'size' => 'default',
    'multiple' => false,
    'options' => null,
    'placeholder' => '',
    'color' => null,
    'indicator' => 'check',
]));

$hasOptions = is_array($options) && count($options) > 0;

$normalized = [];
if ($hasOptions) {
    foreach ($options as $k => $v) {
        if (is_int($k)) {
            $normalized[(string) $v] = (string) $v;
        } else {
            $normalized[(string) $k] = (string) $v;
        }
    }
}

$selectedValues = array_map(
    static fn ($v): string => (string) $v,
    is_array($value) ? array_values($value) : (($value === '' || $value === null) ? [] : [$value]),
);
$initialValue = $multiple ? $selectedValues : (string) $value;

$colorStyle = $color ? "--ring: {$color}; --primary: {$color}; --primary-foreground: #ffffff;" : '';
$userStyle = (string) $attributes->get('style', '');
$style = trim($colorStyle.($colorStyle && $userStyle ? ' ' : '').$userStyle);
$attributes = $attributes->except('style');

$hasHotModel = ($modelPath = $attributes->get('data-hot-model')) !== null;

$nativeSizes = ['sm' => 'h-8 text-sm', 'default' => 'h-9', 'lg' => 'h-10'];
$nativeSize = $nativeSizes[$size] ?? $nativeSizes['default'];
?>
<?php if ($native): ?>
    <select
        <?php if ($name): ?>name="<?= e($name) ?><?= $multiple ? '[]' : '' ?>"<?php endif; ?>
        <?php if ($multiple): ?>multiple<?php endif; ?>
        data-slot="select"
        data-size="<?= e($size) ?>"
        <?php if ($style): ?>style="<?= e($style) ?>"<?php endif; ?>
        <?= $attributes->twMerge('hot-select '.($multiple ? 'h-auto min-h-9 py-1' : $nativeSize)) ?>
    >
        <?php if ($hasOptions): ?>
            <?php if (! $multiple && $placeholder !== ''): ?>
                <option value="" disabled<?php if ($value === '') { echo ' selected'; } ?>><?= e($placeholder) ?></option>
            <?php endif; ?>
            <?php foreach ($normalized as $val => $lab): ?>
                <option value="<?= e($val) ?>"<?php if (in_array((string) $val, $selectedValues, true)) { echo ' selected'; } ?>><?= e($lab) ?></option>
            <?php endforeach; ?>
        <?php else: ?>
            <?= $slot ?>
        <?php endif; ?>
    </select>
<?php else: ?>
    <div
        data-slot="select"
        x-data="hotSelect({ model: $hot.model(<?= js($initialValue) ?>), wired: <?= js($hasHotModel) ?>, multiple: <?= js((bool) $multiple) ?> })"
        x-id="['hot-listbox']"
        <?php if ($style): ?>style="<?= e($style) ?>"<?php endif; ?>
        <?= $attributes->twMerge('relative') ?>
    >
        <?php if ($name): ?>
            <?php if ($multiple): ?>
                <template x-for="v in value" :key="v">
                    <input type="hidden" name="<?= e($name) ?>[]" :value="v">
                </template>
            <?php else: ?>
                <input type="hidden" name="<?= e($name) ?>" :value="value">
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($hasOptions): ?>
            <?= $this->uiSelectTrigger(['class' => 'w-full'.($multiple ? ' data-[size=default]:h-auto min-h-9 py-1' : ''), 'ariaLabel' => $placeholder !== '' ? $placeholder : 'Select option'], function () use ($placeholder): void { ?>
                <?= $this->uiSelectValue(['placeholder' => $placeholder]) ?>
            <?php }) ?>
            <?= $this->uiSelectContent(['indicator' => $indicator], function () use ($normalized): void {
                foreach ($normalized as $val => $lab) { ?>
                    <?= $this->uiSelectItem(['value' => $val], e($lab)) ?>
                <?php }
            }) ?>
        <?php else: ?>
            <?= $slot ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
