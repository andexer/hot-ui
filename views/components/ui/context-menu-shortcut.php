<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<?= $this->uiMenuShortcut(array_merge($attributes->all(), [
    'dataSlot' => 'context-menu-shortcut',
]), $slot) ?>
