<?php

declare(strict_types=1);

extract(props($__ctx, ['inset' => false]));
?>
<?= $this->uiMenuLabel(array_merge($attributes->all(), [
    'dataSlot' => 'context-menu-label',
    'classes' => 'text-foreground px-2 py-1.5 text-sm font-medium data-[inset]:ps-8',
    'inset' => $inset,
]), $slot) ?>
