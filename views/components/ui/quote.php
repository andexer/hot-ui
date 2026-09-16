<?php

declare(strict_types=1);

extract(props($__ctx, [
    'author' => null,
    'role' => null,
    'avatar' => null,
    'cite' => null,
]));

$citeUrl = safe_url(is_string($cite) ? $cite : null);
$avatarUrl = safe_url(is_string($avatar) ? $avatar : null);
?>
<figure data-slot="quote" <?= $attributes->twMerge('text-foreground relative max-w-2xl') ?>>
    <svg class="text-muted-foreground/25 mb-3 size-8" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M9.5 4C6.46 4 4 6.46 4 9.5c0 3.04 2.46 5.5 5.5 5.5.17 0 .33-.01.5-.03V15c0 2.21-1.79 4-4 4a1 1 0 1 0 0 2c3.31 0 6-2.69 6-6V9.5C12 6.46 9.54 4 9.5 4Zm10 0C16.46 4 14 6.46 14 9.5c0 3.04 2.46 5.5 5.5 5.5.17 0 .33-.01.5-.03V15c0 2.21-1.79 4-4 4a1 1 0 1 0 0 2c3.31 0 6-2.69 6-6V9.5C22 6.46 19.54 4 19.5 4Z" />
    </svg>

    <blockquote<?php if ($citeUrl !== null): ?> cite="<?= e($citeUrl) ?>"<?php endif; ?> class="text-lg leading-relaxed font-medium text-balance sm:text-xl">
        <?= $slot ?>
    </blockquote>

    <?php if ($author || $role || $avatar): ?>
        <figcaption class="mt-5 flex items-center gap-3">
            <?php if ($avatarUrl !== null): ?>
                <img src="<?= e($avatarUrl) ?>" alt="<?= e($author) ?>" loading="lazy" class="size-10 shrink-0 rounded-full object-cover" />
            <?php endif; ?>
            <div class="text-sm">
                <?php if ($author): ?><div class="font-semibold"><?= e($author) ?></div><?php endif; ?>
                <?php if ($role): ?><div class="text-muted-foreground"><?= e($role) ?></div><?php endif; ?>
            </div>
        </figcaption>
    <?php endif; ?>
</figure>
