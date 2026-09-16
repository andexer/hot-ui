<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => '',
    'closeOnSelect' => false,
]));
?>
<?= $this->uiMenuRadioItem([
    'dataSlot' => 'menubar-radio-item',
    'value' => $value,
    'closeOnSelect' => $closeOnSelect,
    ...$attributes->all(),
], $slot) ?>
