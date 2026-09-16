<?php

declare(strict_types=1);

extract(props($__ctx, [
    'href' => '#',
    'label' => null,
    'ariaLabel' => null,
]));

$label ??= 'Previous';
$ariaLabel ??= 'Go to previous page';
?>
<?= $this->uiPaginationLink([
    'href' => $href,
    'size' => 'default',
    'aria-label' => $ariaLabel,

    'class' => trim(((string) ($attributes->get('class') ?? '')).' gap-1 px-2.5 sm:ps-2.5'),
    ...$attributes->except('class')->all(),
], function () use ($label): void { ?>
    <i data-lucide="chevron-left" class="rtl:rotate-180"></i>
    <span class="hidden sm:block"><?= e($label) ?></span>
<?php }) ?>
