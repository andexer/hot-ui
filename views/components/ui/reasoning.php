<?php

declare(strict_types=1);

extract(props($__ctx, [
    'open' => false,
    'label' => 'Reasoning',
    'duration' => null,
    'id' => null,
]));

$headerLabel = $duration ? 'Thought for '.$duration : $label;
?>
<div
    data-slot="reasoning"
    x-data="{ open: <?= js((bool) $open) ?> }"
    x-id="['hot-reasoning']"
    <?= $attributes->twMerge('w-full text-sm') ?>
>
    <button
        type="button"
        data-slot="reasoning-trigger"
        @click="open = !open"
        :aria-expanded="open"
        <?php if ($id): ?>aria-controls="<?= e($id) ?>"<?php else: ?>:aria-controls="$id('hot-reasoning')"<?php endif; ?>
        :data-state="open ? 'open' : 'closed'"
        class="group inline-flex items-center gap-2 rounded-md text-muted-foreground outline-none transition-colors hover:text-foreground focus-visible:ring-[3px] focus-visible:ring-ring/50"
    >
        <i data-lucide="brain" class="size-4 shrink-0" aria-hidden="true"></i>
        <span class="font-medium"><?= e($headerLabel) ?></span>
        <i
            data-lucide="chevron-down"
            class="size-4 shrink-0 transition-transform duration-200 rtl:-scale-x-100"
            x-bind:class="open ? 'rotate-180' : 'rotate-0'"
            aria-hidden="true"
        ></i>
    </button>

    <div
        data-slot="reasoning-content"
        <?php if ($id): ?>id="<?= e($id) ?>"<?php else: ?>:id="$id('hot-reasoning')"<?php endif; ?>
        x-show="open"
        x-collapse
        x-cloak
        :data-state="open ? 'open' : 'closed'"
        class="mt-2 border-s-2 border-border ps-4 text-sm leading-relaxed text-muted-foreground"
    >
        <?= $slot ?>
    </div>
</div>
