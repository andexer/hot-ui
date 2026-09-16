<?php

declare(strict_types=1);

extract(props($__ctx, [
    'align' => 'center',
    'side' => 'bottom',
    'sideOffset' => 4,
]));

$placement = $side.($align === 'center' ? '' : '-'.$align);
$anchorAttr = 'x-hot-anchor.'.$placement.'.offset.'.$sideOffset.'.no-size="$refs.trigger"';
?>
<template x-teleport="body">
    <div
        x-hot-dialog-layer
        x-show="open"
        x-cloak
        <?= $anchorAttr ?>
        @mouseenter="show()"
        @mouseleave="hide()"
        tabindex="-1"
        data-slot="hover-card-content"
        data-side="<?= e($side) ?>"
        :data-state="open ? 'open' : 'closed'"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        <?= $attributes->twMerge('bg-popover text-popover-foreground fixed z-50 w-64 origin-top rounded-md border p-4 shadow-md outline-hidden') ?>
    >
        <?= $slot ?>
    </div>
</template>
