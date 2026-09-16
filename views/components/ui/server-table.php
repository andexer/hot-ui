<?php

declare(strict_types=1);

extract(props($__ctx, [
    'columns' => [],
    'rows' => [],
    'rowKey' => 'id',

    'sort' => null,
    'direction' => 'asc',

    'actions' => [],
    'actionsView' => null,
    'actionsLabel' => 'Actions',
    'actionsMode' => 'inline',
    'stickyActions' => false,

    'cellViews' => [],

    'selectable' => false,
    'selectModel' => 'selected',

    'searchable' => false,
    'searchModel' => 'search',
    'searchPlaceholder' => 'Search...',

    'perPageModel' => 'perPage',
    'perPageOptions' => [],
    'perPageLabel' => 'Rows per page',

    'toggleableColumns' => false,
    'visibleColumns' => null,
    'toggleColumnMethod' => 'toggleColumn',
    'columnsLabel' => 'Columns',

    'caption' => null,
    'captionVisible' => false,
    'emptyText' => 'No results.',
    'emptyIcon' => 'search-x',
    'responsive' => 'scroll',
    'variant' => 'default',
    'paginate' => true,
]));

$dataGet = static function ($target, string $path) {
    $segments = $path === '' ? [] : explode('.', $path);
    foreach ($segments as $segment) {
        if (is_array($target)) {
            if (! array_key_exists($segment, $target)) {
                return null;
            }
            $target = $target[$segment];
        } elseif (is_object($target)) {
            if (isset($target->{$segment})) {
                $target = $target->{$segment};
            } elseif (method_exists($target, $segment)) {
                $target = $target->{$segment}();
            } elseif (method_exists($target, '__get')) {
                $target = $target->{$segment};
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    return $target;
};

$headlineOf = static function (string $key): string {
    $spaced = preg_replace('/[_\-\s]+/', ' ', trim($key)) ?? '';
    $spaced = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $spaced) ?? '';
    $words = [];
    foreach (explode(' ', $spaced) as $word) {
        if ($word !== '') {
            $words[] = ucfirst(strtolower($word));
        }
    }

    return implode(' ', $words);
};

$allCols = [];
foreach ($columns as $c) {
    $allCols[] = [
        'key' => $c['key'] ?? '',
        'label' => $c['label'] ?? $headlineOf((string) ($c['key'] ?? '')),
        'sortable' => $c['sortable'] ?? false,
        'align' => $c['align'] ?? 'left',
        'class' => $c['class'] ?? '',
        'width' => $c['width'] ?? null,
        'hideable' => $c['hideable'] ?? true,
    ];
}

$cols = $visibleColumns === null
    ? $allCols
    : array_values(array_filter($allCols, static fn (array $c): bool => ! $c['hideable'] || in_array($c['key'], $visibleColumns, true)));

$isPaginator = is_object($rows) && method_exists($rows, 'items');
$items = $isPaginator
    ? $rows->items()
    : (is_array($rows) ? $rows : (is_object($rows) && method_exists($rows, 'all') ? $rows->all() : (array) $rows));

$hasActions = $actions !== [] || $actionsView !== null;
$colspan = count($cols) + ($selectable ? 1 : 0) + ($hasActions ? 1 : 0);

$alignClass = static function (string $a): string {
    if ($a === 'center') {
        return 'text-center';
    }
    if ($a === 'right' || $a === 'end') {
        return 'text-end';
    }

    return 'text-start';
};

$actionPayloadJs = static function (array $action, $row) use ($rowKey, $dataGet): string {
    $payload = [
        'method' => (string) ($action['method'] ?? ''),
        'args' => $action['params'] ?? [$dataGet($row, $rowKey)],
    ];

    return (string) json_encode($payload, JSON_UNESCAPED_SLASHES);
};

$actionHref = static function (array $action, $row) use ($rowKey, $dataGet) {
    $h = $action['href'] ?? null;
    if ($h instanceof Closure) {
        return $h($row);
    }
    if (is_string($h)) {
        return str_replace('{id}', (string) $dataGet($row, $rowKey), $h);
    }

    return null;
};

$stack = $responsive === 'stack';

if ($variant === 'card') {
    $wrapperClass = $stack
        ? 'max-md:border-0 max-md:bg-transparent max-md:shadow-none md:bg-card md:rounded-lg md:border md:shadow-xs'
        : 'bg-card rounded-lg border shadow-xs';
} else {
    $wrapperClass = $stack ? 'md:rounded-md md:border' : 'rounded-md border';
}

$hasToolbar = $searchable || $perPageOptions !== [] || $toggleableColumns || trim((string) ($toolbar ?? '')) !== '';
?>
<div
    data-slot="server-table"
    <?php if ($selectable): ?>
    x-data="{
        selected: [],
        toggleAll(checked) {
            this.$root.querySelectorAll('tbody input[type=checkbox][data-row-select]').forEach(c => {
                if (c.checked !== checked) { c.checked = checked; c.dispatchEvent(new Event('change', { bubbles: true })); }
            });
        },
    }"
    <?php endif; ?>
    <?= $attributes->twMerge('w-full') ?>
>
    <?php if ($hasToolbar): ?>
        <div class="flex flex-wrap items-center gap-2 pb-4">
            <?php if ($searchable): ?>
                <?= $this->uiInput([
                    'type' => 'search',
                    'placeholder' => $searchPlaceholder,
                    'aria-label' => $searchPlaceholder,
                    'x-on:input.debounce.300ms' => "\$dispatch('table:search', { model: '".addslashes((string) $searchModel)."', value: \$event.target.value })",
                    'class' => 'max-w-xs',
                ]) ?>
            <?php endif; ?>

            <?= $toolbar ?? '' ?>

            <?php if ($perPageOptions !== [] || $toggleableColumns): ?>
                <div class="ms-auto flex items-center gap-2">
                    <?php if ($perPageOptions !== []):

                        $perPage = [];
                        foreach ($perPageOptions as $pk => $plabel) {
                            if (is_int($pk) && ! is_array($plabel)) {
                                $perPage[(string) $plabel] = (string) $plabel;
                            } else {
                                $perPage[(string) $pk] = (string) $plabel;
                            }
                        } ?>
                        <?= $this->uiSelect([
                            'native' => true,
                            'options' => $perPage,
                            'aria-label' => $perPageLabel,
                            'size' => 'sm',
                            'class' => 'w-auto',
                            'x-on:change' => "\$dispatch('table:per-page', { model: '".addslashes((string) $perPageModel)."', value: \$event.target.value })",
                        ]) ?>
                    <?php endif; ?>

                    <?php if ($toggleableColumns): ?>
                        <?= $this->uiDropdownMenu([], function () use ($columnsLabel, $allCols, $visibleColumns, $toggleColumnMethod): void { ?>
                            <?= $this->uiDropdownMenuTrigger([], function () use ($columnsLabel): void { ?>
                                <?= $this->uiButton(['variant' => 'outline', 'size' => 'sm'], function () use ($columnsLabel): void { ?>
                                    <i data-lucide="columns-3" class="size-4" aria-hidden="true"></i>
                                    <span class="max-sm:sr-only"><?= e($columnsLabel) ?></span>
                                    <i data-lucide="chevron-down" class="size-4 opacity-50" aria-hidden="true"></i>
                                <?php }) ?>
                            <?php }) ?>
                            <?= $this->uiDropdownMenuContent(['align' => 'end', 'class' => 'w-56'], function () use ($allCols, $visibleColumns, $toggleColumnMethod): void {
                                foreach ($allCols as $col):
                                    if (! $col['hideable']) { continue; }
                                    $isVisible = $visibleColumns === null || in_array($col['key'], $visibleColumns, true); ?>
                                    <?= $this->uiDropdownMenuCheckboxItem([
                                        'checked' => $isVisible,
                                        'x-on:click' => "\$dispatch('table:column-toggle', { method: '".addslashes((string) $toggleColumnMethod)."', key: '".addslashes((string) $col['key'])."' })",
                                    ], e((string) $col['label'])) ?>
                                <?php endforeach;
                            }) ?>
                        <?php }) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="<?= e($wrapperClass.' '.($stack ? 'md:overflow-x-auto' : 'relative w-full overflow-x-auto')) ?>">
        <table data-slot="table" class="w-full caption-bottom text-sm <?= $stack ? 'max-md:block' : '' ?>">
            <?php if ($caption): ?>
                <caption class="<?= $captionVisible ? 'text-muted-foreground p-2 text-start text-sm' : 'sr-only' ?>"><?= e($caption) ?></caption>
            <?php endif; ?>

            <thead data-slot="table-header" class="[&_tr]:border-b <?= $stack ? 'max-md:sr-only' : '' ?>">
                <tr class="hover:bg-muted/50 border-b transition-colors">
                    <?php if ($selectable): ?>
                        <th scope="col" class="h-10 w-10 px-2 text-start align-middle">
                            <span class="sr-only">Select</span>
                            <input type="checkbox" aria-label="Select all rows on this page"
                                @change="toggleAll($event.target.checked)"
                                class="border-input text-primary focus-visible:ring-ring/50 size-4 rounded-[4px] border shadow-xs outline-none focus-visible:ring-[3px]" />
                        </th>
                    <?php endif; ?>

                    <?php foreach ($cols as $col): ?>
                        <th scope="col"
                            <?php if ($col['sortable']): ?>aria-sort="<?= e($sort === $col['key'] ? ($direction === 'asc' ? 'ascending' : 'descending') : 'none') ?>"<?php endif; ?>
                            <?php if ($col['width']): ?>style="width: <?= e((string) $col['width']) ?>"<?php endif; ?>
                            class="text-foreground h-10 px-2 align-middle font-medium whitespace-nowrap <?= e($alignClass($col['align'])) ?> <?= e((string) $col['class']) ?>">
                            <?php if ($col['sortable']): ?>
                                <button type="button" x-on:click="<?= e("\$dispatch('table:sort', { key: '".addslashes((string) $col['key'])."' })") ?>"
                                    class="hover:text-foreground focus-visible:border-ring focus-visible:ring-ring/50 -ms-2 inline-flex items-center gap-1 rounded-md px-2 py-1 transition-colors outline-none focus-visible:ring-[3px] <?= $col['align'] === 'right' ? 'flex-row-reverse' : '' ?>">
                                    <?= e((string) $col['label']) ?>
                                    <span class="text-muted-foreground inline-flex" aria-hidden="true">
                                        <?php if ($sort === $col['key']): ?>
                                            <i data-lucide="<?= e($direction === 'asc' ? 'chevron-up' : 'chevron-down') ?>" class="size-3.5"></i>
                                        <?php else: ?>
                                            <i data-lucide="chevrons-up-down" class="size-3.5 opacity-50"></i>
                                        <?php endif; ?>
                                    </span>
                                </button>
                            <?php else: ?>
                                <?= e((string) $col['label']) ?>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>

                    <?php if ($hasActions): ?>
                        <th scope="col" class="<?= classes([
                            'text-foreground bg-background h-10 px-2 text-end align-middle font-medium whitespace-nowrap',
                            'sticky end-0 border-s' => $stickyActions,
                        ]) ?>">
                            <span class="sr-only"><?= e($actionsLabel) ?></span>
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody data-slot="table-body" class="<?= $stack ? 'max-md:block md:[&_tr:last-child]:border-0' : '[&_tr:last-child]:border-0' ?>">
                <?php if ($items === []): ?>
                    <tr class="<?= $stack ? 'max-md:block' : '' ?>">
                        <td colspan="<?= $colspan ?>" class="h-24 text-center align-middle <?= $stack ? 'max-md:block' : '' ?>">
                            <div class="text-muted-foreground flex flex-col items-center justify-center gap-2 py-6">
                                <i data-lucide="<?= e((string) $emptyIcon) ?>" class="size-6 opacity-60" aria-hidden="true"></i>
                                <span><?= e($emptyText) ?></span>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                <?php foreach ($items as $row): ?>
                    <tr class="hover:bg-muted/50 data-[state=selected]:bg-muted transition-colors <?= $stack ? 'max-md:divide-border max-md:mb-3 max-md:block max-md:divide-y max-md:rounded-md max-md:border md:border-b' : 'border-b' ?>">
                        <?php if ($selectable): ?>
                            <td class="w-10 px-2 align-middle <?= $stack ? 'max-md:flex max-md:justify-end max-md:p-2' : '' ?>">
                                <input type="checkbox" data-row-select
                                    x-model="selected"
                                    value="<?= e((string) ($dataGet($row, $rowKey) ?? '')) ?>"
                                    aria-label="Select row"
                                    x-on:change="<?= e("\$dispatch('table:select', { model: '".addslashes((string) $selectModel)."', value: \$event.target.value, checked: \$event.target.checked })") ?>"
                                    class="border-input text-primary focus-visible:ring-ring/50 size-4 rounded-[4px] border shadow-xs outline-none focus-visible:ring-[3px]" />
                            </td>
                        <?php endif; ?>

                        <?php foreach ($cols as $col):
                            $cellValue = $dataGet($row, (string) $col['key']); ?>
                            <td class="<?= classes([
                                'p-2 align-middle whitespace-nowrap',
                                $alignClass($col['align']),
                                $col['class'],
                                'max-md:flex max-md:items-center max-md:justify-between max-md:gap-4 max-md:whitespace-normal max-md:px-4 max-md:py-2.5 max-md:text-end' => $stack,
                            ]) ?>">
                                <?php if ($stack): ?>
                                    <span class="text-muted-foreground font-medium md:hidden" aria-hidden="true"><?= e((string) $col['label']) ?></span>
                                <?php endif; ?>
                                <?php if (isset($cellViews[$col['key']])): ?>
                                    <?= ui()->render($cellViews[$col['key']], ['value' => $cellValue, 'row' => $row]) ?>
                                <?php else: ?>
                                    <?= e($cellValue === null ? '' : (string) $cellValue) ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>

                        <?php if ($hasActions): ?>
                            <td class="<?= classes([
                                'bg-background p-2 align-middle whitespace-nowrap',
                                'sticky end-0 border-s' => $stickyActions,
                                'max-md:flex max-md:justify-end max-md:px-4 max-md:py-3' => $stack,
                            ]) ?>">
                                <?php if ($actionsView): ?>
                                    <?= ui()->render($actionsView, ['row' => $row]) ?>
                                <?php elseif ($actionsMode === 'dropdown'): ?>
                                    <div class="flex justify-end">
                                        <?= $this->uiDropdownMenu([], function () use ($actions, $actionsLabel, $actionPayloadJs, $actionHref): void { ?>
                                            <?= $this->uiDropdownMenuTrigger([], function (): void { ?>
                                                <?= $this->uiButton(['variant' => 'ghost', 'size' => 'icon-xs'], function () use ($actionsLabel): void { ?>
                                                    <i data-lucide="ellipsis" class="size-4" aria-hidden="true"></i>
                                                    <span class="sr-only"><?= e($actionsLabel) ?></span>
                                                <?php }) ?>
                                            <?php }) ?>
                                            <?= $this->uiDropdownMenuContent(['align' => 'end'], function () use ($row, $actions, $actionPayloadJs, $actionHref): void {
                                                foreach ($actions as $action):
                                                    if (isset($action['visible']) && $action['visible'] instanceof Closure && ! ($action['visible'])($row)) { continue; }
                                                    $href = $actionHref($action, $row);
                                                    $itemVariant = (($action['variant'] ?? null) === 'danger' || ($action['variant'] ?? null) === 'destructive') ? 'destructive' : 'default';
                                                    $click = (! $href && ($action['method'] ?? null))
                                                        ? "\$dispatch('table:action', ".$actionPayloadJs($action, $row).')'
                                                        : null;
                                                    if ($click && ($action['confirm'] ?? null)) {
                                                        $click = "if (confirm('".addslashes((string) $action['confirm'])."')) { ".$click.' }';
                                                    } ?>
                                                    <?= $this->uiDropdownMenuItem([
                                                        'href' => $href,
                                                        'variant' => $itemVariant,
                                                        'x-on:click' => $click,
                                                    ], function () use ($action): void { ?>
                                                        <?php if (isset($action['icon'])): ?><i data-lucide="<?= e((string) $action['icon']) ?>" class="size-4" aria-hidden="true"></i><?php endif; ?>
                                                        <?= e((string) ($action['label'] ?? '')) ?>
                                                    <?php }) ?>
                                                <?php endforeach;
                                            }) ?>
                                        <?php }) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="flex items-center justify-end gap-1">
                                        <?php foreach ($actions as $action):
                                            if (isset($action['visible']) && $action['visible'] instanceof Closure && ! ($action['visible'])($row)) { continue; }
                                            $href = $actionHref($action, $row);
                                            $click = (! $href && ($action['method'] ?? null))
                                                ? "\$dispatch('table:action', ".$actionPayloadJs($action, $row).')'
                                                : null;
                                            if ($click && ($action['confirm'] ?? null)) {
                                                $click = "if (confirm('".addslashes((string) $action['confirm'])."')) { ".$click.' }';
                                            } ?>
                                            <?= $this->uiButton([
                                                'href' => $href,
                                                'size' => $action['size'] ?? 'sm',
                                                'variant' => $action['variant'] ?? 'ghost',
                                                'color' => $action['color'] ?? null,
                                                'class' => $action['class'] ?? null,
                                                'x-on:click' => $click,
                                                'aria-label' => $action['label'] ?? 'Action',
                                            ], function () use ($action): void { ?>
                                                <?php if (isset($action['icon'])): ?>
                                                    <i data-lucide="<?= e((string) $action['icon']) ?>" class="size-4" aria-hidden="true"></i>
                                                <?php endif; ?>
                                                <?php if (! ($action['iconOnly'] ?? false)): ?>
                                                    <span<?php if (isset($action['icon'])): ?> class="sr-only sm:not-sr-only"<?php endif; ?>><?= e((string) ($action['label'] ?? '')) ?></span>
                                                <?php endif; ?>
                                            <?php }) ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($isPaginator && $paginate && method_exists($rows, 'hasPages') && $rows->hasPages()): ?>
        <div class="pt-4">
            <?= $rows->links() ?>
        </div>
    <?php endif; ?>
</div>
