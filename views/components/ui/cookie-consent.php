<?php

declare(strict_types=1);

extract(props($__ctx, [
    'position' => 'bottom',
    'customizable' => false,
    'demo' => false,
]));

$categories = [
    'necessary'  => ['label' => 'Strictly necessary', 'locked' => true,  'default' => true],
    'analytics'  => ['label' => 'Analytics',          'locked' => false, 'default' => false],
    'marketing'  => ['label' => 'Marketing',          'locked' => false, 'default' => false],
];

$positions = [
    'bottom'       => 'fixed inset-x-0 bottom-0 mx-auto mb-4 w-[min(100%-2rem,42rem)]',
    'bottom-start' => 'fixed bottom-4 start-4 w-[min(100%-2rem,26rem)]',
    'bottom-end'   => 'fixed bottom-4 end-4 w-[min(100%-2rem,26rem)]',
    'top'          => 'fixed inset-x-0 top-0 mx-auto mt-4 w-[min(100%-2rem,42rem)]',
];
$place = $demo ? 'w-full' : ($positions[$position] ?? $positions['bottom']).' z-50';

$storeKey = 'hot-cookie-consent';

$catState = [];
foreach ($categories as $key => $cat) {
    $catState[$key] = (bool) $cat['default'];
}

$lockedKeys = array_keys(array_filter($categories, static fn (array $c): bool => (bool) $c['locked']));
?>
<div
    data-slot="cookie-consent"
    x-data="{
        demo: <?= js((bool) $demo) ?>,
        storeKey: <?= js($storeKey) ?>,
        show: true,
        customizing: false,
        cats: <?= js($catState) ?>,
        locked: <?= js($lockedKeys) ?>,
        init() {
            
            if (this.demo) { this.show = true; return; }
            try {
                this.show = !JSON.parse(localStorage.getItem(this.storeKey) || 'null')?.set;
            } catch (e) { this.show = true; }
        },
        save(choice) {
            this.show = false;
            if (this.demo) return;
            try { localStorage.setItem(this.storeKey, JSON.stringify({ ...choice, set: true })); } catch (e) {}
        },
        acceptAll() {
            let all = {};
            for (const k in this.cats) all[k] = true;
            this.cats = all;
            this.save(all);
        },
        reject() {
            
            let only = {};
            for (const k in this.cats) only[k] = this.locked.includes(k);
            this.cats = only;
            this.save(only);
        },
        savePrefs() { this.save({ ...this.cats }); },
    }"
    x-show="show"
    x-cloak
    role="region"
    aria-label="Cookie consent"
    <?= $attributes->twMerge('bg-card text-card-foreground rounded-xl border p-4 shadow-lg sm:p-5 '.$place) ?>
>
    <div class="flex flex-col gap-3">
        <div class="flex items-start gap-3">
            <span class="bg-muted text-muted-foreground hidden size-9 shrink-0 items-center justify-center rounded-lg sm:flex" aria-hidden="true">
                <i data-lucide="cookie" class="size-5"></i>
            </span>
            <div class="space-y-1 text-sm">
                <p class="font-medium">We value your privacy</p>
                <p class="text-muted-foreground">
                    <?= $slot->isEmpty()
                        ? 'We use cookies to enhance your experience, analyze traffic, and personalize content. You can accept all, reject non-essential, or manage your preferences.'
                        : $slot ?>
                    <a href="#" class="text-foreground font-medium underline underline-offset-2 outline-none focus-visible:ring-ring/50 focus-visible:ring-[3px] rounded-sm">Cookie policy</a>
                </p>
            </div>
        </div>

        <?php if ($customizable): ?>
            <div x-show="customizing" x-cloak class="border-border space-y-2.5 rounded-lg border p-3">
                <?php foreach ($categories as $key => $cat): ?>
                    <div class="flex items-center justify-between gap-3">
                        <label for="cc-<?= e($key) ?>" class="<?= classes(['text-sm', 'text-muted-foreground' => $cat['locked']]) ?>">
                            <?= e($cat['label']) ?>
                            <?php if ($cat['locked']): ?>
                                <span class="text-muted-foreground/70 text-xs">(always on)</span>
                            <?php endif; ?>
                        </label>
                        <button
                            type="button"
                            id="cc-<?= e($key) ?>"
                            role="switch"
                            <?php if ($cat['locked']): ?>
                            aria-checked="true"
                            aria-label="<?= e($cat['label']) ?> (required, always on)"
                            disabled
                            data-state="checked"
                            <?php else: ?>
                            @click="cats['<?= e($key) ?>'] = !cats['<?= e($key) ?>']"
                            :aria-checked="cats['<?= e($key) ?>']"
                            aria-label="<?= e($cat['label']) ?>"
                            :data-state="cats['<?= e($key) ?>'] ? 'checked' : 'unchecked'"
                            <?php endif; ?>
                            class="peer data-[state=checked]:bg-primary data-[state=unchecked]:bg-input focus-visible:border-ring focus-visible:ring-ring/50 dark:data-[state=unchecked]:bg-input/80 inline-flex h-[1.15rem] w-8 shrink-0 items-center rounded-full border border-transparent shadow-xs transition-all outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span
                                <?php if ($cat['locked']): ?>data-state="checked"<?php else: ?>:data-state="cats['<?= e($key) ?>'] ? 'checked' : 'unchecked'"<?php endif; ?>
                                class="bg-background dark:data-[state=unchecked]:bg-foreground dark:data-[state=checked]:bg-primary-foreground pointer-events-none block size-4 rounded-full ring-0 transition-transform data-[state=checked]:translate-x-[calc(100%-2px)] data-[state=unchecked]:translate-x-0"
                            ></span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-end">
            <?php if ($customizable): ?>
                <button
                    type="button"
                    x-show="!customizing"
                    @click="customizing = true"
                    class="text-sm font-medium underline-offset-4 outline-none transition-colors hover:underline focus-visible:ring-ring/50 focus-visible:ring-[3px] rounded-md px-2 py-1 sm:me-auto"
                >Customize</button>
                <button
                    type="button"
                    x-show="customizing"
                    x-cloak
                    @click="savePrefs()"
                    class="border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50 inline-flex h-9 items-center justify-center rounded-md px-4 py-2 text-sm font-medium outline-none transition-all focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] sm:me-auto"
                >Save preferences</button>
            <?php endif; ?>

            <button
                type="button"
                @click="reject()"
                class="border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input dark:hover:bg-input/50 inline-flex h-9 items-center justify-center rounded-md px-4 py-2 text-sm font-medium outline-none transition-all focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
            >Reject</button>
            <button
                type="button"
                @click="acceptAll()"
                class="bg-primary text-primary-foreground shadow-xs hover:bg-primary/90 inline-flex h-9 items-center justify-center rounded-md px-4 py-2 text-sm font-medium outline-none transition-all focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
            >Accept all</button>
        </div>
    </div>
</div>
