<?php

declare(strict_types=1);

extract(props($__ctx, [
    'title' => 'Command Palette',
    'description' => 'Search for a command to run...',
]));
?>
<div data-slot="command-dialog" x-data="hotCommandPalette()" x-id="['hot-command-dialog']" <?= $attributes ?>>
    <?php if (isset($trigger)): ?>
        <div @click="open = true" x-hot-trigger="{ haspopup: 'dialog', controls: $id('hot-command-dialog') }"><?= $trigger ?></div>
    <?php endif; ?>

    <template x-teleport="body">
        <div x-show="open" x-cloak class="fixed inset-0 z-50">
            <div
                x-show="open"
                @click="open = false"
                role="presentation"
                aria-hidden="true"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black/50"
            ></div>
            <div
                x-show="open"
                @keydown.escape.window="open = false"
                x-effect="trap($el, open)"
                :id="$id('hot-command-dialog')"
                x-hot-labelledby="{ label: 'h2', description: 'p' }"
                role="dialog"
                aria-modal="true"
                tabindex="-1"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-popover text-popover-foreground fixed top-[50%] left-[50%] z-50 w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] overflow-hidden rounded-lg border p-0 shadow-lg sm:max-w-lg"
            >
                <div class="sr-only">
                    <h2><?= e($title) ?></h2>
                    <p><?= e($description) ?></p>
                </div>
                <?= $slot ?>
            </div>
        </div>
    </template>
</div>
