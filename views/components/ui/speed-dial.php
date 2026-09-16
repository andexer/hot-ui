<?php

declare(strict_types=1);

extract(props($__ctx, [
    'actions' => [],
    'direction' => 'up',
    'open' => false,
    'icon' => 'plus',
    'label' => 'Open actions',
]));

$isUp = $direction !== 'down';
$stackClasses = $isUp
    ? 'bottom-full mb-3 flex-col-reverse'
    : 'top-full mt-3 flex-col';

$items = array_values($actions);

$count = count($items);
$rows = [];
foreach ($items as $i => $action) {
    $delayIndex = $isUp ? ($count - 1 - $i) : $i;
    $rows[] = [
        'tag' => ! empty($action['href']) ? 'a' : 'button',
        'href' => ! empty($action['href']) ? safe_url((string) $action['href']) : null,
        'ariaLabel' => $action['label'] ?? ('Action '.($i + 1)),
        'hasLabel' => ! empty($action['label']),
        'label' => $action['label'] ?? '',
        'icon' => $action['icon'] ?? null,
        'delayMs' => $delayIndex * 40,
    ];
}
?>
<div
    data-slot="speed-dial"
    x-data="{
        open: <?= js((bool) $open) ?>,
        count: <?= js($count) ?>,
        toggle() { this.open = !this.open; },
        close() { this.open = false; },
    }"
    @keydown.escape.window="open && (close(), $refs.fab.focus())"
    @click.outside="close()"
    <?= $attributes->twMerge('relative inline-flex flex-col items-center motion-reduce:transition-none') ?>
>
    <div
        class="absolute <?= $stackClasses ?> flex items-center gap-3"
        :class="open ? '' : 'pointer-events-none'"
    >
        <?php foreach ($rows as $row): ?>
            <<?= $row['tag'] ?><?php if ($row['tag'] === 'a'): ?>
                href="<?= e((string) $row['href']) ?>"
            <?php else: ?>
                type="button"
            <?php endif; ?>
                aria-label="<?= e((string) $row['ariaLabel']) ?>"
                :tabindex="open ? 0 : -1"
                @click="close()"
                x-show="open"
                x-transition:enter="transition ease-out duration-200 motion-reduce:transition-none"
                x-transition:enter-start="opacity-0 scale-75 <?= $isUp ? 'translate-y-2' : '-translate-y-2' ?>"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150 motion-reduce:transition-none"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-75 <?= $isUp ? 'translate-y-2' : '-translate-y-2' ?>"
                style="transition-delay: <?= e((string) $row['delayMs']) ?>ms"
                class="group/action inline-flex items-center justify-end gap-2 outline-none"
            >
                <?php if ($row['hasLabel']): ?>
                    <span class="bg-popover text-popover-foreground pointer-events-none rounded-md border px-2.5 py-1 text-xs font-medium whitespace-nowrap shadow-sm">
                        <?= e((string) $row['label']) ?>
                    </span>
                <?php endif; ?>
                <span class="bg-card text-foreground inline-flex size-10 shrink-0 items-center justify-center rounded-full border shadow-sm transition-colors group-hover/action:bg-accent group-hover/action:text-accent-foreground group-focus-visible/action:ring-ring/50 group-focus-visible/action:ring-[3px]">
                    <?php if (! empty($row['icon'])): ?>
                        <i data-lucide="<?= e((string) $row['icon']) ?>" class="size-5" aria-hidden="true"></i>
                    <?php endif; ?>
                </span>
            </<?= $row['tag'] ?>>
        <?php endforeach; ?>
    </div>

    <button
        x-ref="fab"
        type="button"
        aria-label="<?= e($label) ?>"
        aria-haspopup="menu"
        :aria-expanded="open ? 'true' : 'false'"
        @click="toggle()"
        class="bg-primary text-primary-foreground inline-flex size-14 shrink-0 cursor-pointer items-center justify-center rounded-full shadow-lg transition-colors outline-none hover:bg-primary/90 focus-visible:ring-ring/50 focus-visible:ring-[3px]"
    >
        <i data-lucide="<?= e($icon) ?>" class="size-6 transition-transform duration-200 ease-out motion-reduce:transition-none" ::class="open ? 'rotate-45' : 'rotate-0'" aria-hidden="true"></i>
    </button>
</div>
