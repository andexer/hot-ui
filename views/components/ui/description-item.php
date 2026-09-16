<?php

declare(strict_types=1);

use Components\Support\Slot;

extract(aware($__ctx, [
    'term' => null,
    'layout' => 'horizontal',
    'bordered' => false,
]), EXTR_SKIP);

$isHorizontal = $layout === 'horizontal';

$rowClasses = $isHorizontal
    ? 'grid gap-1 sm:grid-cols-3 sm:gap-4'
    : 'flex flex-col gap-1';

if ($bordered) {
    $rowClasses .= ' px-4 py-3';
}
?>
<div data-slot="description-item" <?= $attributes->twMerge($rowClasses) ?>>
    <dt class="<?= classes(['text-muted-foreground text-sm', 'sm:col-span-1' => $isHorizontal]) ?>">
        <?php if ($term instanceof Slot) { echo $term; } elseif ($term !== null) { echo e($term); } ?>
    </dt>
    <dd class="<?= classes(['text-foreground text-sm', 'sm:col-span-2' => $isHorizontal]) ?>">
        <?= $slot ?>
    </dd>
</div>
