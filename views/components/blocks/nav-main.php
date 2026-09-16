<?php

declare(strict_types=1);

extract(props($__ctx, [
    'items' => [],
    'label' => 'Platform',
]));
?>
<?= $this->uiSidebarGroup([], function () use ($label, $items): void { ?>
    <?= $this->uiSidebarGroupLabel([], e($label)) ?>
    <?= $this->uiSidebarMenu([], function () use ($items): void {
        foreach ($items as $item) { ?>
            <?= $this->uiSidebarMenuItem([], function () use ($item): void { ?>
                <?= $this->uiCollapsible([
                    'open' => $item['isActive'] ?? false,
                    'class' => 'group/collapsible',
                    ':data-state' => "open ? 'open' : 'closed'",
                ], function () use ($item): void { ?>
                    <?= $this->uiSidebarMenuButton([
                        'tooltip' => $item['title'],
                        'x-on:click' => 'open = !open',
                        '::aria-expanded' => 'open',
                        '::aria-controls' => '$id(\'hot-collapsible\')',
                        ':data-state' => "open ? 'open' : 'closed'",
                    ], function () use ($item): void {
                        if (isset($item['icon'])) { ?>
                            <i data-lucide="<?= e($item['icon']) ?>" aria-hidden="true"></i>
                        <?php } ?>
                        <span><?= e($item['title']) ?></span>
                        <i data-lucide="chevron-right" class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" aria-hidden="true"></i>
                    <?php }) ?>
                    <?php if (! empty($item['items'])): ?>
                        <?= $this->uiCollapsibleContent([], function () use ($item): void { ?>
                            <?= $this->uiSidebarMenuSub([], function () use ($item): void {
                                foreach ($item['items'] as $subItem) { ?>
                                    <?= $this->uiSidebarMenuSubItem([], function () use ($subItem): void { ?>
                                        <?= $this->uiSidebarMenuSubButton(['href' => '#'], function () use ($subItem): void { ?>
                                            <span><?= e($subItem['title']) ?></span>
                                        <?php }) ?>
                                    <?php }) ?>
                                <?php }
                            }) ?>
                        <?php }) ?>
                    <?php endif; ?>
                <?php }) ?>
            <?php }) ?>
        <?php }
    }) ?>
<?php }) ?>
