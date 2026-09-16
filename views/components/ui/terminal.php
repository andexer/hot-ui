<?php

declare(strict_types=1);

extract(props($__ctx, [

    'title' => null,

    'buttons' => true,
]));

$titleClasses = ($buttons ? 'ms-2' : '').' truncate font-mono text-xs text-zinc-400';
?>
<div
    data-slot="terminal"
    <?= $attributes->twMerge('overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 text-zinc-100 shadow-md') ?>
>
    <div data-slot="terminal-bar" class="flex items-center gap-2 border-b border-white/10 bg-zinc-900 px-3.5 py-2.5">
        <?php if ($buttons): ?>
            <span class="size-3 rounded-full" style="background:#ff5f57"></span>
            <span class="size-3 rounded-full" style="background:#febc2e"></span>
            <span class="size-3 rounded-full" style="background:#28c840"></span>
        <?php endif; ?>
        <?php if ($title): ?>
            <span data-slot="terminal-title" class="<?= e($titleClasses) ?>"><?= e($title) ?></span>
        <?php endif; ?>
    </div>
    <div data-slot="terminal-body" class="overflow-x-auto p-4 font-mono text-[13px] leading-relaxed [&_.prompt]:text-emerald-400 [&_.ok]:text-emerald-400 [&_.dim]:text-zinc-400 [&_.path]:text-cyan-400 [&_.warn]:text-amber-400">
        <?= $slot ?>
    </div>
</div>
