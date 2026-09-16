<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'placeholder' => null,
    'minRows' => 2,
    'maxRows' => null,
    'size' => 'default',
    'disabled' => false,
    'id' => null,
    'value' => null,
]));

$slotContent = $value !== null ? e((string) $value) : $slot;
?>
<?= $this->uiTextarea([
    'rows' => (int) $minRows,
    'maxRows' => $maxRows,
    'size' => $size,
    'name' => $name,
    'id' => $id,
    'placeholder' => $placeholder,
    'disabled' => $disabled,
    ...$attributes->all(),
], $slotContent) ?>
