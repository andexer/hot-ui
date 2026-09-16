<?php

declare(strict_types=1);

extract(props($__ctx, [
    'steps' => [],
    'open' => false,
]));

$normalized = [];
foreach ($steps as $s) {
    $normalized[] = [
        'target' => $s['target'] ?? null,
        'title' => $s['title'] ?? '',
        'body' => $s['body'] ?? '',
        'placement' => $s['placement'] ?? 'bottom',
    ];
}
?>
<div
    data-slot="onboarding-tour"
    x-id="['hot-tour-title', 'hot-tour-body']"
    x-data="hotOnboardingTour({
        steps: <?= js($normalized) ?>,
        open: <?= js((bool) $open) ?>,
    })"
    x-init="if (active) start()"
    @keydown.escape.window="active && end()"
    @resize.window="active && recompute()"
    @scroll.window.passive="active && recompute()"
    <?= $attributes->twMerge('contents') ?>
>
    <?= $slot ?>

    <template x-teleport="body">
        <div x-show="active" x-cloak class="fixed inset-0 z-[100]" role="presentation">
            <div
                x-show="active && hasTarget"
                data-slot="onboarding-tour-spotlight"
                aria-hidden="true"
                class="pointer-events-none fixed rounded-lg ring-2 ring-ring transition-all duration-200 ease-out"
                style="box-shadow: 0 0 0 9999px rgb(0 0 0 / 0.6);"
                :style="`top:${rect.top}px; left:${rect.left}px; width:${rect.width}px; height:${rect.height}px; box-shadow: 0 0 0 9999px rgb(0 0 0 / 0.6);`"
            ></div>

            <div
                x-show="active && !hasTarget"
                data-slot="onboarding-tour-overlay"
                aria-hidden="true"
                class="fixed inset-0 bg-foreground/50"
            ></div>

            <div
                x-ref="cardEl"
                x-show="active"
                x-effect="trap($el, active)"
                tabindex="-1"
                role="dialog"
                aria-modal="true"
                data-slot="onboarding-tour-card"
                :aria-labelledby="$id('hot-tour-title')"
                :aria-describedby="$id('hot-tour-body')"
                :data-placement="card.placement"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="bg-popover text-popover-foreground fixed z-[101] w-[min(20rem,calc(100vw-1.5rem))] rounded-lg border p-4 shadow-lg outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                :style="`top:${card.top}px; left:${card.left}px;`"
            >
                <div class="flex flex-col gap-1.5">
                    <p class="text-muted-foreground text-xs font-medium" aria-hidden="true">
                        <span x-text="index + 1"></span> of <span x-text="count"></span>
                    </p>
                    <h2 :id="$id('hot-tour-title')" data-slot="onboarding-tour-title" class="text-sm leading-none font-semibold" x-text="step.title"></h2>
                    <p :id="$id('hot-tour-body')" data-slot="onboarding-tour-body" class="text-muted-foreground text-sm" x-text="step.body"></p>
                </div>

                <div class="mt-3 flex items-center gap-1.5" aria-hidden="true">
                    <template x-for="i in count" :key="i">
                        <span
                            class="size-1.5 rounded-full transition-colors"
                            :class="(i - 1) === index ? 'bg-primary' : 'bg-muted-foreground/30'"
                        ></span>
                    </template>
                </div>

                <div class="mt-4 flex items-center justify-between gap-2">
                    <button
                        type="button"
                        @click="end()"
                        data-slot="onboarding-tour-skip"
                        class="text-muted-foreground hover:text-foreground inline-flex h-8 items-center rounded-md px-2 text-sm font-medium transition-colors outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                    >Skip</button>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            x-show="!isFirst"
                            @click="back()"
                            data-slot="onboarding-tour-back"
                            class="border-input bg-background hover:bg-accent hover:text-accent-foreground inline-flex h-8 items-center rounded-md border px-3 text-sm font-medium shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                        >Back</button>
                        <button
                            type="button"
                            @click="next()"
                            data-slot="onboarding-tour-next"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 inline-flex h-8 items-center rounded-md px-3 text-sm font-medium shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                        >
                            <span x-show="!isLast">Next</span>
                            <span x-show="isLast">Done</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
