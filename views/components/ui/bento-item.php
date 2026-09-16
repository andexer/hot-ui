<?php

declare(strict_types=1);

extract(props($__ctx, [
    'title' => null,
    'description' => null,
    'icon' => null,
    'colSpan' => 1,
    'rowSpan' => 1,
]));

$colSpanClasses = [
    1 => '',
    2 => 'lg:col-span-2',
    3 => 'lg:col-span-3',
];

$rowSpanClasses = [
    1 => '',
    2 => 'row-span-2',
];

$span = trim(($colSpanClasses[(int) $colSpan] ?? '').' '.($rowSpanClasses[(int) $rowSpan] ?? ''));
?>
<div
    data-slot="bento-item"
    <?= $attributes->twMerge('bg-card text-card-foreground flex flex-col gap-3 rounded-xl border p-6 transition-colors hover:bg-muted/40 '.$span) ?>
>
<?php if ($icon || isset($leading)): ?>
    <span class="bg-muted text-muted-foreground flex size-10 shrink-0 items-center justify-center rounded-lg" aria-hidden="true">
<?php if (isset($leading)): ?>
        <?= $leading ?>
<?php else: ?>
        <i data-lucide="<?= e($icon) ?>" class="size-5"></i>
<?php endif; ?>
    </span>
<?php endif; ?>

<?php if ($title !== null): ?>
    <h3 class="font-semibold leading-none"><?= e($title) ?></h3>
<?php endif; ?>

<?php if ($description !== null): ?>
    <p class="text-muted-foreground text-sm"><?= e($description) ?></p>
<?php endif; ?>

    <?= $slot ?>
</div>
