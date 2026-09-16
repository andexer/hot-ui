<?php

declare(strict_types=1);

extract(props($__ctx, [
    'teams' => [],
]));
?>
<?= $this->uiSidebarMenu(['x-data' => '{ active: 0 }'], function () use ($teams): void {
    $teamNames = array_column($teams, 'name');
    $teamPlans = array_column($teams, 'plan'); ?>
    <?= $this->uiSidebarMenuItem([], function () use ($teams, $teamNames, $teamPlans): void { ?>
        <?= $this->uiDropdownMenu([], function () use ($teams, $teamNames, $teamPlans): void { ?>
            <?= $this->uiDropdownMenuTrigger(['class' => 'w-full'], function () use ($teams, $teamNames, $teamPlans): void { ?>
                <?= $this->uiSidebarMenuButton([
                    'size' => 'lg',
                    'class' => 'data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground',
                    ':data-state' => "open ? 'open' : 'closed'",
], function () use ($teams, $teamNames, $teamPlans): void { ?>
                    <div class="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                        <?php foreach ($teams as $i => $team): ?>
                            <span x-show="active === <?= (int) $i ?>">
                                <i data-lucide="<?= e($team['logo']) ?>" class="size-4"></i>
                            </span>
                        <?php endforeach; ?>
                    </div>
                    <div class="grid flex-1 text-left text-sm leading-tight">
                        <span class="truncate font-medium" x-text="<?= js($teamNames) ?>[active]"></span>
                        <span class="truncate text-xs" x-text="<?= js($teamPlans) ?>[active]"></span>
                    </div>
                    <i data-lucide="chevrons-up-down" class="ml-auto"></i>
                <?php }) ?>
            <?php }) ?>
            <?= $this->uiDropdownMenuContent([
                'class' => 'w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg',
                'align' => 'start',
                'side' => 'right',
                'sideOffset' => 4,
            ], function () use ($teams): void { ?>
                <?= $this->uiDropdownMenuLabel(['class' => 'text-muted-foreground text-xs'], 'Teams') ?>
                <?php foreach ($teams as $i => $team): ?>
                    <?= $this->uiDropdownMenuItem(['x-on:click' => 'active = '.(int) $i, 'class' => 'gap-2 p-2'], function () use ($team, $i): void { ?>
                        <div class="flex size-6 items-center justify-center rounded-md border">
                            <i data-lucide="<?= e($team['logo']) ?>" class="size-3.5 shrink-0"></i>
                        </div>
                        <?= e($team['name']) ?>
                        <?= $this->uiDropdownMenuShortcut([], '⌘'.((int) $i + 1)) ?>
                    <?php }) ?>
                <?php endforeach; ?>
                <?= $this->uiDropdownMenuSeparator() ?>
                <?= $this->uiDropdownMenuItem(['class' => 'gap-2 p-2'], function (): void { ?>
                    <div class="flex size-6 items-center justify-center rounded-md border bg-transparent">
                        <i data-lucide="plus" class="size-4"></i>
                    </div>
                    <div class="text-muted-foreground font-medium">Add team</div>
                <?php }) ?>
            <?php }) ?>
        <?php }) ?>
    <?php }) ?>
<?php }) ?>
