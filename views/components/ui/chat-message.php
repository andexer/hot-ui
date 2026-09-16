<?php

declare(strict_types=1);

extract(props($__ctx, [
    'role' => 'assistant',
    'name' => null,
    'time' => null,
    'avatar' => null,
    'typing' => false,
]));

$isUser = $role === 'user';

$initials = '';
if ($name) {
    $parts = array_values(array_filter(preg_split('/\s+/', trim((string) $name))));
    if (count($parts) >= 2) {
        $initials = strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[count($parts) - 1], 0, 1));
    } elseif (count($parts) === 1) {
        $initials = strtoupper(mb_substr($parts[0], 0, 2));
    }
}

$avatarUrl = is_string($avatar) || $avatar instanceof \Stringable ? safe_url((string) $avatar) : null;
?>
<div
    data-slot="chat-message"
    class="<?= classes(['flex items-end gap-2', 'flex-row-reverse' => $isUser]) ?>"
    <?= $attributes ?>
>
<?= $this->uiAvatar(['class' => classes(['shrink-0', 'bg-muted' => ! $avatar])], function () use ($avatar, $name, $initials, $isUser, $avatarUrl): void { ?>
        <?php if ($avatar && $avatarUrl !== null): ?>
            <?= $this->uiAvatarImage(['src' => $avatarUrl, 'alt' => $name ? $name : '']) ?>
            <?= $this->uiAvatarFallback([], e($initials ?: '?')) ?>
        <?php elseif ($initials): ?>
            <?= $this->uiAvatarFallback(['class' => 'text-foreground'], e($initials)) ?>
        <?php else: ?>
            <?= $this->uiAvatarFallback(['class' => 'text-muted-foreground'], '<i data-lucide="'.e($isUser ? 'user' : 'bot').'" class="size-4" aria-hidden="true"></i>') ?>
        <?php endif; ?>
    <?php }) ?>

<div class="<?= classes([
        'flex min-w-0 flex-col gap-1',
        'items-end' => $isUser,
        'items-start' => ! $isUser,
    ]) ?>">
        <?php if ($name || $time): ?>
            <div class="flex items-center gap-2 px-1 text-xs text-muted-foreground">
                <?php if ($name): ?>
                    <span class="font-medium"><?= e($name) ?></span>
                <?php endif; ?>
                <?php if ($time): ?>
                    <span><?= e($time) ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="<?= classes([
            'max-w-[80%] rounded-2xl px-3.5 py-2 text-sm break-words',
            'bg-primary text-primary-foreground rounded-ee-sm' => $isUser,
            'bg-muted text-foreground rounded-es-sm' => ! $isUser,
        ]) ?>">
            <?php if ($typing): ?>
                <span class="flex items-center gap-1 py-1" role="status" aria-label="<?= e($name ? $name.' is typing' : 'Typing') ?>">
                    <span class="size-2 animate-bounce rounded-full bg-current opacity-60 [animation-delay:-0.3s]" aria-hidden="true"></span>
                    <span class="size-2 animate-bounce rounded-full bg-current opacity-60 [animation-delay:-0.15s]" aria-hidden="true"></span>
                    <span class="size-2 animate-bounce rounded-full bg-current opacity-60" aria-hidden="true"></span>
                </span>
            <?php else: ?>
                <?= $slot ?>
            <?php endif; ?>
        </div>
    </div>
</div>
