<?php

declare(strict_types=1);

extract(props($__ctx, [
    'projects' => [],
    'label' => 'Projects',
]));
?>
<?= $this->uiSidebarGroup(['class' => 'group-data-[collapsible=icon]:hidden'], function () use ($label, $projects): void { ?>
    <?= $this->uiSidebarGroupLabel([], e($label)) ?>
    <?= $this->uiSidebarMenu([], function () use ($projects): void {
        foreach ($projects as $project) { ?>
            <?= $this->uiSidebarMenuItem([], function () use ($project): void { ?>
                <?= $this->uiSidebarMenuButton(['href' => '#'], function () use ($project): void { ?>
                    <i data-lucide="<?= e($project['icon']) ?>"></i>
                    <span><?= e($project['name']) ?></span>
                <?php }) ?>
                <?= $this->uiDropdownMenu([], function (): void { ?>
                    <?= $this->uiDropdownMenuTrigger([], function (): void { ?>
                        <?= $this->uiSidebarMenuAction(['showOnHover' => true], function (): void { ?>
                            <i data-lucide="more-horizontal"></i>
                            <span class="sr-only">More</span>
                        <?php }) ?>
                    <?php }) ?>
                    <?= $this->uiDropdownMenuContent(['class' => 'w-48 rounded-lg', 'side' => 'right', 'align' => 'start'], function (): void { ?>
                        <?= $this->uiDropdownMenuItem([], function (): void { ?>
                            <i data-lucide="folder" class="text-muted-foreground"></i>
                            <span>View Project</span>
                        <?php }) ?>
                        <?= $this->uiDropdownMenuItem([], function (): void { ?>
                            <i data-lucide="forward" class="text-muted-foreground"></i>
                            <span>Share Project</span>
                        <?php }) ?>
                        <?= $this->uiDropdownMenuSeparator() ?>
                        <?= $this->uiDropdownMenuItem([], function (): void { ?>
                            <i data-lucide="trash-2" class="text-muted-foreground"></i>
                            <span>Delete Project</span>
                        <?php }) ?>
                    <?php }) ?>
                <?php }) ?>
            <?php }) ?>
        <?php } ?>
        <?= $this->uiSidebarMenuItem([], function (): void { ?>
            <?= $this->uiSidebarMenuButton(['class' => 'text-sidebar-foreground/70'], function (): void { ?>
                <i data-lucide="more-horizontal" class="text-sidebar-foreground/70"></i>
                <span>More</span>
            <?php }) ?>
        <?php }) ?>
    <?php }) ?>
<?php }) ?>
