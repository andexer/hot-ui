<?php

declare(strict_types=1);

extract(props($__ctx, [
    'avatars' => [],
    'max' => 4,
    'size' => 'default',
]));

$sizeMap = [
    'sm'      => ['box' => 'size-6',  'overlap' => '-ms-2',   'ring' => 'ring-1', 'text' => 'text-xs'],
    'default' => ['box' => 'size-8',  'overlap' => '-ms-2.5', 'ring' => 'ring-2', 'text' => 'text-sm'],
    'lg'      => ['box' => 'size-12', 'overlap' => '-ms-3',   'ring' => 'ring-2', 'text' => 'text-base'],
];
$s = $sizeMap[$size] ?? $sizeMap['default'];

$items = array_values($avatars ?? []);
$total = count($items);
$shown = array_slice($items, 0, max(0, (int) $max));
$remaining = max(0, $total - count($shown));

$initials = function (?string $name): string {
    $name = trim((string) $name);
    if ($name === '') {
        return '?';
    }
    $parts = preg_split('/\s+/', $name);
    $first = mb_substr($parts[0], 0, 1);
    $last = count($parts) > 1 ? mb_substr($parts[count($parts) - 1], 0, 1) : '';

    return mb_strtoupper($first.$last);
};
?>
<div
    data-slot="avatar-group"
    role="list"
    <?= $attributes->twMerge('flex items-center') ?>
>
<?php foreach ($shown as $i => $avatar):
    $src = $avatar['src'] ?? null;
    $name = $avatar['name'] ?? null;
    $label = trim((string) $name) !== '' ? $name : 'User avatar';
    $srcUrl = is_string($src) ? safe_url($src) : null;
?>
    <?= $this->uiAvatar([
        'role' => 'listitem',
        'title' => $name,
        'class' => classes([$s['box'], $s['ring'], 'ring-background', $s['overlap'] => $i > 0]),
    ], function () use ($src, $srcUrl, $s, $label, $name, $initials): void { ?>
        <?php if ($src && $srcUrl !== null): ?>
            <?= $this->uiAvatarImage(['src' => $srcUrl, 'alt' => $label]) ?>
        <?php endif; ?>
        <?= $this->uiAvatarFallback(['class' => $s['text']], e($initials($name))) ?>
    <?php }) ?>
<?php endforeach; ?>

<?php if ($remaining > 0): ?>
    <span
        role="listitem"
        aria-label="and <?= e($remaining) ?> more"
        class="<?= classes([
            'relative z-10 flex shrink-0 items-center justify-center rounded-full font-medium',
            'bg-muted text-foreground',
            $s['box'],
            $s['ring'],
            'ring-background',
            $s['text'],
            $s['overlap'] => count($shown) > 0,
        ]) ?>"
    >
        <span aria-hidden="true">+<?= e($remaining) ?></span>
    </span>
<?php endif; ?>
</div>
