<?php

declare(strict_types=1);

extract(props($__ctx, [
    'align' => 'start',
    'side' => 'bottom',
    'sideOffset' => 4,
]));

$placement = $side.($align === 'center' ? '' : '-'.$align);

$anchorRef = '($refs.trigger?.querySelector(\'[data-slot=sidebar-menu-action]\') || $refs.trigger?.firstElementChild || $refs.trigger)';
$anchorAttr = 'x-hot-anchor.'.$placement.'.offset.'.$sideOffset.'="'.$anchorRef.'"';
?>
<template x-teleport="body">
    <div
        x-hot-dialog-layer
        x-show="open"
        x-cloak
        x-ref="menu"
        x-init="_menu = $el"
        <?= $anchorAttr ?>
        @click.outside="closeMenu(false)"
        @keydown.escape.prevent.stop="closeMenu()"
        @keydown.tab.prevent.stop="closeMenu()"
        @keydown="$hot.nav($event); $hot.type($event)"
        :id="$id('hot-menu')"
        :aria-labelledby="$id('hot-menu-trigger')"
        role="menu"
        aria-orientation="vertical"
        tabindex="-1"
        data-slot="dropdown-menu-content"
        data-side="<?= e($side) ?>"
        :data-state="open ? 'open' : 'closed'"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        <?= $attributes->twMerge('bg-popover text-popover-foreground fixed z-50 max-h-96 min-w-[8rem] origin-top overflow-x-hidden overflow-y-auto rounded-md border p-1 shadow-md outline-none') ?>
    >
        <?= $slot ?>
    </div>
</template>
