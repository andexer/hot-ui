<?php

declare(strict_types=1);

extract(props($__ctx, [
    'text' => '',
    'speed' => 18,
    'startDelay' => 0,
    'by' => 'char',
    'caret' => true,
    'autostart' => true,
]));

$full = trim((string) $text);
if ($full === '') {
    $full = trim(strip_tags((string) $slot));
}
$by = $by === 'word' ? 'word' : 'char';
?>
<span
    data-slot="streaming-text"
    x-data="hotStreamingText({
        full: <?= js($full) ?>,
        by: <?= js($by) ?>,
        speed: <?= js(max(0, (int) $speed)) ?>,
        startDelay: <?= js(max(0, (int) $startDelay)) ?>,
        autostart: <?= js((bool) $autostart) ?>,
    })"
    <?= $attributes->twMerge('inline whitespace-pre-wrap text-foreground') ?>
>
    <span class="sr-only" aria-live="polite"><?= e($full) ?></span>

    <span aria-hidden="true" x-text="out"></span>
    <?php if ($caret): ?>
        <span
            x-show="! done"
            aria-hidden="true"
            class="animate-caret-blink ms-px inline-block h-[1em] w-[0.5em] translate-y-[0.12em] bg-primary align-text-bottom"
        ></span>
    <?php endif; ?>
</span>
