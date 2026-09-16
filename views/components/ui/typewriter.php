<?php

declare(strict_types=1);

extract(props($__ctx, [
    'words' => [],
    'typeSpeed' => 90,
    'deleteSpeed' => 40,
    'pause' => 1600,
    'loop' => true,
    'cursor' => true,
]));

$words = is_array($words)
    ? array_values(array_filter(array_map(fn ($w) => trim((string) $w), $words), fn ($w) => $w !== ''))
    : [];
if ($words === []) {
    $fallback = trim(strip_tags((string) $slot));
    $words = $fallback !== '' ? [$fallback] : [];
}

$loopJs = $loop ? 'true' : 'false';
?>
<span
    data-slot="typewriter"
    x-data="hotTypewriter({
        words: <?= js($words) ?>,
        typeSpeed: <?= js((int) $typeSpeed) ?>,
        deleteSpeed: <?= js((int) $deleteSpeed) ?>,
        pause: <?= js((int) $pause) ?>,
        loop: <?= $loopJs ?>,
    })"
    <?= $attributes->twMerge('inline-flex items-baseline whitespace-pre') ?>
>
    <span aria-hidden="true" x-text="out"></span>
    <?php if ($cursor): ?>
        <span x-show="! done" aria-hidden="true" class="animate-caret-blink ms-px inline-block h-[1em] w-[2px] translate-y-[0.12em] bg-current"></span>
    <?php endif; ?>
    <span class="sr-only"><?= e(implode(', ', $words)) ?></span>
</span>
