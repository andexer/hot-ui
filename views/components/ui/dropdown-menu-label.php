<?php

declare(strict_types=1);

extract(props($__ctx, ['inset' => false]));
?>
<?= $this->uiMenuLabel(array_merge($attributes->all(), [
    'dataSlot' => 'dropdown-menu-label',
    'inset' => $inset,
]), $slot) ?>
