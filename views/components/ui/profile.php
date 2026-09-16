<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'avatar' => null,
    'initials' => null,
    'description' => null,
    'as' => 'button',
    'href' => null,
    'size' => 'default',
    'chevron' => null,
]));

$tag = in_array($as, ['button', 'a', 'div'], true) ? $as : 'button';

$fallbackWords = array_values(array_filter(explode(' ', (string) $name), static fn (string $w): bool => $w !== ''));
$derivedInitials = implode('', array_map(
    static fn (string $w): string => mb_strtoupper(mb_substr($w, 0, 1)),
    array_slice($fallbackWords, 0, 2),
));
$fallback = $initials ?: $derivedInitials;

$avatarSize = $size === 'sm' ? 'size-7' : 'size-8';
$showChevron = $chevron ?? ($tag === 'button');

$linkTarget = safe_url(is_string($href) ? $href : null);
$avatarUrl = safe_url(is_string($avatar) ? $avatar : null);
?>
<<?= $tag ?>
    data-slot="profile"
<?php if ($tag === 'button'): ?>
    type="button"
<?php endif; ?>
<?php if ($tag === 'a' && $linkTarget !== null): ?>
    href="<?= e($linkTarget) ?>"
<?php endif; ?>
    <?= $attributes->twMerge('group flex w-full items-center gap-2 rounded-md p-1 text-start text-foreground outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50') ?>
>
    <?= $this->uiAvatar(['class' => $avatarSize.' shrink-0'], function () use ($avatarUrl, $name, $fallback): void { ?>
        <?php if ($avatarUrl !== null): ?><?= $this->uiAvatarImage(['src' => $avatarUrl, 'alt' => $name ?? '']) ?><?php endif; ?>
        <?= $this->uiAvatarFallback([], e((string) $fallback)) ?>
    <?php }) ?>

    <?php if ($name || $description): ?>
        <span class="flex min-w-0 flex-1 flex-col">
            <?php if ($name): ?>
                <span class="truncate text-sm font-medium leading-tight"><?= e($name) ?></span>
            <?php endif; ?>
            <?php if ($description): ?>
                <span class="text-muted-foreground truncate text-xs leading-tight"><?= e($description) ?></span>
            <?php endif; ?>
        </span>
    <?php endif; ?>

    <?php if ($showChevron): ?>
        <i data-lucide="chevrons-up-down" class="text-muted-foreground ms-auto size-4 shrink-0" aria-hidden="true"></i>
    <?php endif; ?>

    <?= $slot ?>
</<?= $tag ?>>
