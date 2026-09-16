<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<?= $this->uiMenuGroup(array_merge($attributes->all(), [
    'dataSlot' => 'context-menu-group',
    'labelSlot' => 'context-menu-label',
]), $slot) ?>
