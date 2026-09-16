<?php

declare(strict_types=1);

extract(props($__ctx, [
    'node' => [],
]));

$name = $node['name'] ?? '';
$title = $node['title'] ?? null;
$avatar = $node['avatar'] ?? null;
$children = $node['children'] ?? [];
$hasChildren = is_array($children) && count($children) > 0;

$words = array_values(array_filter(
    preg_split('/\s+/', trim((string) $name)) ?: [],
    static fn (string $word): bool => $word !== '',
));
$initials = implode('', array_map(
    static fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)),
    array_slice($words, 0, 2),
));

$avatarUrl = safe_url(is_string($avatar) ? $avatar : null);
?>
<li>
    <div class="bg-card text-card-foreground node relative z-10 flex w-44 flex-col items-center gap-2 rounded-xl border p-4 text-center shadow-sm">
        <span class="bg-muted relative flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-full">
            <?php if ($avatarUrl !== null): ?>
                <img src="<?= e($avatarUrl) ?>" alt="" class="size-full object-cover" />
            <?php else: ?>
                <span class="text-muted-foreground text-sm font-medium" aria-hidden="true"><?= e($initials) ?></span>
            <?php endif; ?>
        </span>

        <span class="text-foreground text-sm leading-tight font-semibold"><?= e($name) ?></span>

        <?php if ($title): ?>
            <span class="text-muted-foreground text-xs leading-tight"><?= e($title) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($hasChildren): ?>
        <ul class="m-0 list-none p-0">
            <?php foreach ($children as $child): ?>
                <?= $this->uiOrgChartNode(['node' => $child]) ?>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</li>
