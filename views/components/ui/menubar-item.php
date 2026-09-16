<?php

declare(strict_types=1);

extract(props($__ctx, [
    'href' => null,
    'variant' => 'default',
    'inset' => false,
    'disabled' => false,
]));

$menubarClasses = "focus:bg-accent focus:text-accent-foreground hover:bg-accent hover:text-accent-foreground data-[variant=destructive]:text-destructive data-[variant=destructive]:focus:bg-destructive/10 data-[variant=destructive]:hover:bg-destructive/10 data-[variant=destructive]:focus:text-destructive data-[variant=destructive]:*:[svg]:!text-destructive [&_svg:not([class*='text-'])]:text-muted-foreground relative flex w-full cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-start text-sm outline-hidden select-none data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4 data-[inset]:ps-8";

$attributes = $attributes->except('type', 'closeOnSelect', 'close-on-select');
?>
<?= $this->uiMenuItem([
    'dataSlot' => 'menubar-item',
    'classes' => $menubarClasses,
    'mergeClick' => false,
    'clickConditional' => false,
    'href' => $href,
    'variant' => $variant,
    'inset' => $inset,
    'disabled' => $disabled,
    ...$attributes->all(),
], $slot) ?>
