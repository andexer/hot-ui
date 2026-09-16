<?php

declare(strict_types=1);

extract(props($__ctx, [
    'checked' => false,
    'disabled' => false,
    'closeOnSelect' => false,
]));

$forwarded = array_merge($attributes->all(), [
    'dataSlot' => 'dropdown-menu-checkbox-item',
    'checked' => $checked,
    'disabled' => $disabled,
    'closeOnSelect' => $closeOnSelect,
]);
?>
<?= $this->uiMenuCheckboxItem($forwarded, $slot) ?>
