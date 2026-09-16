<?php

declare(strict_types=1);

extract(props($__ctx, [
    'lat' => null,
    'lon' => null,
    'zoom' => 14,
    'label' => 'Location',
    'marker' => true,
    'height' => 320,
    'ratio' => null,
]));

$lat = $lat === null ? 50.8467 : (float) $lat;
$lon = $lon === null ? 4.3499 : (float) $lon;
$zoom = max(1, min(19, (int) $zoom));

$delta = 360 / pow(2, $zoom);
$minLon = $lon - $delta;
$maxLon = $lon + $delta;
$minLat = $lat - $delta / 2;
$maxLat = $lat + $delta / 2;

$bbox = implode(',', [
    number_format($minLon, 6, '.', ''),
    number_format($minLat, 6, '.', ''),
    number_format($maxLon, 6, '.', ''),
    number_format($maxLat, 6, '.', ''),
]);

$embed = 'https://www.openstreetmap.org/export/embed.html?bbox='.$bbox.'&layer=mapnik';
if ($marker) {
    $embed .= '&marker='.number_format($lat, 6, '.', '').','.number_format($lon, 6, '.', '');
}

$larger = 'https://www.openstreetmap.org/?mlat='.number_format($lat, 6, '.', '')
    .'&mlon='.number_format($lon, 6, '.', '')
    .'#map='.$zoom.'/'.number_format($lat, 6, '.', '').'/'.number_format($lon, 6, '.', '');

$embedUrl = safe_url($embed);
$largerUrl = safe_url($larger);
$frameStyle = $ratio !== null ? 'aspect-ratio: '.$ratio.';' : 'height: '.(int) $height.'px;';
?>
<div data-slot="map" <?= $attributes->twMerge('w-full') ?>>
    <div class="bg-muted relative w-full overflow-hidden rounded-xl border" style="<?= e($frameStyle) ?>">
        <iframe
            title="Map of <?= e($label) ?>"
            src="<?= e($embedUrl) ?>"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            class="absolute inset-0 size-full border-0"
        ></iframe>
    </div>

    <a
        href="<?= e($largerUrl) ?>"
        target="_blank"
        rel="noopener noreferrer"
        class="text-muted-foreground hover:text-foreground mt-2 inline-flex items-center gap-1 text-xs underline-offset-4 hover:underline"
    >
        <span>View larger map</span>
        <i data-lucide="external-link" class="size-3 shrink-0 rtl:-scale-x-100" aria-hidden="true"></i>
        <span class="sr-only">(opens openstreetmap.org in a new tab)</span>
    </a>
</div>
