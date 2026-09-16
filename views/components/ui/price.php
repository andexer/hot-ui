<?php

declare(strict_types=1);

extract(props($__ctx, [
    'amount' => 0,
    'compareAt' => null,
    'currency' => '$',
    'size' => 'default',
    'showDiscount' => true,
]));

$amount = (float) $amount;
$compareAt = $compareAt === null ? null : (float) $compareAt;
$onSale = $compareAt !== null && $compareAt > $amount;

$sizes = [
    'sm' => 'text-sm',
    'default' => 'text-base',
    'lg' => 'text-2xl',
];
$current = $sizes[$size] ?? $sizes['default'];

$compareSizes = [
    'sm' => 'text-xs',
    'default' => 'text-sm',
    'lg' => 'text-base',
];
$compareCls = $compareSizes[$size] ?? $compareSizes['default'];

$fmt = static fn (float $value): string => $currency.number_format($value, 2);

$discount = $onSale && $compareAt > 0
    ? (int) round((($compareAt - $amount) / $compareAt) * 100)
    : 0;
?>
<span data-slot="price" <?= $attributes->twMerge('inline-flex items-baseline gap-2 font-medium tabular-nums') ?>>
    <span class="<?= classes([
        $current,
        'text-emerald-700 dark:text-emerald-400' => $onSale,
        'text-foreground' => ! $onSale,
    ]) ?>"><?= e($fmt($amount)) ?></span>

    <?php if ($onSale): ?>
        <s class="<?= classes([$compareCls, 'text-muted-foreground']) ?>">
            <span class="sr-only">was </span><?= e($fmt((float) $compareAt)) ?>
        </s>

        <?php if ($showDiscount && $discount > 0): ?>
            <?= $this->uiBadge(['tone' => 'success', 'variant' => 'soft', 'size' => 'sm'], '<span class="sr-only">save </span>-'.$discount.'%') ?>
        <?php endif; ?>
    <?php endif; ?>
</span>
