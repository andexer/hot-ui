<?php

declare(strict_types=1);

extract(props($__ctx, [
    'filename' => null,
]));
?>
<div
    data-slot="code-block"
    x-data="{ copied: false, copy() { navigator.clipboard.writeText(this.$refs.code.innerText); this.copied = true; clearTimeout(this._t); this._t = setTimeout(() => this.copied = false, 1600) } }"
    <?= $attributes->twMerge('group/code-block relative overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950 text-zinc-100 dark:bg-zinc-900') ?>
>
    <?php if ($filename): ?>
        <div data-slot="code-block-header" class="flex items-center justify-between border-b border-white/10 px-4 py-2">
            <span data-slot="code-block-filename" class="flex items-center gap-2 font-mono text-xs text-zinc-400">
                <i data-lucide="file" class="size-3.5" aria-hidden="true"></i> <?= e($filename) ?>
            </span>
            <button type="button" @click="copy()" data-slot="code-block-copy" aria-label="Copy code" class="text-zinc-400 transition-colors hover:text-zinc-100">
                <i data-lucide="copy" class="size-3.5" x-show="!copied"></i>
                <i data-lucide="check" class="size-3.5 text-emerald-400" x-show="copied" x-cloak></i>
            </button>
        </div>
    <?php else: ?>
        <button type="button" @click="copy()" data-slot="code-block-copy" aria-label="Copy code" class="absolute end-2 top-2 z-10 rounded-md p-1.5 text-zinc-400 opacity-0 transition-all hover:bg-white/10 hover:text-zinc-100 focus-visible:opacity-100 group-hover/code-block:opacity-100">
            <i data-lucide="copy" class="size-3.5" x-show="!copied"></i>
            <i data-lucide="check" class="size-3.5 text-emerald-400" x-show="copied" x-cloak></i>
        </button>
    <?php endif; ?>
    <pre data-slot="code-block-pane" class="overflow-x-auto p-4 text-[13px] leading-relaxed"><code x-ref="code" class="font-mono"><?= e(trim((string) $slot)) ?></code></pre>
</div>
