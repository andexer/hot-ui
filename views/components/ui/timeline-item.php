<?php

declare(strict_types=1);

extract(props($__ctx, [
    'icon' => null,
    'time' => null,
    'title' => null,
    'active' => false,
]));
?>
<li data-slot="timeline-item" <?= $attributes->twMerge('relative flex gap-4 pb-8 last:pb-0') ?>>
    <div class="relative flex flex-col items-center">
        <span
            data-slot="timeline-dot"
            class="<?= classes([
                'z-10 flex size-8 shrink-0 items-center justify-center rounded-full border',
                'bg-primary text-primary-foreground border-primary' => $active,
                'bg-background text-muted-foreground' => ! $active,
            ]) ?>"
        >
            <?php if ($icon): ?>
                <i data-lucide="<?= e($icon) ?>" class="size-4" aria-hidden="true"></i>
            <?php else: ?>
                <span class="<?= classes(['size-2 rounded-full', 'bg-primary-foreground' => $active, 'bg-muted-foreground' => ! $active]) ?>"></span>
            <?php endif; ?>
        </span>
        <span data-slot="timeline-line" class="bg-border mt-1 w-px flex-1" aria-hidden="true"></span>
    </div>

    <div class="flex-1 pt-1 pb-1">
        <?php if ($time): ?>
            <div data-slot="timeline-time" class="text-muted-foreground text-xs font-medium tabular-nums"><?= e($time) ?></div>
        <?php endif; ?>
        <?php if ($title): ?>
            <h3 data-slot="timeline-title" class="text-sm font-semibold"><?= e($title) ?></h3>
        <?php endif; ?>
        <?php if (trim((string) $slot) !== ''): ?>
            <div data-slot="timeline-content" class="text-muted-foreground mt-1 text-sm"><?= $slot ?></div>
        <?php endif; ?>
    </div>
</li>
