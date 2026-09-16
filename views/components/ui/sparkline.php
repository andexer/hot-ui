<?php

declare(strict_types=1);

extract(props($__ctx, [
    'data' => [],
    'width' => 100,
    'height' => 28,
    'area' => true,
    'strokeWidth' => 1.5,
    'ariaLabel' => 'Trend',
]));

$vals = array_values(array_map('floatval', (array) $data));
$n = count($vals);
$w = (float) $width;
$h = (float) $height;
$pad = (float) $strokeWidth;

$line = '';
$areaPath = '';

if ($n >= 2) {
    $min = min($vals);
    $max = max($vals);
    $range = ($max - $min) ?: 1;
    $coords = [];
    foreach ($vals as $i => $v) {
        $x = $pad + ($i / ($n - 1)) * ($w - 2 * $pad);
        $y = $pad + (1 - ($v - $min) / $range) * ($h - 2 * $pad);
        $coords[] = round($x, 2).','.round($y, 2);
    }
    $line = 'M'.implode(' L', $coords);
    $firstX = explode(',', $coords[0])[0];
    $lastX = explode(',', $coords[$n - 1])[0];
    $areaPath = $line." L{$lastX},{$h} L{$firstX},{$h} Z";
} elseif ($n === 1) {
    $mid = round($h / 2, 2);
    $line = "M{$pad},{$mid} L".($w - $pad).",{$mid}";
}
?>
<?php if ($line): ?>
    <svg
        data-slot="sparkline"
        role="img"
        aria-label="<?= e($ariaLabel) ?>"
        width="<?= e((string) $width) ?>" height="<?= e((string) $height) ?>" viewBox="0 0 <?= e((string) $width) ?> <?= e((string) $height) ?>"
        fill="none" preserveAspectRatio="none"
        <?= $attributes->twMerge('text-primary inline-block align-middle') ?>
    >
        <?php if ($area && $areaPath): ?>
            <path d="<?= e($areaPath) ?>" fill="currentColor" fill-opacity="0.12" stroke="none" />
        <?php endif; ?>
        <path d="<?= e($line) ?>" fill="none" stroke="currentColor" stroke-width="<?= e((string) $strokeWidth) ?>" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
<?php endif; ?>
