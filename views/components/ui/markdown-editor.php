<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => '',
    'placeholder' => 'Write markdown…',
    'rows' => 8,
    'id' => null,
]));

$uid = $id ?: 'hot-md-'.bin2hex(random_bytes(3));
$textareaId = $uid.'-textarea';

?>
<div
    data-slot="markdown-editor"
    x-data="hotMarkdownEditor($hot.model(<?= js((string) $value) ?>))"
    <?= $attributes->twMerge('border-input bg-background flex w-full flex-col overflow-hidden rounded-md border shadow-xs') ?>
>
    <div class="bg-muted/40 flex flex-wrap items-center gap-1 border-b p-1.5">
        <div class="flex items-center gap-0.5" role="group" aria-label="Formatting">
            <button type="button" aria-label="Bold" @click="bold()" class="text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 inline-flex size-7 items-center justify-center rounded-sm outline-none transition-colors focus-visible:ring-[3px]">
                <i data-lucide="bold" class="size-4" aria-hidden="true"></i>
            </button>
            <button type="button" aria-label="Italic" @click="italic()" class="text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 inline-flex size-7 items-center justify-center rounded-sm outline-none transition-colors focus-visible:ring-[3px]">
                <i data-lucide="italic" class="size-4" aria-hidden="true"></i>
            </button>
            <button type="button" aria-label="Inline code" @click="code()" class="text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 inline-flex size-7 items-center justify-center rounded-sm outline-none transition-colors focus-visible:ring-[3px]">
                <i data-lucide="code" class="size-4" aria-hidden="true"></i>
            </button>
            <button type="button" aria-label="Insert link" @click="insertLink()" class="text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 inline-flex size-7 items-center justify-center rounded-sm outline-none transition-colors focus-visible:ring-[3px]">
                <i data-lucide="link" class="size-4" aria-hidden="true"></i>
            </button>
            <span class="bg-border mx-0.5 h-5 w-px" aria-hidden="true"></span>
            <button type="button" aria-label="Bulleted list" @click="ul()" class="text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 inline-flex size-7 items-center justify-center rounded-sm outline-none transition-colors focus-visible:ring-[3px]">
                <i data-lucide="list" class="size-4" aria-hidden="true"></i>
            </button>
            <button type="button" aria-label="Numbered list" @click="ol()" class="text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 inline-flex size-7 items-center justify-center rounded-sm outline-none transition-colors focus-visible:ring-[3px]">
                <i data-lucide="list-ordered" class="size-4" aria-hidden="true"></i>
            </button>
            <button type="button" aria-label="Blockquote" @click="quote()" class="text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 inline-flex size-7 items-center justify-center rounded-sm outline-none transition-colors focus-visible:ring-[3px]">
                <i data-lucide="quote" class="size-4" aria-hidden="true"></i>
            </button>
        </div>

        <div role="tablist" aria-label="Editor view" class="bg-muted text-muted-foreground ms-auto inline-flex h-8 items-center justify-center rounded-md p-0.5">
            <button
                type="button"
                role="tab"
                :aria-selected="view === 'write'"
                :tabindex="view === 'write' ? 0 : -1"
                aria-controls="<?= e($uid) ?>-panel-write"
                id="<?= e($uid) ?>-tab-write"
                @click="view = 'write'"
                @keydown.arrow-right.prevent="view = 'preview'; $nextTick(() => $refs.tabPreview.focus())"
                @keydown.arrow-left.prevent="view = 'write'; $nextTick(() => $refs.tabWrite.focus())"
                x-ref="tabWrite"
                class="focus-visible:ring-ring/50 inline-flex h-7 items-center justify-center rounded-sm px-3 text-sm font-medium outline-none transition-all focus-visible:ring-[3px]"
                :class="view === 'write' ? 'bg-background text-foreground shadow-xs' : 'hover:text-foreground'"
            >
                Write
            </button>
            <button
                type="button"
                role="tab"
                :aria-selected="view === 'preview'"
                :tabindex="view === 'preview' ? 0 : -1"
                aria-controls="<?= e($uid) ?>-panel-preview"
                id="<?= e($uid) ?>-tab-preview"
                @click="view = 'preview'"
                @keydown.arrow-right.prevent="view = 'preview'; $nextTick(() => $refs.tabPreview.focus())"
                @keydown.arrow-left.prevent="view = 'write'; $nextTick(() => $refs.tabWrite.focus())"
                x-ref="tabPreview"
                class="focus-visible:ring-ring/50 inline-flex h-7 items-center justify-center rounded-sm px-3 text-sm font-medium outline-none transition-all focus-visible:ring-[3px]"
                :class="view === 'preview' ? 'bg-background text-foreground shadow-xs' : 'hover:text-foreground'"
            >
                Preview
            </button>
        </div>
    </div>

    <div role="tabpanel" id="<?= e($uid) ?>-panel-write" aria-labelledby="<?= e($uid) ?>-tab-write" x-show="view === 'write'">
        <label for="<?= e($textareaId) ?>" class="sr-only">Markdown source</label>
        <textarea
            x-ref="textarea"
            x-model="source"
            id="<?= e($textareaId) ?>"
<?php if ($name): ?> name="<?= e($name) ?>"<?php endif; ?>
            rows="<?= e((int) $rows) ?>"
            placeholder="<?= e($placeholder) ?>"
            aria-label="Markdown source"
            class="placeholder:text-muted-foreground focus-visible:ring-ring/50 block w-full resize-y border-0 bg-transparent px-3 py-2.5 font-mono text-sm leading-6 outline-none focus-visible:ring-2 focus-visible:ring-inset"
        ></textarea>
    </div>

    <div
        role="tabpanel"
        id="<?= e($uid) ?>-panel-preview"
        aria-labelledby="<?= e($uid) ?>-tab-preview"
        x-show="view === 'preview'"
        x-cloak
        class="text-foreground min-h-32 px-4 py-3 text-sm break-words [&_ol]:my-3 [&_pre]:my-3 [&_ul]:my-3"
        x-html="html"
    ></div>
</div>
