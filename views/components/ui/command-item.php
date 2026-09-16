<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => null,
    'href' => null,
    'disabled' => false,
]));

$kw = $value ?? trim(strip_tags((string) $slot));
$classes = "data-[selected=true]:bg-accent data-[selected=true]:text-accent-foreground hover:bg-accent hover:text-accent-foreground [&_svg:not([class*='text-'])]:text-muted-foreground relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-hidden select-none data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4";
$kwJs = js($kw);
$linkTarget = safe_url(is_string($href) ? $href : null);
?>
<?php if ($href): ?>
    <a
        href="<?= e((string) $linkTarget) ?>"
        role="option"
        data-slot="command-item"
        x-init="registerItem($el, <?= $kwJs ?>, <?= js((bool) $disabled) ?>)"
        x-show="matches(<?= $kwJs ?>)"
        :aria-selected="activeId === $el.id"
        :data-selected="activeId === $el.id"
        @mouseenter="activeId = $el.id"
        <?php if ($disabled): ?>aria-disabled="true" data-disabled="true"<?php endif; ?>
        <?= $attributes->twMerge($classes) ?>
    ><?= $slot ?></a>
<?php else: ?>
    <div
        role="option"
        data-slot="command-item"
        x-init="registerItem($el, <?= $kwJs ?>, <?= js((bool) $disabled) ?>)"
        x-show="matches(<?= $kwJs ?>)"
        :aria-selected="activeId === $el.id"
        :data-selected="activeId === $el.id"
        @mouseenter="activeId = $el.id"
        <?php if ($disabled): ?>aria-disabled="true" data-disabled="true"<?php endif; ?>
        <?= $attributes->twMerge($classes) ?>
    ><?= $slot ?></div>
<?php endif; ?>
