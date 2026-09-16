<?php

declare(strict_types=1);

extract(props($__ctx, [
    'versions' => [],
    'default' => null,
]));

$default = $default ?? ($versions[0] ?? '');
?>
<?= $this->uiSidebarMenu([], function () use ($versions, $default): void { ?>
    <?= $this->uiSidebarMenuItem(['x-data' => '{ selected: '.js($default).' }'], function () use ($versions, $default): void { ?>
        <?= $this->uiDropdownMenu([], function () use ($versions, $default): void { ?>
            <?= $this->uiDropdownMenuTrigger(['class' => 'w-full'], function () use ($default): void { ?>
                <?= $this->uiSidebarMenuButton([
                    'size' => 'lg',
                    'class' => 'data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground',
                    ':data-state' => "open ? 'open' : 'closed'",
                ], function (): void { ?>
                    <div class="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                        <i data-lucide="gallery-vertical-end" class="size-4"></i>
                    </div>
                    <div class="flex flex-col gap-0.5 leading-none">
                        <span class="font-medium">Documentation</span>
                        <span x-text="'v' + selected"></span>
                    </div>
                    <i data-lucide="chevrons-up-down" class="ml-auto"></i>
                <?php }) ?>
            <?php }) ?>
            <?= $this->uiDropdownMenuContent(['align' => 'start', 'class' => 'w-(--radix-popper-anchor-width) min-w-56'], function () use ($versions): void {
                foreach ($versions as $version) { ?>
                    <?= $this->uiDropdownMenuItem(['x-on:click' => 'selected = '.js($version)], function () use ($version): void { ?>
                        v<?= e($version) ?>
                        <i data-lucide="check" class="ml-auto" x-show="selected === <?= js($version) ?>" x-cloak></i>
                    <?php }) ?>
                <?php }
            }) ?>
        <?php }) ?>
    <?php }) ?>
<?php }) ?>
