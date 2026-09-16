<?php

declare(strict_types=1);

extract(props($__ctx, [
    'title' => null,
    'href' => null,
    'image' => null,
    'imageAlt' => '',
    'price' => null,
    'compareAt' => null,
    'currency' => '$',
    'badge' => null,
    'category' => null,
    'rating' => null,
    'reviews' => null,
    'wishlist' => false,
]));

$alt = $imageAlt !== '' ? $imageAlt : $title;

$badgeKey = mb_strtolower((string) $badge);
$badgeTone = match (true) {
    str_contains($badgeKey, 'sale'), str_contains($badgeKey, 'off'), str_contains($badgeKey, '%') => 'danger',
    str_contains($badgeKey, 'new') => 'success',
    default => 'neutral',
};

$badgeOverride = match ($badgeTone) {
    'danger' => 'bg-red-700 text-white border-transparent',
    'success' => 'bg-emerald-700 text-white border-transparent',
    default => '',
};

$imageUrl = safe_url(is_string($image) ? $image : null);
$linkTarget = safe_url(is_string($href) ? $href : null);

$slotContent = trim((string) $slot);
?>
<div
    data-slot="product-card"
    <?= $attributes->twMerge('group bg-card text-card-foreground flex flex-col overflow-hidden rounded-xl border shadow-sm') ?>
>
    <div class="bg-muted relative aspect-square overflow-hidden">
        <img
            src="<?= e($imageUrl ?? '') ?>"
            alt="<?= e($alt) ?>"
            loading="lazy"
            class="size-full rounded-t-xl object-cover transition-transform duration-300 group-hover:scale-105"
        />

        <?php if ($badge): ?>
            <div class="absolute start-2 top-2">
                <?= $this->uiBadge([
                    'tone' => $badgeTone,
                    'variant' => 'solid',
                    'class' => $badgeOverride,
                ], e((string) $badge)) ?>
            </div>
        <?php endif; ?>

        <?php if ($wishlist): ?>
            <button
                type="button"
                x-data="{ active: false }"
                @click="active = !active"
                :aria-pressed="active.toString()"
                aria-label="Add to wishlist"
                class="bg-background/80 text-foreground hover:bg-background absolute end-2 top-2 inline-flex size-8 cursor-pointer items-center justify-center rounded-full border shadow-sm backdrop-blur transition-colors outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px]"
            >
                <i data-lucide="heart" class="size-4 transition-colors" x-bind:class="active ? 'fill-red-500 text-red-500' : 'fill-none'" aria-hidden="true"></i>
            </button>
        <?php endif; ?>
    </div>

    <div class="flex flex-1 flex-col gap-2 p-4">
        <?php if ($category): ?>
            <p class="text-muted-foreground text-xs"><?= e($category) ?></p>
        <?php endif; ?>

        <h3 class="text-sm leading-snug font-medium">
            <?php if ($linkTarget !== null): ?>
                <a href="<?= e($linkTarget) ?>" class="rounded-sm outline-none hover:underline focus-visible:ring-ring/50 focus-visible:ring-[3px]"><?= e($title) ?></a>
            <?php else: ?>
                <?= e($title) ?>
            <?php endif; ?>
        </h3>

        <?php if ($rating !== null): ?>
            <div class="flex items-center gap-2 text-sm">
                <?= $this->uiRating([
                    'value' => $rating,
                    'readonly' => true,
                    'size' => 'sm',
                    'name' => 'Rated '.$rating.' out of 5',
                ]) ?>
                <?php if ($reviews !== null): ?>
                    <span class="text-muted-foreground">(<?= e($reviews) ?>)</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($price !== null): ?>
            <div class="mt-auto pt-1">
                <?= $this->uiPrice([
                    'amount' => $price,
                    'compareAt' => $compareAt,
                    'currency' => $currency,
                    'size' => 'lg',
                ]) ?>
            </div>
        <?php endif; ?>

        <div class="mt-3">
            <?php if ($slotContent !== ''): ?>
                <?= $slot ?>
            <?php else: ?>
                <?= $this->uiButton(['class' => 'w-full'], '<i data-lucide="shopping-cart" aria-hidden="true"></i> Add to cart') ?>
            <?php endif; ?>
        </div>
    </div>
</div>
