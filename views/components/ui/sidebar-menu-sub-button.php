<?php

declare(strict_types=1);

extract(props($__ctx, [
    'href' => '#',
    'isActive' => false,
    'size' => 'md',
]));

$classes = "text-sidebar-foreground ring-sidebar-ring hover:bg-sidebar-accent hover:text-sidebar-accent-foreground active:bg-sidebar-accent active:text-sidebar-accent-foreground [&>svg]:text-sidebar-accent-foreground data-[active=true]:bg-sidebar-accent data-[active=true]:text-sidebar-accent-foreground flex h-7 min-w-0 -translate-x-px items-center gap-2 overflow-hidden rounded-md px-2 outline-none focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50 [&>span:last-child]:truncate [&>svg]:size-4 [&>svg]:shrink-0 group-data-[collapsible=icon]:hidden ".($size === 'sm' ? 'text-xs' : 'text-sm');

$hrefTarget = safe_url((string) $href);
?>
<a
    href="<?= e($hrefTarget) ?>"
    data-slot="sidebar-menu-sub-button"
    data-sidebar="menu-sub-button"
    data-size="<?= e($size) ?>"
    <?php if ($isActive): ?>data-active="true"<?php endif; ?>
    <?= $attributes->twMerge($classes) ?>
><?= $slot ?></a>
