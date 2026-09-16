<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => '',
    'closeOnSelect' => false,
]));
?>
<?= $this->uiMenuRadioItem(array_merge($attributes->all(), [
    'dataSlot' => 'dropdown-menu-radio-item',
    'value' => $value,
    'closeOnSelect' => $closeOnSelect,
]), $slot) ?>
