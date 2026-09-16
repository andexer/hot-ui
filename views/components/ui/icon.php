<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
]));

$directional = $name !== null
    && preg_match('/(arrow|chevron|caret)/i', $name) === 1
    && preg_match('/(left|right)/i', $name) === 1;
?>
<?php if ($name !== null): ?>
<i data-lucide="<?= e($name) ?>"<?= $attributes->class(['hot-rtl-flip' => (bool) $directional]) ?>></i>
<?php endif; ?>
