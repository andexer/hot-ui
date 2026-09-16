<?php

declare(strict_types=1);

extract(props($__ctx, [
    'align' => 'start',
    'side' => 'bottom',
    'sideOffset' => 8,
]));

$placement = $side.($align === 'center' ? '' : '-'.$align);
$anchorAttr = 'x-hot-anchor.'.$placement.'.offset.'.$sideOffset.'.no-size="$refs.trigger"';
?>
<template x-teleport="body">
    <div
        x-hot-dialog-layer
        x-show="active === id"
        x-cloak
        <?= $anchorAttr ?>
        @click.outside="if (active === id) closeMenu(false)"
        @keydown.escape.prevent.stop="closeMenu()"
        @keydown.tab.prevent.stop="closeMenu(false)"
        @keydown.left.prevent.stop="moveTrigger(-1, id)"
        @keydown.right.prevent.stop="moveTrigger(1, id)"
        @keydown="$hot.nav($event); $hot.type($event)"
        :id="id"
        :aria-labelledby="id + '-trigger'"
        role="menu"
        aria-orientation="vertical"
        tabindex="-1"
        data-slot="menubar-content"
        :data-state="active === id ? 'open' : 'closed'"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        <?= $attributes->twMerge('bg-popover text-popover-foreground fixed z-50 min-w-[12rem] origin-top-left overflow-hidden rounded-md border p-1 shadow-md outline-none') ?>
    >
        <?= $slot ?>
    </div>
</template>
