<?php

declare(strict_types=1);

extract(props($__ctx, [
    'type' => 'single',
    'collapsible' => false,
    'value' => null,
]));
?>
<div
    data-slot="accordion"
    x-data="hotAccordion({
        type: <?= js($type) ?>,
        collapsible: <?= js((bool) $collapsible) ?>,
        value: <?= js($type === 'multiple' ? (array) ($value ?? []) : $value) ?>,
    })"
    x-id="['hot-accordion-trigger', 'hot-accordion-panel']"
    @keydown="$hot.nav($event, { selector: '[data-slot=accordion-trigger]', orientation: 'vertical', loop: false, requireMatch: true })"
    <?= $attributes ?>
>
    <?= $slot ?>
</div>
