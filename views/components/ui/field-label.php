<?php

declare(strict_types=1);

extract(props($__ctx, [
    'for' => null,
]));
?>
<label
    <?php if ($for): ?>for="<?= e($for) ?>"
    <?php endif; ?>
    data-slot="field-label"
    <?= $attributes->twMerge('group/field-label peer/field-label flex w-fit gap-2 leading-snug group-data-[disabled=true]/field:opacity-50 has-[>[data-slot=field]]:w-full has-[>[data-slot=field]]:flex-col has-[>[data-slot=field]]:rounded-md text-sm leading-snug font-medium select-none') ?>
><?= $slot ?></label>
