<?php

declare(strict_types=1);

extract(props($__ctx, [
    'href' => '#',
    'label' => null,
    'ariaLabel' => null,
]));

$label ??= 'Next';
$ariaLabel ??= 'Go to next page';
?>
<?= $this->uiPaginationLink([
    'href' => $href,
    'size' => 'default',
    'aria-label' => $ariaLabel,

    'class' => trim(((string) ($attributes->get('class') ?? '')).' gap-1 px-2.5 sm:pe-2.5'),
    ...$attributes->except('class')->all(),
], function () use ($label): void { ?>
    <span class="hidden sm:block"><?= e($label) ?></span>
    <i data-lucide="chevron-right" class="rtl:rotate-180"></i>
<?php }) ?>
