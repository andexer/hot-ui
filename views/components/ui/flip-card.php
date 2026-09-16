<?php

declare(strict_types=1);

extract(props($__ctx, [
    'trigger' => 'hover',
    'height' => null,
]));

$trigger = in_array($trigger, ['hover', 'click'], true) ? $trigger : 'hover';
$isClick = $trigger === 'click';

$boxHeight = $height ?: '16rem';

$hasFront = isset($front) && $front->isNotEmpty();
?>
<div
    data-slot="flip-card"
    x-data="{ flipped: false }"
    <?php if (! $isClick): ?>
    @mouseenter="flipped = true"
    @mouseleave="flipped = false"
    @focusin="flipped = true"
    @focusout="flipped = false"
    <?php endif; ?>
    style="perspective: 1000px; height: <?= e($boxHeight) ?>;"
    <?= $attributes->twMerge('relative w-full') ?>
>
    <<?= $isClick ? 'button' : 'div' ?>
        <?php if ($isClick): ?>
        type="button"
        @click="flipped = !flipped"
        :aria-pressed="flipped ? 'true' : 'false'"
        <?php endif; ?>
        data-slot="flip-card-flipper"
        class="relative block size-full text-start outline-none transition-transform duration-500 [transform-style:preserve-3d] focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:ring-offset-2 focus-visible:ring-offset-background rounded-xl<?php if ($isClick): ?> cursor-pointer<?php endif; ?>"
        :class="flipped ? '[transform:rotateY(180deg)]' : ''"
    >
        <div
            data-slot="flip-card-front"
            class="bg-card text-card-foreground absolute inset-0 flex flex-col overflow-hidden rounded-xl border p-6 shadow-sm [backface-visibility:hidden] [-webkit-backface-visibility:hidden]"
            :inert="flipped"
            :aria-hidden="flipped ? 'true' : 'false'"
        >
            <?= $hasFront ? $front : $slot ?>
        </div>

        <div
            data-slot="flip-card-back"
            class="bg-card text-card-foreground absolute inset-0 flex flex-col overflow-hidden rounded-xl border p-6 shadow-sm [backface-visibility:hidden] [-webkit-backface-visibility:hidden] [transform:rotateY(180deg)]"
            :inert="!flipped"
            :aria-hidden="flipped ? 'false' : 'true'"
        >
            <?= $back ?? '' ?>
        </div>
    </<?= $isClick ? 'button' : 'div' ?>>
</div>
