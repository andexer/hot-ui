<?php

declare(strict_types=1);

extract(props($__ctx, [
    'status' => 'online',
    'size' => 'default',
    'pulse' => false,
    'label' => null,
    'showLabel' => false,
]));

static $stylesEmitted = false;
$emitStyles = ! $stylesEmitted;
$stylesEmitted = true;

$pulse = filter_var($pulse, FILTER_VALIDATE_BOOLEAN);
$showLabel = filter_var($showLabel, FILTER_VALIDATE_BOOLEAN);

$status = in_array($status, ['online', 'away', 'busy', 'offline'], true) ? $status : 'online';

$colors = [
    'online'  => 'bg-emerald-500',
    'away'    => 'bg-amber-500',
    'busy'    => 'bg-destructive',
    'offline' => 'bg-muted-foreground/40',
];
$dotColor = $colors[$status];

$labels = [
    'online'  => 'Online',
    'away'    => 'Away',
    'busy'    => 'Busy',
    'offline' => 'Offline',
];
$text = $label ?? $labels[$status];

$sizes = ['sm' => 'size-2', 'default' => 'size-2.5', 'lg' => 'size-3.5'];
$dotSize = $sizes[$size] ?? $sizes['default'];

$showPing = $pulse && $status === 'online';
?>
<?php if ($emitStyles): ?>
<style>
    @media (prefers-reduced-motion: reduce) {
        [data-slot="presence"] .hot-presence-ping { animation: none !important; }
    }
</style>
<?php endif; ?>
<span
    data-slot="presence"
    data-status="<?= e($status) ?>"
    <?= $attributes->twMerge('relative inline-flex items-center gap-1.5') ?>
>
    <span class="relative inline-flex <?= e($dotSize) ?> shrink-0">
        <?php if ($showPing): ?>
            <span class="hot-presence-ping absolute inline-flex h-full w-full animate-ping rounded-full <?= e($dotColor) ?> opacity-75" aria-hidden="true"></span>
        <?php endif; ?>
        <span class="<?= classes([
            'ring-background relative inline-flex rounded-full ring-2',
            $dotSize,
            $dotColor,
        ]) ?>" aria-hidden="true"></span>
    </span>

    <?php if ($showLabel): ?>
        <span class="text-sm text-foreground"><?= e($text) ?></span>
    <?php else: ?>
        <span class="sr-only"><?= e($text) ?></span>
    <?php endif; ?>
</span>
