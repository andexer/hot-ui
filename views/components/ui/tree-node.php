<?php

declare(strict_types=1);

extract(props($__ctx, [
    'item' => [],
    'level' => 1,
    'first' => false,
]));

$label = $item['label'] ?? '';
$icon = $item['icon'] ?? null;
$children = $item['children'] ?? [];
$hasChildren = is_array($children) && count($children) > 0;
$expanded = (bool) ($item['expanded'] ?? false);

$indent = $level * 0.75;
?>
<li
    role="treeitem"
    aria-level="<?= e($level) ?>"
    aria-label="<?= e($label) ?>"
    <?php if ($hasChildren): ?>:aria-expanded="open ? 'true' : 'false'"<?php endif; ?>
    tabindex="<?= $first ? '0' : '-1' ?>"
    x-data="{ open: <?= js($hasChildren ? $expanded : false) ?> }"
    @keydown.enter.stop.prevent="<?= e($hasChildren ? 'open = !open' : '') ?>"
    @keydown.space.stop.prevent="<?= e($hasChildren ? 'open = !open' : '') ?>"
    @keydown.right.stop.prevent="<?= e($hasChildren ? 'open = true' : '') ?>"
    @keydown.left.stop.prevent="<?= e($hasChildren ? 'open = false' : '') ?>"
    class="focus-visible:ring-ring rounded outline-none focus-visible:ring-2"
>
    <div
        class="<?= classes([
            'hover:bg-accent hover:text-accent-foreground flex cursor-pointer items-center gap-1.5 rounded px-2 py-1 text-sm',
        ]) ?>"
        style="padding-inline-start: <?= e($indent) ?>rem;"
        <?php if ($hasChildren): ?>@click="open = !open"<?php endif; ?>
    >
        <?php if ($hasChildren): ?>
            <span class="text-muted-foreground -ms-1 flex size-4 shrink-0 items-center justify-center">
                <i data-lucide="chevron-right" class="size-3.5 transition-transform duration-150 rtl:-scale-x-100" x-bind:class="open && 'rotate-90'" aria-hidden="true"></i>
            </span>
        <?php else: ?>
            <span class="size-4 shrink-0"></span>
        <?php endif; ?>

        <?php if ($hasChildren): ?>
            <i data-lucide="folder" x-show="!open" class="text-muted-foreground size-4 shrink-0" aria-hidden="true"></i>
            <i data-lucide="folder-open" x-show="open" x-cloak class="text-muted-foreground size-4 shrink-0" aria-hidden="true"></i>
        <?php elseif ($icon): ?>
            <i data-lucide="<?= e($icon) ?>" class="text-muted-foreground size-4 shrink-0" aria-hidden="true"></i>
        <?php endif; ?>

        <span class="truncate"><?= e($label) ?></span>
    </div>

    <?php if ($hasChildren): ?>
        <ul role="group" x-show="open" x-cloak class="list-none">
            <?php foreach ($children as $child): ?>
                <?= $this->uiTreeNode(['item' => $child, 'level' => $level + 1]) ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>
