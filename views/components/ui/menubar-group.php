<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<?= $this->uiMenuGroup([
    'dataSlot' => 'menubar-group',
    'labelSlot' => 'menubar-label',
    ...$attributes->all(),
], $slot) ?>
