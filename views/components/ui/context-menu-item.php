<?php

declare(strict_types=1);

extract(props($__ctx, [
    'href' => null,
    'variant' => 'default',
    'inset' => false,
    'disabled' => false,
]));

$attributes = $attributes->except('type', 'closeOnSelect', 'close-on-select');
?>
<?= $this->uiMenuItem(array_merge($attributes->all(), [
    'dataSlot' => 'context-menu-item',
    'clickConditional' => false,
    'href' => $href,
    'variant' => $variant,
    'inset' => $inset,
    'disabled' => $disabled,
]), $slot) ?>
