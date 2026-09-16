<?php

declare(strict_types=1);

extract(props($__ctx, [
    'tone' => 'default',
    'dismissible' => true,
    'id' => null,
    'persist' => false,
]));

$tones = [
    'default' => 'bg-muted text-foreground border-border',
    'primary' => 'bg-primary text-primary-foreground border-transparent',
    'info' => 'bg-info/10 text-info border-info/25',
    'success' => 'bg-success/10 text-success border-success/25',
    'warning' => 'bg-warning/10 text-warning border-warning/25',
    'danger' => 'bg-destructive/10 text-destructive border-destructive/25',
];
$cls = $tones[$tone] ?? $tones['default'];
$storeKey = ($persist && $id) ? 'hot-banner-'.$id : null;
$showInit = $storeKey ? "localStorage.getItem('{$storeKey}') !== '1'" : 'true';
?>
<div
    data-slot="banner"
    x-data="{ show: <?= e($showInit) ?>, dismiss() { this.show = false;<?php if ($storeKey): ?> localStorage.setItem('<?= e($storeKey) ?>', '1');<?php endif; ?> } }"
    x-show="show"
    x-cloak
    role="region"
    aria-label="Announcement"
    <?= $attributes->twMerge('relative flex w-full items-center gap-3 border-b px-4 py-2.5 text-sm '.$cls) ?>
>
    <div class="flex flex-1 flex-wrap items-center justify-center gap-x-3 gap-y-1">
        <?= $slot ?>
    </div>
<?php if ($dismissible): ?>
    <button
        type="button"
        @click="dismiss()"
        aria-label="Dismiss"
        class="shrink-0 rounded-md p-1 opacity-70 transition-opacity outline-none hover:opacity-100 focus-visible:ring-2 focus-visible:ring-current/40"
    >
        <i data-lucide="x" class="size-4" aria-hidden="true"></i>
    </button>
<?php endif; ?>
</div>
