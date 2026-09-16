<?php

declare(strict_types=1);

extract(props($__ctx, [
    'columns' => [],
    'rows' => [],
    'searchable' => true,
    'searchKey' => null,
    'searchPlaceholder' => 'Search...',
    'selectable' => true,
    'pageSize' => 5,
    'rowKey' => 'id',
    'actionsLabel' => 'Actions',
    'stickyActions' => false,
]));

$cols = [];
foreach ($columns as $c) {
    $cols[] = [
        'key' => $c['key'] ?? '',
        'label' => $c['label'] ?? ucfirst($c['key'] ?? ''),
        'sortable' => $c['sortable'] ?? true,
        'class' => $c['class'] ?? '',
    ];
}
$searchKeys = $searchKey ? [$searchKey] : array_map(static fn (array $c): string => (string) $c['key'], $cols);
?>
<div
    data-slot="data-table"
    x-data="hotDataTable({
        pageSize: <?= js((int) $pageSize) ?>,
        rows: <?= js(array_values(is_array($rows) ? $rows : [])) ?>,
        searchKeys: <?= js($searchKeys) ?>,
    })"
    <?= $attributes->twMerge('w-full') ?>
>
    <?php if ($searchable): ?>
        <div class="flex items-center gap-2 pb-4">
            <?= $this->uiInput(['type' => 'text', 'x-model' => 'q', 'placeholder' => $searchPlaceholder, 'class' => 'max-w-xs']) ?>
        </div>
    <?php endif; ?>

    <div class="relative w-full overflow-x-auto rounded-md border">
        <table data-slot="table" class="w-full caption-bottom text-sm">
            <thead data-slot="table-header" class="[&_tr]:border-b">
                <tr class="hover:bg-muted/50 border-b transition-colors">
                    <?php if ($selectable): ?>
                        <th scope="col" class="h-10 w-10 px-2 text-start align-middle">
                            <span class="sr-only">Select</span>
                            <button type="button" role="checkbox" aria-label="Select all rows" @click="toggleAll()" :aria-checked="allPageSelected" :data-state="allPageSelected ? 'checked' : 'unchecked'"
                                class="border-input data-[state=checked]:bg-primary data-[state=checked]:border-primary data-[state=checked]:text-primary-foreground focus-visible:border-ring focus-visible:ring-ring/50 flex size-4 items-center justify-center rounded-[4px] border shadow-xs outline-none focus-visible:ring-[3px]">
                                <i data-lucide="check" class="size-3.5" x-show="allPageSelected" x-cloak aria-hidden="true"></i>
                            </button>
                        </th>
                    <?php endif; ?>
                    <?php foreach ($cols as $col): ?>
                        <th scope="col"<?php if ($col['sortable']): ?> :aria-sort="sortKey === '<?= e($col['key']) ?>' ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'"<?php endif; ?>
                            class="text-foreground h-10 px-2 text-start align-middle font-medium whitespace-nowrap <?= e($col['class']) ?>">
                            <?php if ($col['sortable']): ?>
                                <button type="button" @click="toggleSort('<?= e($col['key']) ?>')" class="hover:text-foreground focus-visible:border-ring focus-visible:ring-ring/50 -ms-2 inline-flex items-center gap-1 rounded-md px-2 py-1 transition-colors outline-none focus-visible:ring-[3px]">
                                    <?= e($col['label']) ?>
                                    <span class="text-muted-foreground inline-flex" aria-hidden="true">
                                        <span x-show="sortKey === '<?= e($col['key']) ?>' && sortDir === 'asc'" x-cloak><i data-lucide="chevron-up" class="size-3.5"></i></span>
                                        <span x-show="sortKey === '<?= e($col['key']) ?>' && sortDir === 'desc'" x-cloak><i data-lucide="chevron-down" class="size-3.5"></i></span>
                                        <span x-show="sortKey !== '<?= e($col['key']) ?>'"><i data-lucide="chevrons-up-down" class="size-3.5 opacity-50"></i></span>
                                    </span>
                                </button>
                            <?php else: ?>
                                <?= e($col['label']) ?>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                    <?php if (isset($actions)): ?>
                        <th scope="col" class="<?= classes([
                            'text-foreground bg-background h-10 px-2 text-end align-middle font-medium whitespace-nowrap',
                            'sticky end-0 border-s' => $stickyActions,
                        ]) ?>">
                            <span class="sr-only"><?= e($actionsLabel) ?></span>
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody data-slot="table-body" class="[&_tr:last-child]:border-0">
                <template x-for="item in paged" :key="item.r['<?= e($rowKey) ?>'] ?? item.i">
                    <tr class="hover:bg-muted/50 data-[state=selected]:bg-muted border-b transition-colors" :data-state="selected.includes(item.i) ? 'selected' : null">
                        <?php if ($selectable): ?>
                            <td class="w-10 px-2 align-middle">
                                <button type="button" role="checkbox" aria-label="Select row" @click="toggleRow(item.i)" :aria-checked="selected.includes(item.i)" :data-state="selected.includes(item.i) ? 'checked' : 'unchecked'"
                                    class="border-input data-[state=checked]:bg-primary data-[state=checked]:border-primary data-[state=checked]:text-primary-foreground focus-visible:border-ring focus-visible:ring-ring/50 flex size-4 items-center justify-center rounded-[4px] border shadow-xs outline-none focus-visible:ring-[3px]">
                                    <i data-lucide="check" class="size-3.5" x-show="selected.includes(item.i)" x-cloak aria-hidden="true"></i>
                                </button>
                            </td>
                        <?php endif; ?>
                        <?php foreach ($cols as $col): ?>
                            <td class="p-2 align-middle whitespace-nowrap <?= e($col['class']) ?>" x-text="item.r['<?= e($col['key']) ?>']"></td>
                        <?php endforeach; ?>
                        <?php if (isset($actions)): ?>
                            <td class="<?= classes([
                                'bg-background p-2 align-middle whitespace-nowrap',
                                'sticky end-0 border-s' => $stickyActions,
                            ]) ?>">
                                <div class="flex items-center justify-end gap-1"><?= $actions ?></div>
                            </td>
                        <?php endif; ?>
                    </tr>
                </template>
                <tr x-show="paged.length === 0">
                    <td colspan="<?= count($cols) + ($selectable ? 1 : 0) + (isset($actions) ? 1 : 0) ?>" class="text-muted-foreground h-24 text-center align-middle">No results.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between gap-4 pt-4">
        <p class="text-muted-foreground text-sm">
            <?php if ($selectable): ?>
                <span x-text="selected.length"></span> of <span x-text="rows.length"></span> row(s) selected.
            <?php else: ?>
                <span x-text="sorted.length"></span> result(s).
            <?php endif; ?>
        </p>
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium">Page <span x-text="page"></span> of <span x-text="pageCount"></span></span>
            <?= $this->uiButton(['variant' => 'outline', 'size' => 'sm', 'x-bind:disabled' => 'page === 1', '@click' => 'prev()'], 'Previous') ?>
            <?= $this->uiButton(['variant' => 'outline', 'size' => 'sm', 'x-bind:disabled' => 'page === pageCount', '@click' => 'next()'], 'Next') ?>
        </div>
    </div>
</div>
