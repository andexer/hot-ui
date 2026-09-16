<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="alert-description" <?= $attributes->twMerge('text-muted-foreground col-start-2 grid justify-items-start gap-1 text-sm [&_p]:leading-relaxed') ?>><?= $slot ?></div>
