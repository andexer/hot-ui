<?php

declare(strict_types=1);

extract(props($__ctx, [
    'threshold' => 200,
]));

$threshold = max(0, (int) $threshold);
?>
<div
    data-slot="infinite-scroll"
    x-data="hotInfiniteScroll({ threshold: <?= js($threshold) ?> })"
    @load-more-done="$event.detail && $event.detail.done ? finish() : loaded()"
    :aria-busy="loading ? 'true' : 'false'"
    <?= $attributes->twMerge('flex flex-col gap-2') ?>
>
    <?= $slot ?>

    <div x-ref="sentinel" aria-hidden="true" class="h-px w-full" x-show="!finished" x-cloak></div>

    <div
        role="status"
        aria-live="polite"
        x-show="loading"
        x-cloak
        class="flex items-center justify-center gap-2 py-3 text-sm text-muted-foreground"
    >
        <?= $this->uiSpinner(['class' => 'size-4', 'aria-hidden' => 'true']) ?>
        <span>Loading more…</span>
    </div>

    <div x-show="!finished && !loading" class="flex justify-center py-2">
        <?= $this->uiButton([
            'type' => 'button',
            'variant' => 'outline',
            'size' => 'sm',
            'x-on:click' => 'loadMore()',
            'class' => 'focus-visible:ring-ring/50 focus-visible:ring-[3px]',
        ], 'Load more') ?>
    </div>

    <div
        role="status"
        x-show="finished"
        x-cloak
        class="py-3 text-center text-sm text-muted-foreground"
    >
        You’ve reached the end.
    </div>
</div>
