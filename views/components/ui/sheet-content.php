<?php

declare(strict_types=1);

extract(props($__ctx, [
    'side' => 'right',
    'showClose' => true,
    'closeOnOverlay' => true,
]));

$sideClasses = [
    'right' => 'inset-y-0 right-0 h-full w-3/4 border-l sm:max-w-sm',
    'left' => 'inset-y-0 left-0 h-full w-3/4 border-r sm:max-w-sm',
    'top' => 'inset-x-0 top-0 h-auto border-b',
    'bottom' => 'inset-x-0 bottom-0 h-auto border-t',
];

$enterStart = [
    'right' => 'translate-x-full',
    'left' => '-translate-x-full',
    'top' => '-translate-y-full',
    'bottom' => 'translate-y-full',
];

$base = 'bg-background fixed z-50 flex flex-col gap-4 shadow-lg';
$classes = $base.' '.($sideClasses[$side] ?? $sideClasses['right']);
$start = $enterStart[$side] ?? $enterStart['right'];
?>
<template x-teleport="body">
    <div x-show="open" x-cloak class="fixed inset-0 z-50">
        <div
            x-show="open"
            <?php if ($closeOnOverlay): ?>@click="open = false"<?php endif; ?>
            role="presentation"
            aria-hidden="true"
            data-slot="sheet-overlay"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 bg-black/50"
        ></div>

        <div
            x-show="open"
            x-effect="trap($el, open)"
            @keydown.escape.window="open = false"
            :id="$id('hot-sheet')"
            x-hot-labelledby="{ label: '[data-slot=sheet-title]', description: '[data-slot=sheet-description]' }"
            role="dialog"
            aria-modal="true"
            tabindex="-1"
            data-slot="sheet-content"
            data-side="<?= e($side) ?>"
            x-transition:enter="transition ease-in-out duration-500"
            x-transition:enter-start="<?= e($start) ?>"
            x-transition:enter-end="translate-x-0 translate-y-0"
            x-transition:leave="transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0 translate-y-0"
            x-transition:leave-end="<?= e($start) ?>"
            <?= $attributes->twMerge($classes) ?>
        >
            <?= $slot ?>

            <?php if ($showClose): ?>
                <button
                    type="button"
                    @click="open = false"
                    class="ring-offset-background focus:ring-ring absolute top-4 end-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus:ring-2 focus:ring-offset-2 focus:outline-hidden disabled:pointer-events-none"
                >
                    <i data-lucide="x" class="size-4" aria-hidden="true"></i>
                    <span class="sr-only">Close</span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</template>
