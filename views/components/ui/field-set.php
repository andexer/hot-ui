<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<fieldset data-slot="field-set" <?= $attributes->twMerge('flex flex-col gap-6 has-[>[data-slot=checkbox-group]]:gap-3 has-[>[data-slot=radio-group]]:gap-3') ?>>
    <?= $slot ?>
</fieldset>
