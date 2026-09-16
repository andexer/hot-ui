<?php

declare(strict_types=1);

extract(props($__ctx, [
    'size' => 'default',
    'color' => null,
    'rows' => null,
    'maxRows' => null,
]));

$base = 'border-input placeholder:text-muted-foreground focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive dark:bg-input/30 flex field-sizing-content w-full rounded-md border bg-transparent shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50';

$sizes = [
    'sm' => 'min-h-14 px-2.5 py-1.5 text-sm',
    'default' => 'min-h-16 px-3 py-2 text-base md:text-sm',
    'lg' => 'min-h-20 px-4 py-2.5 text-base',
];

$capped = $maxRows !== null;
$classes = ($capped
    ? str_replace('field-sizing-content', 'resize-none overflow-hidden', $base)
    : $base).' '.($sizes[$size] ?? $sizes['default']);

$colorStyle = $color ? "--ring: {$color}; --primary: {$color}; --primary-foreground: #ffffff;" : '';
$userStyle = (string) $attributes->get('style', '');
$style = trim($colorStyle.($colorStyle && $userStyle ? ' ' : '').$userStyle);
$attributes = $attributes->except('style');
?>
<textarea
    data-slot="textarea"
    data-size="<?= e($size) ?>"
    <?php if ($rows !== null): ?>rows="<?= (int) $rows ?>"
    <?php endif; ?>
    <?php if ($style): ?>style="<?= e($style) ?>"
    <?php endif; ?>
    <?php if ($capped): ?>
        x-data="{
            maxRows: <?= (int) $maxRows ?>,
            resize() {
                const el = this.$el;
                
                el.style.height = 'auto';
                let target = el.scrollHeight;
                const cs = getComputedStyle(el);
                let lh = parseFloat(cs.lineHeight);
                if (isNaN(lh)) lh = parseFloat(cs.fontSize) * 1.2;
                const pad = parseFloat(cs.paddingTop) + parseFloat(cs.paddingBottom);
                const border = parseFloat(cs.borderTopWidth) + parseFloat(cs.borderBottomWidth);
                const cap = (lh * this.maxRows) + pad + border;
                if (target > cap) {
                    target = cap;
                    el.style.overflowY = 'auto';
                } else {
                    el.style.overflowY = 'hidden';
                }
                el.style.height = target + 'px';
            },
        }"
        x-init="$nextTick(() => resize())"
        @input="resize()"
    <?php endif; ?>
    <?= $attributes->twMerge($classes) ?>><?= $slot ?></textarea>
