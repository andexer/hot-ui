<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<div data-slot="empty-description" <?= $attributes->twMerge('text-muted-foreground [&>a:hover]:text-primary text-sm/relaxed [&>a]:underline [&>a]:underline-offset-4') ?>><?= $slot ?></div>
