<?php

declare(strict_types=1);

extract(props($__ctx, [
    'icon' => null,
    'label' => null,
    'href' => '#',
    'active' => false,
]));

$isLink = $href && $href !== '#';
$tag = $isLink ? 'a' : 'button';
$linkTarget = safe_url(is_string($href) ? $href : null);
?>
<div data-slot="dock-item" class="group/dock-item relative flex flex-col items-center">
    <?php if ($label): ?>
        <span
            data-slot="dock-item-tooltip"
            role="tooltip"
            class="bg-popover text-popover-foreground pointer-events-none absolute bottom-full mb-2 scale-95 rounded-md border px-2 py-1 text-xs font-medium whitespace-nowrap opacity-0 shadow-md transition-[opacity,transform] group-hover/dock-item:scale-100 group-hover/dock-item:opacity-100 group-focus-within/dock-item:scale-100 group-focus-within/dock-item:opacity-100 motion-reduce:transition-none"
        ><?= e($label) ?></span>
    <?php endif; ?>

    <<?= $tag ?>
        x-data="{
            scale: 1,
            update() {
                const r = this.$el.getBoundingClientRect();

                const center = r.left + (r.width / this.scale) / 2;
                this.scale = this.scaleFor(center);
            },
        }"
        x-init="update()"
        @mousemove.window="update()"
        @mouseleave.window="scale = 1"
        :style="`transform: scale(${scale}); transform-origin: bottom center;`"
        data-slot="dock-item-control"
        <?php if ($isLink && $linkTarget !== null): ?>href="<?= e($linkTarget) ?>"<?php else: ?>type="button"<?php endif; ?>
        <?php if ($label): ?>aria-label="<?= e($label) ?>"<?php endif; ?>
        <?php if ($active): ?>aria-current="page"<?php endif; ?>
        <?= $attributes->twMerge('bg-muted text-foreground/80 hover:text-foreground focus-visible:ring-ring/50 flex size-11 items-center justify-center rounded-xl outline-none transition-transform duration-150 ease-out will-change-transform focus-visible:ring-[3px] motion-reduce:!transform-none motion-reduce:transition-none [&_svg]:size-6') ?>
    >
        <?php if ($icon): ?>
            <i data-lucide="<?= e($icon) ?>" aria-hidden="true"></i>
        <?php endif; ?>
        <?= $slot ?>
    </<?= $tag ?>>

    <?php if ($active): ?>
        <span data-slot="dock-item-dot" class="bg-foreground/70 absolute -bottom-1 size-1 rounded-full" aria-hidden="true"></span>
    <?php endif; ?>
</div>
