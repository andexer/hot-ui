<?php

declare(strict_types=1);

extract(props($__ctx, [
    'src' => null,
    'poster' => null,
    'aspect' => 'video',
    'controls' => true,
    'autoplay' => false,
    'loop' => false,
    'muted' => false,
    'rounded' => 'rounded-xl',
]));

$posterUrl = is_string($poster) && $poster !== '' ? safe_url($poster) : null;
$srcUrl = is_string($src) && $src !== '' ? safe_url($src) : null;

$aspectClass = match ($aspect) {
    'square' => 'aspect-square',
    'video' => 'aspect-video',
    default => '',
};
$aspectStyle = $aspectClass === '' ? "aspect-ratio: {$aspect};" : '';
?>
<div
    data-slot="video"
    x-data="{ started: <?= $autoplay ? 'true' : 'false' ?> }"
    <?= $attributes->twMerge('group bg-muted relative overflow-hidden border '.$rounded.' '.$aspectClass) ?>
    <?php if ($aspectStyle): ?>style="<?= e($aspectStyle) ?>"<?php endif; ?>
>
    <video
        x-ref="player"
        class="size-full object-cover"
        <?php if ($posterUrl): ?>poster="<?= e($posterUrl) ?>"<?php endif; ?>
        <?php if ($controls): ?>controls<?php endif; ?>
        <?php if ($autoplay): ?>autoplay<?php endif; ?>
        <?php if ($loop): ?>loop<?php endif; ?>
        <?php if ($muted): ?>muted<?php endif; ?>
        playsinline
        preload="metadata"
        @play="started = true"
        @ended="started = false"
    >
        <?php if ($srcUrl): ?><source src="<?= e($srcUrl) ?>" /><?php endif; ?>
        <?= $slot ?>
        Your browser does not support the video tag.
    </video>

    <?php if (! $autoplay): ?>
        <button
            type="button"
            x-show="! started"
            x-transition.opacity
            @click="$refs.player.play()"
            aria-label="Play video"
            class="absolute inset-0 grid place-items-center bg-black/20 outline-none transition-colors hover:bg-black/30 focus-visible:bg-black/30"
        >
            <span class="grid size-16 place-items-center rounded-full bg-white/90 text-black shadow-lg transition-transform group-hover:scale-105">
                <i data-lucide="play" class="size-7 translate-x-0.5 fill-current"></i>
            </span>
        </button>
    <?php endif; ?>
</div>
