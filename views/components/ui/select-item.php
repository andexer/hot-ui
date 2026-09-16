<?php

declare(strict_types=1);

extract(aware($__ctx, [
    'value' => '',
    'disabled' => false,
    'indicator' => 'check',
]), EXTR_SKIP);

$indicator = in_array($indicator, ['check', 'checkbox', 'radio'], true) ? $indicator : 'check';
$valueJs = js((string) $value);
?>
<div
    role="option"
    tabindex="-1"
    data-slot="select-item"
    data-value="<?= e($value) ?>"
<?php if ($disabled): ?>
    data-disabled aria-disabled="true"
<?php elseif (! $disabled): ?>
    @click="selectOption(<?= $valueJs ?>, $el.querySelector('[data-slot=select-item-label]').textContent.trim())"
<?php endif; ?>
    x-init="seedSelected(<?= $valueJs ?>, $el.querySelector('[data-slot=select-item-label]').textContent.trim())"
    :aria-selected="isSelected(<?= $valueJs ?>)"
    :data-state="isSelected(<?= $valueJs ?>) ? 'checked' : 'unchecked'"
    <?= $attributes->twMerge("hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground [&_svg:not([class*='text-'])]:text-muted-foreground relative flex w-full cursor-default items-center gap-2 rounded-sm py-1.5 pe-8 ps-2 text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4") ?>
>
<?php if ($indicator === 'checkbox'): ?>
    <span class="absolute end-2 flex items-center justify-center">
        <span class="border-input flex size-4 items-center justify-center rounded-[4px] border transition-colors" :class="isSelected(<?= $valueJs ?>) && 'bg-primary border-primary text-primary-foreground'">
            <i data-lucide="check" class="size-3" x-bind:class="isSelected(<?= $valueJs ?>) ? 'opacity-100' : 'opacity-0'" aria-hidden="true"></i>
        </span>
    </span>
<?php elseif ($indicator === 'radio'): ?>
    <span class="absolute end-2 flex items-center justify-center">
        <span class="border-input flex size-4 items-center justify-center rounded-full border transition-colors" :class="isSelected(<?= $valueJs ?>) && 'border-primary'">
            <span class="bg-primary size-2 rounded-full transition-opacity" :class="isSelected(<?= $valueJs ?>) ? 'opacity-100' : 'opacity-0'"></span>
        </span>
    </span>
<?php else: ?>
    <span class="absolute end-2 flex size-3.5 items-center justify-center">
        <i data-lucide="check" class="size-4" x-show="isSelected(<?= $valueJs ?>)" x-cloak aria-hidden="true"></i>
    </span>
<?php endif; ?>
    <span data-slot="select-item-label" class="flex items-center gap-2"><?= $slot ?></span>
</div>
