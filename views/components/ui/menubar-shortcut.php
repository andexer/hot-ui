<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<?= $this->uiMenuShortcut([
    'dataSlot' => 'menubar-shortcut',
    ...$attributes->all(),
], $slot) ?>
