<?php

declare(strict_types=1);

extract(props($__ctx, [
    'items' => [],
]));
?>
<?= $this->uiSidebarGroup($attributes->all(), function () use ($items): void { ?>
    <?= $this->uiSidebarGroupContent([], function () use ($items): void { ?>
        <?= $this->uiSidebarMenu([], function () use ($items): void {
            foreach ($items as $item) { ?>
                <?= $this->uiSidebarMenuItem([], function () use ($item): void { ?>
                    <?= $this->uiSidebarMenuButton(['href' => '#', 'size' => 'sm', 'tooltip' => $item['title']], function () use ($item): void { ?>
                        <i data-lucide="<?= e($item['icon']) ?>"></i>
                        <span><?= e($item['title']) ?></span>
                    <?php }) ?>
                <?php }) ?>
            <?php }
        }) ?>
    <?php }) ?>
<?php }) ?>
