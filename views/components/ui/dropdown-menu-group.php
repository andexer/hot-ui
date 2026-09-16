<?php

declare(strict_types=1);

extract(props($__ctx));

?>
<?= $this->uiMenuGroup(array_merge($attributes->all(), [
    'dataSlot' => 'dropdown-menu-group',
    'labelSlot' => 'dropdown-menu-label',
    'compact' => true,
]), $slot) ?>
