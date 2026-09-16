<?php

declare(strict_types=1);

extract(props($__ctx, [
    'notifications' => [],
    'open' => false,
]));

$feed = [];
foreach (array_values($notifications) as $i => $n) {
    $feed[] = [
        'id' => $i,
        'title' => (string) ($n['title'] ?? ''),
        'body' => isset($n['body']) && $n['body'] !== '' ? (string) $n['body'] : null,
        'time' => (string) ($n['time'] ?? ''),
        'read' => (bool) ($n['read'] ?? false),
        'icon' => isset($n['icon']) && $n['icon'] !== '' ? (string) $n['icon'] : null,
        'avatar' => $n['avatar'] ?? null,
    ];
}

$readState = array_map(static fn (array $n): array => ['read' => $n['read']], $feed);
?>
<div
    data-slot="notification-center"
    x-data="{
        open: <?= js((bool) $open) ?>,
        items: <?= js($readState) ?>,
        get unread() { return this.items.filter(i => ! i.read).length },
        get isEmpty() { return this.items.length === 0 },
        markRead(i) { if (this.items[i]) this.items[i].read = true },
        markAllRead() { this.items.forEach(i => i.read = true) },
        toggle() { this.open ? this.close(false) : this.openPanel() },
        openPanel() { this.open = true; this.$nextTick(() => this.$refs.panel?.focus()) },
        close(returnFocus = true) {
            if (! this.open) return;
            this.open = false;
            if (returnFocus) this.$nextTick(() => this.$refs.trigger?.focus());
        },
    }"
    x-id="['hot-notification-center', 'hot-notification-title']"
    <?= $attributes->twMerge('relative inline-block') ?>
>
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        aria-haspopup="dialog"
        :aria-expanded="open"
        :aria-controls="$id('hot-notification-center')"
        :aria-label="(unread === 0 ? 'Notifications, no unread' : unread === 1 ? 'Notifications, 1 unread' : 'Notifications, ' + unread + ' unread')"
        class="text-foreground hover:bg-accent hover:text-accent-foreground relative inline-flex size-9 items-center justify-center rounded-md outline-none transition-colors focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]"
    >
        <i data-lucide="bell" class="size-5" aria-hidden="true"></i>
        <span
            x-show="unread > 0"
            x-cloak
            x-text="unread > 99 ? '99+' : unread"
            aria-hidden="true"
            class="bg-destructive ring-background absolute -end-1 -top-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full px-1 text-[0.625rem] leading-none font-semibold text-white ring-2 tabular-nums"
        ></span>
    </button>

    <template x-teleport="body">
    <div
        x-show="open"
        x-cloak
        x-ref="panel"
        x-hot-anchor.bottom-end.offset.8.no-size.absolute.pad.5="$refs.trigger"
        @click.outside="close(false)"
        @keydown.escape.prevent.stop="close()"
        x-trap="open"
        :id="$id('hot-notification-center')"
        role="dialog"
        aria-modal="false"
        :aria-labelledby="$id('hot-notification-title')"
        tabindex="-1"
        data-slot="notification-center-panel"
        :data-state="open ? 'open' : 'closed'"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="bg-popover text-popover-foreground fixed z-50 flex max-h-[28rem] w-80 max-w-[calc(100vw-1rem)] origin-top flex-col overflow-hidden rounded-md border shadow-md outline-hidden sm:w-96"
    >
        <div class="flex items-center justify-between gap-2 border-b px-4 py-3">
            <h2 :id="$id('hot-notification-title')" class="text-sm font-semibold">Notifications</h2>
            <button
                type="button"
                @click="markAllRead()"
                :disabled="unread === 0"
                aria-label="Mark all notifications as read"
                class="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 rounded-md text-xs font-medium outline-none transition-colors not-disabled:cursor-pointer focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:opacity-50 [&_svg]:size-3.5"
            >
                <i data-lucide="check-check" aria-hidden="true"></i>
                Mark all read
            </button>
        </div>

        <?php if ($feed === []): ?>
        <div class="flex flex-col items-center justify-center gap-3 px-6 py-12 text-center">
                <span class="bg-muted text-muted-foreground flex size-12 items-center justify-center rounded-full">
                    <i data-lucide="bell-off" class="size-6" aria-hidden="true"></i>
                </span>
                <div class="space-y-1">
                    <p class="text-foreground text-sm font-medium">You're all caught up</p>
                    <p class="text-muted-foreground text-xs">No notifications right now.</p>
                </div>
            </div>
        <?php else: ?>
            <ul role="list" class="min-h-0 flex-1 divide-y overflow-y-auto">
            <?php foreach ($feed as $note): $noteId = (int) $note['id']; ?>
                <li
                    @click="markRead(<?= $noteId ?>)"
                    :class="items[<?= $noteId ?>].read ? 'hover:bg-accent/50' : 'bg-muted/60 hover:bg-muted'"
                    class="relative flex cursor-pointer items-start gap-3 px-4 py-3 ps-5 transition-colors"
                >
                    <span
                        x-show="! items[<?= $noteId ?>].read"
                        aria-hidden="true"
                        class="bg-primary absolute inset-y-0 start-0 w-1"
                    ></span>

                    <?php if (! empty($note['avatar'])): ?>
                        <?php $avatarUrl = safe_url(is_string($note['avatar']) ? $note['avatar'] : null); ?>
                        <img src="<?= e($avatarUrl ?? '') ?>" alt="" class="bg-muted size-9 shrink-0 rounded-full object-cover" />
                    <?php else: ?>
                        <span class="bg-muted text-muted-foreground flex size-9 shrink-0 items-center justify-center rounded-full" aria-hidden="true">
                            <i data-lucide="<?= e($note['icon'] ?? 'bell') ?>" class="size-4"></i>
                        </span>
                    <?php endif; ?>

                    <div class="min-w-0 flex-1">
                        <p class="text-foreground text-sm font-medium"><?= e($note['title']) ?></p>
                        <?php if ($note['body']): ?>
                            <p class="text-muted-foreground mt-0.5 text-xs leading-snug"><?= e($note['body']) ?></p>
                        <?php endif; ?>
                        <p class="text-muted-foreground mt-1 text-[0.6875rem]"><?= e($note['time']) ?></p>
                    </div>

                    <span
                        x-show="! items[<?= $noteId ?>].read"
                        role="img"
                        aria-label="Unread"
                        class="bg-primary mt-1.5 size-2 shrink-0 rounded-full"
                    ></span>
                </li>
            <?php endforeach; ?>
            </ul>

            <div class="shrink-0 border-t p-2">
                <a
                    href="#"
                    class="text-foreground hover:bg-accent hover:text-accent-foreground flex w-full items-center justify-center rounded-md px-3 py-2 text-sm font-medium outline-none transition-colors focus-visible:ring-ring/50 focus-visible:ring-[3px]"
                >
                    View all notifications
                </a>
            </div>
        <?php endif; ?>
    </div>
    </template>
</div>
