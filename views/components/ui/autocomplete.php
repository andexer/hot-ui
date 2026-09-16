<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'options' => [],
    'value' => '',
    'placeholder' => 'Search...',
    'empty' => 'No results found.',
    'size' => 'default',
    'disabled' => false,
    'multiple' => false,
    'icon' => null,
    'width' => 'w-[260px]',
]));

?>
<?= $this->uiCombobox([
    'trigger' => 'input',
    'name' => $name,
    'options' => $options,
    'value' => $value,
    'placeholder' => $placeholder,
    'empty' => $empty,
    'size' => $size,
    'disabled' => $disabled,
    'multiple' => $multiple,
    'icon' => $icon,
    'width' => $width,
    ...$attributes->all(),
]) ?>
