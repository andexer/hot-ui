<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<template x-teleport="body">
    <div
        x-hot-dialog-layer
        x-show="open"
        x-cloak
        x-ref="submenu"
        x-init="_menu = $el"
        x-hot-anchor.right-start.offset.4.no-size="$refs.subtrigger"
        @mouseenter="cancelClose()"
        @mouseleave="closeSoon()"
        @keydown.escape.prevent.stop="closeMenu()"
        @keydown.left.prevent.stop="closeMenu()"
        @keydown.stop="$hot.nav($event); $hot.type($event)"
        :id="$id('hot-submenu')"
        :aria-labelledby="$id('hot-submenu-trigger')"
        role="menu"
        aria-orientation="vertical"
        tabindex="-1"
        data-slot="menubar-sub-content"
        :data-state="open ? 'open' : 'closed'"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        <?= $attributes->twMerge('bg-popover text-popover-foreground fixed z-50 min-w-[8rem] origin-top-left overflow-hidden rounded-md border p-1 shadow-lg outline-none') ?>
    >
        <?= $slot ?>
    </div>
</template>
