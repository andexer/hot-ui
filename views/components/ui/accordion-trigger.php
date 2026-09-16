<?php

declare(strict_types=1);

extract(props($__ctx, [
    'icon' => 'chevron',
    'iconPosition' => 'right',
]));

$iconCls = 'text-muted-foreground pointer-events-none size-4 shrink-0 transition-transform duration-200'
    .($iconPosition === 'left' ? ' order-first' : '');
?>
<h3 class="flex">
    <button
        type="button"
        data-slot="accordion-trigger"
        @click="toggle(_v)"
        :id="$id('hot-accordion-trigger', _v)"
        :aria-controls="$id('hot-accordion-panel', _v)"
        :data-state="isOpen(_v) ? 'open' : 'closed'"
        :aria-expanded="isOpen(_v)"
        <?= $attributes->twMerge('focus-visible:border-ring focus-visible:ring-ring/50 flex flex-1 cursor-pointer items-start justify-between gap-4 rounded-md py-4 text-start text-sm font-medium transition-all outline-none hover:underline focus-visible:ring-[3px] disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50') ?>
    >
        <?= $slot ?>

<?php if ($icon === 'none'): ?><?php elseif ($icon === 'plus-minus'): ?>
        <i data-lucide="plus" class="<?= e($iconCls.' [[data-state=open]>&]:hidden') ?>"></i>
        <i data-lucide="minus" class="<?= e($iconCls.' hidden [[data-state=open]>&]:block') ?>"></i>
<?php elseif ($icon === 'plus'): ?>
        <i data-lucide="plus" class="<?= e($iconCls.' [[data-state=open]>&]:rotate-45') ?>"></i>
<?php elseif ($icon === 'chevron-updown'): ?>
        <i data-lucide="chevron-down" class="<?= e($iconCls.' [[data-state=open]>&]:hidden') ?>"></i>
        <i data-lucide="chevron-up" class="<?= e($iconCls.' hidden [[data-state=open]>&]:block') ?>"></i>
<?php elseif ($icon === 'chevron-left'): ?>
        <i data-lucide="chevron-left" class="<?= e($iconCls.' [[data-state=open]>&]:-rotate-90') ?>"></i>
<?php else: ?>
        <i data-lucide="chevron-down" class="<?= e($iconCls.' translate-y-0.5 [[data-state=open]>&]:rotate-180') ?>"></i>
<?php endif; ?>
    </button>
</h3>
