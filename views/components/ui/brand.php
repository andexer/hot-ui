<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'href' => '/',
    'logo' => null,
    'alt' => null,
]));

$tag = ($href === null || $href === false) ? 'div' : 'a';
$hasLogoSlot = $slot->isNotEmpty();
$linkTarget = safe_url(is_string($href) ? $href : null);
?>
<<?= $tag ?>
    data-slot="brand"
<?php if ($tag === 'a'): ?>
    href="<?= e($linkTarget ?? '#') ?>"
<?php endif; ?>
<?php if (! $name): ?>
    aria-label="<?= e($alt ?? 'Home') ?>"
<?php endif; ?>
    <?= $attributes->twMerge('inline-flex items-center gap-2 rounded-md font-semibold text-foreground outline-none [&_img]:size-6 [&_svg]:size-6 [&_svg]:shrink-0 focus-visible:ring-ring/50 focus-visible:ring-[3px]') ?>
>
<?php if ($logo): ?>
    <img src="<?= e(safe_url(is_string($logo) ? $logo : null) ?? '') ?>" alt="<?= e($alt ?? $name ?? '') ?>" class="size-6 shrink-0 rounded" />
<?php elseif ($hasLogoSlot): ?>
    <?= $slot ?>
<?php endif; ?>

<?php if ($name): ?>
    <span><?= e($name) ?></span>
<?php endif; ?>
</<?= $tag ?>>
