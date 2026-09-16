<?php

declare(strict_types=1);

extract(props($__ctx, [
    'value' => null,
    'depth' => 0,
    'expanded' => true,
    'keyName' => null,
    'isLast' => true,
]));

$isAssoc = is_array($value) && (array_keys($value) !== range(0, count($value) - 1) || $value === []);

$isObject = is_array($value) && $isAssoc;
$isArray = is_array($value) && ! $isAssoc;
$isContainer = $isObject || $isArray;

$open = $isObject ? '{' : '[';
$close = $isObject ? '}' : ']';

$hasKey = $keyName !== null;
$comma = $isLast ? '' : ',';

$pad = 'padding-inline-start:'.((int) $depth * 1).'rem';

if ($isContainer) {
    $count = count($value);
} else {

    if (is_string($value)) {
        $token = '"'.$value.'"';
        $tokenClass = 'text-emerald-700 dark:text-emerald-400';
    } elseif (is_bool($value)) {
        $token = $value ? 'true' : 'false';
        $tokenClass = 'text-purple-700 dark:text-purple-400';
    } elseif (is_null($value)) {
        $token = 'null';
        $tokenClass = 'text-purple-700 dark:text-purple-400';
    } else {

        $token = (string) $value;
        $tokenClass = 'text-amber-700 dark:text-amber-400';
    }
}
?>
<?php if ($isContainer): ?>
    <div
        data-slot="json-viewer-node"
        x-data="{ open: <?= js($expanded) ?> }"
        class="text-muted-foreground"
        style="<?= e($pad) ?>"
    >
        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open"
            class="hover:bg-accent/60 focus-visible:ring-ring/50 -mx-1 inline-flex max-w-full items-baseline gap-1 rounded px-1 text-start align-baseline outline-none focus-visible:ring-2"
        >
            <i data-lucide="chevron-right" class="text-muted-foreground size-3 shrink-0 self-center transition-transform" ::class="open && 'rotate-90'" aria-hidden="true"></i>
            <?php if ($hasKey): ?>
                <?php if (is_int($keyName)): ?>
                    <span class="text-muted-foreground"><?= e($keyName) ?></span><span class="text-muted-foreground">:</span>
                <?php else: ?>
                    <span class="text-sky-700 dark:text-sky-400">"<?= e($keyName) ?>"</span><span class="text-muted-foreground">:</span>
                <?php endif; ?>
            <?php endif; ?>
            <span class="text-muted-foreground"><?= e($open) ?></span>
            <span x-show="!open" x-cloak class="text-muted-foreground">
                …<?= e($close) ?><?= e($comma) ?>
                <span class="text-muted-foreground italic"><?= e($count) ?> <?= $isObject ? ($count === 1 ? 'key' : 'keys') : ($count === 1 ? 'item' : 'items') ?></span>
            </span>
        </button>

        <div x-show="open" x-cloak>
            <?php $lastChildKey = array_key_last($value); ?>
            <?php foreach ($value as $childKey => $childValue): ?>
                <?= $this->uiJsonViewerNode([
                    'value' => $childValue,
                    'depth' => $depth + 1,
                    'expanded' => $expanded,
                    'keyName' => $childKey,
                    'isLast' => $childKey === $lastChildKey,
                ]) ?>
            <?php endforeach; ?>
            <div class="text-muted-foreground" style="padding-inline-start:<?= e((int) $depth * 1) ?>rem"><?= e($close) ?><?= e($comma) ?></div>
        </div>
    </div>
<?php else: ?>
    <div data-slot="json-viewer-leaf" class="text-muted-foreground" style="<?= e($pad) ?>">
        <?php if ($hasKey): ?>
            <?php if (is_int($keyName)): ?>
                <span class="text-muted-foreground"><?= e($keyName) ?></span><span class="text-muted-foreground">:</span>
            <?php else: ?>
                <span class="text-sky-700 dark:text-sky-400">"<?= e($keyName) ?>"</span><span class="text-muted-foreground">:</span>
            <?php endif; ?>
        <?php endif; ?>
        <span class="<?= e($tokenClass) ?>"><?= e($token) ?></span><span class="text-muted-foreground"><?= e($comma) ?></span>
    </div>
<?php endif; ?>
