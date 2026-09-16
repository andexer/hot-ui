<?php

declare(strict_types=1);

extract(props($__ctx, [
    'color' => null,
    'foreground' => null,
]));

$style = '';
if ($color) {
    $fg = $foreground ?: '#ffffff';
    $style = "--primary: {$color}; --secondary: {$color}; --ring: {$color}; --sidebar-primary: {$color}; --primary-foreground: {$fg};";
}
$userStyle = (string) $attributes->get('style', '');
$style = trim($style.($style && $userStyle ? ' ' : '').$userStyle);
$attributes = $attributes->except('style');
?>
<div data-slot="accent"<?php if ($style): ?> style="<?= e($style) ?>"<?php endif; ?> <?= $attributes->twMerge('contents') ?>>
    <?= $slot ?>
</div>
