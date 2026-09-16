<?php

declare(strict_types=1);

extract(props($__ctx, [
    'items' => [],
]));
?>
<ul
    data-slot="tree"
    role="tree"
    aria-label="<?= e($attributes->get('aria-label', 'Tree')) ?>"
    x-data="hotTree()"
    @keydown.down.prevent="move($event.target, 1)"
    @keydown.up.prevent="move($event.target, -1)"
    <?= $attributes->except('aria-label')->twMerge('text-foreground select-none') ?>
>
    <?php foreach ($items as $loopIndex => $item): ?>
        <?= $this->uiTreeNode(['item' => $item, 'level' => 1, 'first' => $loopIndex === array_key_first($items)]) ?>
    <?php endforeach; ?>
</ul>
