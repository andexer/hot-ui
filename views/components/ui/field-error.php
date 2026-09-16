<?php

declare(strict_types=1);

extract(props($__ctx, [
    'messages' => null,
]));

$isBlank = static fn (mixed $v): bool => $v === null || $v === [] || (is_string($v) && trim($v) === '');

$items = array_values(array_unique(array_filter(
    array_map(
        static fn ($m) => is_array($m) ? ($m['message'] ?? null) : $m,
        is_array($messages) ? $messages : ($messages !== null && $messages !== '' ? [$messages] : []),
    ),
    static fn ($m) => ! $isBlank($m),
), SORT_REGULAR));

$hasSlot = trim((string) $slot) !== '';
?>
<?php if ($hasSlot || $items !== []): ?>
<div role="alert" data-slot="field-error" <?= $attributes->twMerge('text-destructive text-sm font-normal') ?>>
    <?php if ($hasSlot): ?>
        <?= $slot ?>
    <?php elseif (count($items) === 1): ?>
        <?= e($items[0]) ?>
    <?php else: ?>
        <ul class="ms-4 flex list-disc flex-col gap-1">
            <?php foreach ($items as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<?php endif; ?>
