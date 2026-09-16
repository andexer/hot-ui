<?php

declare(strict_types=1);

extract(props($__ctx, [
    'icon' => null,
    'label' => null,
    'href' => '#',
    'active' => false,
    'badge' => null,
]));

$filled = static fn (mixed $value): bool => match (true) {
    $value === null, $value === false => false,
    is_array($value) => $value !== [],
    default => trim((string) $value) !== '',
};

$isLink = $filled($href);
$tag = $isLink ? 'a' : 'button';
$linkTarget = safe_url(is_string($href) ? $href : null);

$base = 'group relative flex flex-1 flex-col items-center justify-center gap-1 px-1 text-center outline-none transition-colors focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:ring-inset';

$state = $active
    ? 'text-primary'
    : 'text-muted-foreground hover:text-foreground';

$hasDot = $badge === true;
$hasCount = $filled($badge) && $badge !== true;
?>
<<?= $tag ?>
    data-slot="bottom-navigation-item"
<?php if ($isLink): ?>
    href="<?= e($linkTarget ?? '#') ?>"
<?php else: ?>
    type="button"
<?php endif; ?>
<?php if ($active): ?>
    aria-current="page"
<?php endif; ?>
    <?= $attributes->twMerge($base.' '.$state) ?>
>
<?php if ($icon): ?>
    <span class="relative inline-flex">
        <i data-lucide="<?= e($icon) ?>" class="size-5 shrink-0" aria-hidden="true"></i>

<?php if ($hasDot): ?>
        <span class="bg-destructive absolute end-0 top-0 size-2 -translate-y-1/2 translate-x-1/2 rounded-full ring-2 ring-background rtl:-translate-x-1/2"></span>
        <span class="sr-only">(new)</span>
<?php elseif ($hasCount): ?>
        <span class="bg-destructive text-destructive-foreground absolute end-0 top-0 inline-flex h-4 min-w-4 -translate-y-1/2 translate-x-1/2 items-center justify-center rounded-full px-1 text-[0.625rem] font-medium leading-none ring-2 ring-background rtl:-translate-x-1/2"><?= e($badge) ?></span>
<?php endif; ?>
    </span>
<?php endif; ?>

<?php if ($filled($label)): ?>
    <span class="text-xs leading-none font-medium"><?= e($label) ?></span>
<?php endif; ?>

    <?= $slot ?>
</<?= $tag ?>>
