<?php

declare(strict_types=1);

extract(props($__ctx, [
    'row' => [],
    'columns' => [],
    'path' => '0',
    'depth' => 0,
]));

$columns = array_values($columns);
$children = $row['children'] ?? [];
$hasChildren = is_array($children) && count($children) > 0;

$indent = $depth * 1.25;
$level = $depth + 1;

$alignClass = fn ($align) => match ($align) {
    'right' => 'text-end',
    'center' => 'text-center',
    default => 'text-start',
};
?>
<tr
    data-slot="tree-table-row"
    <?php if ($depth > 0): ?>x-show="isVisible(<?= js($path) ?>)" x-cloak<?php endif; ?>
    class="hover:bg-muted/50 border-b transition-colors last:border-0"
>
    <?php foreach ($columns as $colIndex => $col): ?>
        <?php
            $key = $col['key'] ?? '';
            $value = $row[$key] ?? '';
            $isTreeCol = $colIndex === 0;
        ?>
        <td
            class="<?= classes([
                'px-4 py-2.5 align-middle',
                $alignClass($col['align'] ?? null),
            ]) ?>"
            <?php if ($isTreeCol): ?>style="padding-inline-start: <?= e(1 + $indent) ?>rem;"<?php endif; ?>
        >
            <?php if ($isTreeCol): ?>
                <div class="flex items-center gap-1.5">
                    <?php if ($hasChildren): ?>
                        <button
                            type="button"
                            @click="toggle(<?= js($path) ?>)"
                            :aria-expanded="isOpen(<?= js($path) ?>) ? 'true' : 'false'"
                            aria-label="Toggle row"
                            class="text-muted-foreground hover:text-foreground focus-visible:ring-ring/50 -ms-1 flex size-5 shrink-0 cursor-pointer items-center justify-center rounded outline-none focus-visible:ring-[3px] [&[aria-expanded=true]>svg]:rotate-90"
                        >
                            <i
                                data-lucide="chevron-right"
                                class="size-4 transition-transform duration-150 rtl:-scale-x-100"
                                aria-hidden="true"
                            ></i>
                        </button>
                    <?php else: ?>
                        <span class="size-5 shrink-0" aria-hidden="true"></span>
                    <?php endif; ?>
                    <span class="truncate"><?= e($value) ?></span>
                </div>
            <?php else: ?>
                <?= e($value) ?>
            <?php endif; ?>
        </td>
    <?php endforeach; ?>
</tr>

<?php if ($hasChildren): ?>
    <?php foreach (array_values($children) as $childIndex => $childRow): ?>
        <?= $this->uiTreeTableRow([
            'row' => $childRow,
            'columns' => $columns,
            'path' => $path.'.'.$childIndex,
            'depth' => $depth + 1,
        ]) ?>
    <?php endforeach; ?>
<?php endif; ?>
