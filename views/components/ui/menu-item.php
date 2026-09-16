<?php

declare(strict_types=1);

extract(props($__ctx, [
    'dataSlot' => 'menu-item',
    'classes' => "focus:bg-accent focus:text-accent-foreground hover:bg-accent hover:text-accent-foreground data-[variant=destructive]:text-destructive data-[variant=destructive]:focus:bg-destructive/10 dark:data-[variant=destructive]:focus:bg-destructive/20 data-[variant=destructive]:focus:text-destructive data-[variant=destructive]:hover:bg-destructive/10 data-[variant=destructive]:*:[svg]:!text-destructive [&_svg:not([class*='text-'])]:text-muted-foreground relative flex w-full cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-start text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 data-[inset]:ps-8",
    'href' => null,
    'variant' => 'default',
    'inset' => false,
    'disabled' => false,
    'closeOnSelect' => true,
    'type' => 'button',

    'mergeClick' => true,

    'clickConditional' => true,
]));

if ($mergeClick) {
    $userClick = $attributes->get('@click') ?? $attributes->get('x-on:click');
    $attributes = $attributes->except('@click', 'x-on:click');
    $clickExpr = implode('; ', array_filter([$userClick, $closeOnSelect ? 'closeMenu()' : null]));
} else {
    $clickExpr = 'closeMenu()';
}

$linkTarget = safe_url(is_string($href) ? $href : null);
?>
<?php if ($linkTarget !== null && $linkTarget !== ''): ?>
<?php if ($clickConditional): ?>
    <a
        href="<?= e($linkTarget) ?>"
        role="menuitem"
        tabindex="-1"
        data-slot="<?= e($dataSlot) ?>"
        data-variant="<?= e($variant) ?>"
<?php if ($inset): ?> data-inset<?php endif; ?>
<?php if ($clickExpr): ?> @click="<?= e($clickExpr) ?>"<?php endif; ?>
        <?= $attributes->twMerge($classes) ?>
    ><?= $slot ?></a>
<?php else: ?>
    <a
        href="<?= e($linkTarget) ?>"
        role="menuitem"
        tabindex="-1"
        data-slot="<?= e($dataSlot) ?>"
        data-variant="<?= e($variant) ?>"
<?php if ($inset): ?> data-inset<?php endif; ?>
        @click="<?= e($clickExpr) ?>"
        <?= $attributes->twMerge($classes) ?>
    ><?= $slot ?></a>
<?php endif; ?>
<?php else: ?>
<?php if ($clickConditional): ?>
    <button
        type="<?= e($type) ?>"
        role="menuitem"
        tabindex="-1"
        data-slot="<?= e($dataSlot) ?>"
        data-variant="<?= e($variant) ?>"
<?php if ($inset): ?> data-inset<?php endif; ?>
<?php if ($disabled): ?> disabled data-disabled aria-disabled="true"<?php endif; ?>
<?php if ($clickExpr): ?> @click="<?= e($clickExpr) ?>"<?php endif; ?>
        <?= $attributes->twMerge($classes) ?>
    ><?= $slot ?></button>
<?php else: ?>
    <button
        type="<?= e($type) ?>"
        role="menuitem"
        tabindex="-1"
        data-slot="<?= e($dataSlot) ?>"
        data-variant="<?= e($variant) ?>"
<?php if ($inset): ?> data-inset<?php endif; ?>
<?php if ($disabled): ?> disabled data-disabled aria-disabled="true"<?php endif; ?>
        @click="<?= e($clickExpr) ?>"
        <?= $attributes->twMerge($classes) ?>
    ><?= $slot ?></button>
<?php endif; ?>
<?php endif; ?>
