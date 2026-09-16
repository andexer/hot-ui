<?php

declare(strict_types=1);

extract(props($__ctx, [
    'item' => null,
]));

$parts = is_array($item) ? $item : [$item];
$name = $parts[0] ?? '';
$children = array_slice($parts, 1);
?>
<?php if ($children === []): ?>
    <?= $this->uiSidebarMenuButton(['isActive' => $name === 'button.tsx', 'class' => 'data-[active=true]:bg-transparent'], function () use ($name): void { ?>
        <i data-lucide="file"></i>
        <?= e($name) ?>
    <?php }) ?>
<?php else: ?>
    <?= $this->uiSidebarMenuItem([], function () use ($name, $children): void { ?>
        <?= $this->uiCollapsible([
            'class' => 'group/collapsible [&[data-state=open]>button>svg:first-child]:rotate-90',
            'open' => in_array($name, ['components', 'ui']),
            ':data-state' => "open ? 'open' : 'closed'",
        ], function () use ($name, $children): void { ?>
            <?= $this->uiSidebarMenuButton(['x-on:click' => 'open = !open', ':data-state' => "open ? 'open' : 'closed'"], function () use ($name): void { ?>
                <i data-lucide="chevron-right" class="transition-transform"></i>
                <i data-lucide="folder"></i>
                <?= e($name) ?>
            <?php }) ?>
            <?= $this->uiCollapsibleContent([], function () use ($children): void { ?>
                <?= $this->uiSidebarMenuSub([], function () use ($children): void {
                    foreach ($children as $child) { ?>
                        <?= $this->blocksFileTree(['item' => $child]) ?>
                    <?php }
                }) ?>
            <?php }) ?>
        <?php }) ?>
    <?php }) ?>
<?php endif; ?>
