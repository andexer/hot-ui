<?php

declare(strict_types=1);

extract(props($__ctx, [
    'title' => null,
    'description' => null,
    'separator' => false,
    'as' => 'h1',
]));

$hasTitle = trim((string) $title) !== '' || trim((string) $slot) !== '';
$hasDescription = trim((string) $description) !== '';
?>
<div
    data-slot="page-header"
    <?= $attributes->twMerge(($separator ? 'border-b pb-6 ' : '').'flex flex-col gap-4') ?>
>
    <?php if (isset($breadcrumb)): ?>
        <div><?= $breadcrumb ?></div>
    <?php endif; ?>

    <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-col gap-1.5">
            <?php if ($hasTitle): ?>
                <<?= $as ?> class="text-2xl font-bold tracking-tight text-balance text-foreground sm:text-3xl">
                    <?= e($title ?? '') ?><?= $slot ?>
                </<?= $as ?>>
            <?php endif; ?>

            <?php if ($hasDescription): ?>
                <p class="text-muted-foreground text-balance"><?= e($description) ?></p>
            <?php endif; ?>
        </div>

        <?php if (isset($actions)): ?>
            <div class="flex shrink-0 flex-wrap items-center gap-2"><?= $actions ?></div>
        <?php endif; ?>
    </div>
</div>
