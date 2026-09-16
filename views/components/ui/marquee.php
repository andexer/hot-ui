<?php

declare(strict_types=1);

extract(props($__ctx, [
    'direction' => 'left',
    'duration' => '40s',
    'gap' => '1rem',
    'pauseOnHover' => true,
    'fade' => false,
]));

$vertical = in_array($direction, ['up', 'down'], true);
$reverse = in_array($direction, ['right', 'down'], true);
$anim = $vertical ? 'hot-marquee-y' : 'hot-marquee-x';
$flexDir = $vertical ? 'flex-col' : 'flex-row';
$padSide = $vertical ? 'padding-bottom' : 'padding-right';
$fadeMask = ! $fade ? '' : ($vertical
    ? '[mask-image:linear-gradient(to_bottom,transparent,#000_12%,#000_88%,transparent)]'
    : '[mask-image:linear-gradient(to_right,transparent,#000_12%,#000_88%,transparent)]');
$groupStyle = "gap: {$gap}; {$padSide}: {$gap};";
?>
<div data-slot="marquee" data-direction="<?= e($direction) ?>" <?= $attributes->twMerge('group/marquee relative flex overflow-hidden '.($vertical ? 'flex-col' : 'flex-row').' '.$fadeMask) ?>>
    <div
        class="flex <?= e($flexDir) ?> w-max shrink-0 <?= $vertical ? 'h-max' : '' ?> <?= $pauseOnHover ? 'hover:[animation-play-state:paused]' : '' ?> motion-reduce:!animate-none"
        style="animation: <?= e($anim) ?> <?= e($duration) ?> linear infinite<?= $reverse ? ' reverse' : '' ?>;"
    >
        <div class="flex <?= e($flexDir) ?> shrink-0 items-center justify-around" style="<?= e($groupStyle) ?>"><?= $slot ?></div>
        <div class="flex <?= e($flexDir) ?> shrink-0 items-center justify-around" aria-hidden="true" style="<?= e($groupStyle) ?>"><?= $slot ?></div>
    </div>
</div>
