<?php

declare(strict_types=1);

extract(props($__ctx, [
    'inset' => false,
]));
?>
<?= $this->uiMenuLabel([
    'dataSlot' => 'menubar-label',
    'inset' => $inset,
    ...$attributes->all(),
], $slot) ?>
