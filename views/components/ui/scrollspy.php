<?php

declare(strict_types=1);

extract(props($__ctx, [
    'items' => [],
]));
?>
<nav
    data-slot="scrollspy"
    aria-label="On this page"
    x-data="hotScrollspy({ items: <?= js(array_values($items)) ?> })"
    <?= $attributes->twMerge('text-sm') ?>
>
    <ul class="flex flex-col gap-1">
        <template x-for="item in items" :key="item.href">
            <li>
                <a
                    :href="item.href"
                    @click="active = idFor(item.href)"
                    :aria-current="active === idFor(item.href) ? 'true' : null"
                    class="block border-s-2 ps-3 py-1 outline-none transition-colors rounded-e-sm focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                    :class="[
                        active === idFor(item.href)
                            ? 'border-primary text-foreground font-medium'
                            : 'border-transparent text-muted-foreground hover:text-foreground',
                        (item.level ?? 1) >= 2 ? 'ps-4' : '',
                    ]"
                    x-text="item.label"
                ></a>
            </li>
        </template>
    </ul>
</nav>
