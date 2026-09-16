<?php

declare(strict_types=1);

extract(props($__ctx, [
    'to' => null,
    'expired' => 'Expired',
    'labels' => ['days' => 'Days', 'hours' => 'Hrs', 'minutes' => 'Min', 'seconds' => 'Sec'],
]));

$targetMs = $to ? (int) (new \DateTimeImmutable((string) $to))->format('Uv') : null;
?>
<div
    data-slot="countdown"
    role="timer"
    aria-live="off"
    x-data="hotCountdown({ target: <?= js($targetMs) ?> })"
    <?= $attributes->twMerge('inline-flex items-center gap-2') ?>
>
    <span x-show="done" x-cloak data-slot="countdown-expired" class="text-muted-foreground text-sm font-medium"><?= trim((string) $slot) !== '' ? $slot : e($expired) ?></span>

    <div x-show="!done" class="flex items-center gap-2">
        <?php foreach (['days', 'hours', 'minutes', 'seconds'] as $unit): ?>
            <div data-slot="countdown-unit" class="bg-card flex min-w-[3.25rem] flex-col items-center rounded-lg border px-2 py-1.5 shadow-xs">
                <span class="text-xl font-bold tabular-nums" x-text="pad(<?= e($unit) ?>)">00</span>
                <span class="text-muted-foreground text-[10px] font-medium tracking-wide uppercase"><?= e($labels[$unit] ?? ucfirst($unit)) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
