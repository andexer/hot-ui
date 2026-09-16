<?php

declare(strict_types=1);

extract(props($__ctx, [
    'toasts' => [],
]));

$flashes = [];
foreach ((array) $toasts as $t) {
    if (is_array($t) && isset($t['description'])) {
        $flashes[] = [
            'type' => is_string($t['type'] ?? null) ? $t['type'] : 'info',
            'description' => (string) $t['description'],
        ];
    } elseif (is_string($t) && $t !== '') {
        $flashes[] = ['type' => 'info', 'description' => $t];
    }
}

$dispatches = '';
foreach ($flashes as $f) {
    $dispatches .= "\$dispatch('toast', ".js($f).'); ';
}
?>
<div
    x-data
    x-init="<?= e($dispatches) ?>"
    class="hidden"
    aria-live="polite"
></div>
