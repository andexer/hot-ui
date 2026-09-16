<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<li
    data-slot="breadcrumb-separator"
    role="presentation"
    aria-hidden="true"
    <?= $attributes->twMerge('[&>svg]:size-3.5') ?>
>
<?php if (trim((string) $slot) !== ''): ?>
    <?= $slot ?>
<?php else: ?>
    <i data-lucide="chevron-right"></i>
<?php endif; ?>
</li>
