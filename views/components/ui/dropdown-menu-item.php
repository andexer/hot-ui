<?php

declare(strict_types=1);

extract(props($__ctx, [
    'href' => null,
    'variant' => 'default',
    'inset' => false,
    'disabled' => false,
    'closeOnSelect' => true,

    'type' => 'button',
]));
?>
<?= $this->uiMenuItem(array_merge($attributes->all(), [
    'dataSlot' => 'dropdown-menu-item',
    'href' => $href,
    'variant' => $variant,
    'inset' => $inset,
    'disabled' => $disabled,
    'closeOnSelect' => $closeOnSelect,
    'type' => $type,
]), $slot) ?>
