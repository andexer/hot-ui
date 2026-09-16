<?php

declare(strict_types=1);

extract(props($__ctx, [
    'tiers' => [],
    'rows' => [],
    'highlight' => null,
    'featureLabel' => 'Feature',
]));

$tiers = array_values($tiers);
$hl = is_int($highlight) ? $highlight : array_search($highlight, $tiers, true);
?>
<div data-slot="comparison-table" <?= $attributes->twMerge('w-full overflow-x-auto rounded-xl border') ?>>
    <table class="w-full text-sm">
        <caption class="sr-only"><?= e($featureLabel) ?> comparison across <?= e(implode(', ', $tiers)) ?></caption>
        <thead>
            <tr class="bg-muted/40 border-b">
                <th scope="col" class="text-muted-foreground px-4 py-3 text-start font-medium"><?= e($featureLabel) ?></th>
                <?php foreach ($tiers as $i => $tier): ?>
                    <th scope="col" class="<?= classes([
                        'px-4 py-3 text-center font-semibold',
                        'text-primary' => $i === $hl,
                    ]) ?>"><?= e($tier) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
                <tr class="border-b last:border-0">
                    <th scope="row" class="px-4 py-3 text-start font-medium"><?= e($row['feature'] ?? '') ?></th>
                    <?php foreach (array_values($row['values'] ?? []) as $i => $val): ?>
                        <td class="<?= classes([
                            'px-4 py-3 text-center',
                            'bg-muted/30' => $i === $hl,
                        ]) ?>">
                            <?php if ($val === true): ?>
                                <i data-lucide="check" class="text-primary mx-auto size-4" aria-label="Included"></i>
                            <?php elseif ($val === false || $val === null): ?>
                                <i data-lucide="minus" class="text-muted-foreground/40 mx-auto size-4" aria-label="Not included"></i>
                            <?php else: ?>
                                <span class="tabular-nums"><?= e($val) ?></span>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
