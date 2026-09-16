<?php

declare(strict_types=1);

extract(props($__ctx, [
    'columns' => [],
    'rows' => [],
    'copyable' => false,
]));

$columns = array_values($columns);

$collectOpen = function (array $rows, string $prefix, callable $self) {
    $ids = [];
    foreach (array_values($rows) as $i => $row) {
        $path = $prefix === '' ? (string) $i : "{$prefix}.{$i}";
        $children = $row['children'] ?? [];
        $hasChildren = is_array($children) && count($children) > 0;
        if ($hasChildren) {
            if (! empty($row['expanded'])) {
                $ids[] = $path;
            }
            $ids = array_merge($ids, $self($children, $path, $self));
        }
    }
    return $ids;
};
$openIds = $collectOpen($rows, '', $collectOpen);

$treeKey = $columns[0]['key'] ?? 'name';
$buildMarkdown = function (array $rows, string $prefix, callable $self, bool $isRoot = false) use ($treeKey) {
    $rows = array_values($rows);
    $last = count($rows) - 1;
    $out = '';
    foreach ($rows as $i => $row) {
        $isLast = $i === $last;
        $children = $row['children'] ?? [];
        $hasChildren = is_array($children) && count($children) > 0;
        $name = (string) ($row[$treeKey] ?? '');
        if ($hasChildren) {
            $name = rtrim($name, '/').'/';
        }
        if ($isRoot) {

            $out .= $name."\n";
            if ($hasChildren) {
                $out .= $self($children, '', $self);
            }
        } else {
            $out .= $prefix.($isLast ? '└── ' : '├── ').$name."\n";
            if ($hasChildren) {
                $out .= $self($children, $prefix.($isLast ? '    ' : '│   '), $self);
            }
        }
    }
    return $out;
};
$markdown = $copyable ? $buildMarkdown($rows, '', $buildMarkdown, true) : '';

$alignClass = fn ($align) => match ($align) {
    'right' => 'text-end',
    'center' => 'text-center',
    default => 'text-start',
};
?>
<div
    data-slot="tree-table"
    x-data="hotTreeTable({
        expanded: <?= js(array_fill_keys($openIds, true)) ?>,
        markdown: <?= js($markdown) ?>,
    })"
    <?= $attributes->twMerge('w-full overflow-x-auto rounded-lg border') ?>
>
    <?php if ($copyable): ?>
        <div class="flex justify-end border-b px-2 py-1.5">
            <?= $this->uiButton(['type' => 'button', 'variant' => 'ghost', 'size' => 'sm', 'class' => 'gap-1.5', '@click' => 'copyTree()', 'aria-label' => 'Copy tree as markdown'], function (): void { ?>
                <i data-lucide="copy" class="size-3.5" x-show="!copied"></i>
                <i data-lucide="check" class="size-3.5 text-emerald-500" x-show="copied" x-cloak></i>
                <span x-text="copied ? 'Copied' : 'Copy tree'"></span>
            <?php }) ?>
        </div>
    <?php endif; ?>

    <table class="w-full caption-bottom text-sm">
        <thead>
            <tr class="bg-muted/40 border-b">
                <?php foreach ($columns as $col): ?>
                    <th
                        scope="col"
                        class="<?= classes([
                            'text-muted-foreground px-4 py-3 font-medium',
                            $alignClass($col['align'] ?? null),
                        ]) ?>"
                    ><?= e($col['label'] ?? '') ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach (array_values($rows) as $i => $row): ?>
                <?= $this->uiTreeTableRow([
                    'row' => $row,
                    'columns' => $columns,
                    'path' => (string) $i,
                    'depth' => 0,
                ]) ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
