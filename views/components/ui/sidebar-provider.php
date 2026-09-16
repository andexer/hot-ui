<?php

declare(strict_types=1);

extract(props($__ctx, [
    'defaultOpen' => true,
    'mobileBreakpoint' => '767px',
]));

$mobileQuery = '(max-width: '.(is_numeric($mobileBreakpoint) ? $mobileBreakpoint.'px' : $mobileBreakpoint).')';

$style = rtrim('--sidebar-width: calc(var(--spacing) * 64); --sidebar-width-icon: calc(var(--spacing) * 12); '.$attributes->get('style', ''));
?>
<div
    data-slot="sidebar-provider"
    x-data="hotSidebar({
        defaultOpen: <?= js((bool) $defaultOpen) ?>,
        mobileQuery: <?= js($mobileQuery) ?>,
    })"
    x-effect="collapsed = !isMobile && !open"
    style="<?= e($style) ?>"
    <?= $attributes->except('style')->twMerge('group/sidebar-wrapper flex min-h-svh w-full has-data-[variant=inset]:bg-sidebar') ?>
>
    <?= $slot ?>
</div>
