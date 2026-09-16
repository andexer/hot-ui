<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<p data-slot="item-description" <?= $attributes->twMerge('text-muted-foreground line-clamp-2 text-sm leading-normal font-normal text-balance [&>a:hover]:text-primary [&>a]:underline [&>a]:underline-offset-4') ?>><?= $slot ?></p>
