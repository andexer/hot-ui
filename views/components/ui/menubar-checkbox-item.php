<?php

declare(strict_types=1);

extract(props($__ctx, [
    'checked' => false,
    'disabled' => false,
    'closeOnSelect' => false,
]));
?>
<?= $this->uiMenuCheckboxItem([
    'dataSlot' => 'menubar-checkbox-item',
    'checked' => $checked,
    'disabled' => $disabled,
    'closeOnSelect' => $closeOnSelect,
    ...$attributes->all(),
], $slot) ?>
