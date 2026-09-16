<?php

declare(strict_types=1);

extract(props($__ctx));

$srLabel = $attributes->get('aria-label');
$attributes = $attributes->except('aria-label');
?>
<kbd
    data-slot="kbd"
    <?= $attributes->twMerge("bg-muted text-muted-foreground pointer-events-none inline-flex h-5 w-fit min-w-5 items-center justify-center gap-1 rounded-sm px-1 font-sans text-xs font-medium select-none [&_svg:not([class*='size-'])]:size-3 [[data-slot=tooltip-content]_&]:bg-background/20 [[data-slot=tooltip-content]_&]:text-background dark:[[data-slot=tooltip-content]_&]:bg-background/10") ?>
><?php if ($srLabel): ?><span class="sr-only"><?= e($srLabel) ?></span><?php endif; ?><?= $slot ?></kbd>
