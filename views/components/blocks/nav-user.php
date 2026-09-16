<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => 'shadcn',
    'email' => 'm@example.com',
    'avatar' => '',
    'fallback' => 'CN',
    'align' => 'end',
]));

$avatarUrl = is_string($avatar) && $avatar !== '' ? safe_url($avatar) : null;
?>
<?= $this->uiSidebarMenu([], function () use ($name, $email, $avatarUrl, $fallback, $align): void { ?>
    <?= $this->uiSidebarMenuItem([], function () use ($name, $email, $avatarUrl, $fallback, $align): void { ?>
        <?= $this->uiDropdownMenu([], function () use ($name, $email, $avatarUrl, $fallback, $align): void { ?>
            <?= $this->uiDropdownMenuTrigger(['class' => 'w-full'], function () use ($name, $email, $avatarUrl, $fallback): void { ?>
                <?= $this->uiSidebarMenuButton([
                    'size' => 'lg',
                    'class' => 'data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground',
                    ':data-state' => "open ? 'open' : 'closed'",
                ], function () use ($name, $email, $avatarUrl, $fallback): void { ?>
                    <?= $this->uiAvatar(['class' => 'h-8 w-8 rounded-lg'], function () use ($name, $avatarUrl, $fallback): void { ?>
                        <?php if ($avatarUrl !== null): ?>
                            <?= $this->uiAvatarImage(['src' => $avatarUrl, 'alt' => $name]) ?>
                        <?php endif; ?>
                        <?= $this->uiAvatarFallback(['class' => 'rounded-lg'], e($fallback)) ?>
                    <?php }) ?>
                    <div class="grid flex-1 text-left text-sm leading-tight">
                        <span class="truncate font-medium"><?= e($name) ?></span>
                        <span class="truncate text-xs"><?= e($email) ?></span>
                    </div>
                    <i data-lucide="chevrons-up-down" class="ml-auto size-4"></i>
                <?php }) ?>
            <?php }) ?>
            <?= $this->uiDropdownMenuContent([
                'class' => 'w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg',
                'side' => 'right',
                'align' => $align,
                'sideOffset' => 4,
            ], function () use ($name, $email, $avatarUrl, $fallback): void { ?>
                <?= $this->uiDropdownMenuLabel(['class' => 'p-0 font-normal'], function () use ($name, $email, $avatarUrl, $fallback): void { ?>
                    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                        <?= $this->uiAvatar(['class' => 'h-8 w-8 rounded-lg'], function () use ($name, $avatarUrl, $fallback): void { ?>
                            <?php if ($avatarUrl !== null): ?>
                                <?= $this->uiAvatarImage(['src' => $avatarUrl, 'alt' => $name]) ?>
                            <?php endif; ?>
                            <?= $this->uiAvatarFallback(['class' => 'rounded-lg'], e($fallback)) ?>
                        <?php }) ?>
                        <div class="grid flex-1 text-left text-sm leading-tight">
                            <span class="truncate font-medium"><?= e($name) ?></span>
                            <span class="truncate text-xs"><?= e($email) ?></span>
                        </div>
                    </div>
                <?php }) ?>
                <?= $this->uiDropdownMenuSeparator() ?>
                <?= $this->uiDropdownMenuGroup([], function (): void { ?>
                    <?= $this->uiDropdownMenuItem([], function (): void { ?>
                        <i data-lucide="sparkles"></i>
                        Upgrade to Pro
                    <?php }) ?>
                <?php }) ?>
                <?= $this->uiDropdownMenuSeparator() ?>
                <?= $this->uiDropdownMenuGroup([], function (): void { ?>
                    <?= $this->uiDropdownMenuItem([], function (): void { ?>
                        <i data-lucide="badge-check"></i>
                        Account
                    <?php }) ?>
                    <?= $this->uiDropdownMenuItem([], function (): void { ?>
                        <i data-lucide="credit-card"></i>
                        Billing
                    <?php }) ?>
                    <?= $this->uiDropdownMenuItem([], function (): void { ?>
                        <i data-lucide="bell"></i>
                        Notifications
                    <?php }) ?>
                <?php }) ?>
                <?= $this->uiDropdownMenuSeparator() ?>
                <?= $this->uiDropdownMenuItem([], function (): void { ?>
                    <i data-lucide="log-out"></i>
                    Log out
                <?php }) ?>
            <?php }) ?>
        <?php }) ?>
    <?php }) ?>
<?php }) ?>
