<?php

declare(strict_types=1);

extract(props($__ctx));
?>
<form <?= $attributes ?>>
    <?= $this->uiSidebarGroup(['class' => 'py-0'], function (): void { ?>
        <?= $this->uiSidebarGroupContent(['class' => 'relative'], function (): void { ?>
            <?= $this->uiLabel(['for' => 'search', 'class' => 'sr-only'], 'Search') ?>
            <?= $this->uiSidebarInput(['id' => 'search', 'placeholder' => 'Search the docs...', 'class' => 'pl-8']) ?>
            <i data-lucide="search" class="pointer-events-none absolute top-1/2 left-2 size-4 -translate-y-1/2 opacity-50 select-none"></i>
        <?php }) ?>
    <?php }) ?>
</form>
