<?php

declare(strict_types=1);

extract(props($__ctx, [
    'from' => null,
    'via' => null,
    'to' => null,
    'preset' => null,
    'animate' => false,
    'as' => 'span',
]));

$emitStyles = ! isset($GLOBALS['__hot_gradient_text_styles_emitted']);
if ($emitStyles) {
    $GLOBALS['__hot_gradient_text_styles_emitted'] = true;
}

$presets = [
    'brand'    => ['#6366f1', '#a855f7', '#ec4899'],
    'sunset'   => ['#f97316', '#ef4444', '#ec4899'],
    'ocean'    => ['#06b6d4', '#3b82f6', '#6366f1'],
    'candy'    => ['#ec4899', '#d946ef', '#8b5cf6'],
    'gold'     => ['#f59e0b', '#eab308', '#fbbf24'],
    'aurora'   => ['#22d3ee', '#a78bfa', '#34d399'],
    'flamingo' => ['#fb7185', '#f472b6', '#c084fc'],
    'mint'     => ['#34d399', '#10b981', '#059669'],
];

if ($from !== null || $to !== null) {
    $cFrom = $from ?? '#6366f1';
    $cVia = $via;
    $cTo = $to ?? $cFrom;
} else {
    [$cFrom, $cVia, $cTo] = $presets[$preset] ?? $presets['brand'];

    if ($via !== null) {
        $cVia = $via;
    }
}

$stops = $cVia !== null && $cVia !== ''
    ? "{$cFrom}, {$cVia}, {$cTo}"
    : "{$cFrom}, {$cTo}";

$gradient = "linear-gradient(90deg, {$stops})";

$animate = filter_var($animate, FILTER_VALIDATE_BOOLEAN);

$style = "background-image: {$gradient};"
    . " background-size: 200% auto;"
    . " background-position: ".($animate ? '0% center' : '50% center').";"
    . " -webkit-background-clip: text; background-clip: text;"
    . " color: transparent; -webkit-text-fill-color: transparent;";

if ($animate) {
    $style .= " animation: hot-gradient-text-shimmer 4s linear infinite;";
}
?>
<?php if ($emitStyles): ?>
<style>
    @keyframes hot-gradient-text-shimmer {
        to { background-position: 200% center; }
    }
    @media (prefers-reduced-motion: reduce) {
        [data-slot="gradient-text"][data-animate="true"] { animation: none !important; }
    }
</style>
<?php endif; ?>
<<?= $as ?>
    data-slot="gradient-text"
    <?php if ($preset): ?>data-preset="<?= e($preset) ?>"
    <?php endif; ?>
    data-animate="<?= $animate ? 'true' : 'false' ?>"
    style="<?= e($style) ?>"
    <?= $attributes->twMerge('inline-block bg-clip-text text-transparent') ?>
><?= $slot ?></<?= $as ?>>
