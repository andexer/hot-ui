<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => '',
    'placeholder' => 'Write something…',
    'id' => null,
]));

$editorId = $id ?: 'rte-'.bin2hex(random_bytes(4));
$labelId = $editorId.'-label';

$tools = [
    ['key' => 'bold',          'icon' => 'bold',           'label' => 'Bold',          'cmd' => 'bold',          'state' => 'bold'],
    ['key' => 'italic',        'icon' => 'italic',         'label' => 'Italic',        'cmd' => 'italic',        'state' => 'italic'],
    ['key' => 'underline',     'icon' => 'underline',      'label' => 'Underline',     'cmd' => 'underline',     'state' => 'underline'],
    ['key' => 'strike',        'icon' => 'strikethrough',  'label' => 'Strikethrough', 'cmd' => 'strikeThrough', 'state' => 'strikeThrough'],
    ['sep' => true],
    ['key' => 'h1',            'icon' => 'heading-1',      'label' => 'Heading 1',     'block' => 'h1',          'state' => null],
    ['key' => 'h2',            'icon' => 'heading-2',      'label' => 'Heading 2',     'block' => 'h2',          'state' => null],
    ['sep' => true],
    ['key' => 'ul',            'icon' => 'list',           'label' => 'Bullet list',   'cmd' => 'insertUnorderedList', 'state' => 'insertUnorderedList'],
    ['key' => 'ol',            'icon' => 'list-ordered',   'label' => 'Numbered list', 'cmd' => 'insertOrderedList',   'state' => 'insertOrderedList'],
    ['sep' => true],
    ['key' => 'link',          'icon' => 'link',           'label' => 'Insert link',   'link' => true,           'state' => null],
    ['key' => 'clear',         'icon' => 'remove-formatting', 'label' => 'Clear formatting', 'clear' => true,    'state' => null],
];

$hasModel = ($modelPath = $attributes->get('data-hot-model')) !== null;
?>
<div
    data-slot="rich-text-editor"
    x-data="hotRichTextEditor()"
    <?= $attributes->twMerge('border-input bg-background focus-within:border-ring focus-within:ring-ring/50 w-full overflow-hidden rounded-md border shadow-xs transition-[color,box-shadow] focus-within:ring-[3px]') ?>
>
    <div
        role="toolbar"
        aria-label="Formatting"
        data-slot="rich-text-editor-toolbar"
        class="bg-muted/40 flex flex-wrap items-center gap-0.5 border-b p-1"
    >
        <?php foreach ($tools as $tool): ?>
            <?php if (isset($tool['sep'])): ?>
                <span aria-hidden="true" class="bg-border mx-1 h-5 w-px self-center"></span>
            <?php else: ?>
                <button
                    type="button"
                    data-slot="rich-text-editor-button"
                    aria-label="<?= e($tool['label']) ?>"
                    <?php if (! empty($tool['state'])): ?>:aria-pressed="!!active['<?= e($tool['state']) ?>']"<?php endif; ?>
                    <?php if (isset($tool['cmd'])): ?>
                        @click="run(<?= js($tool['cmd']) ?>)"
                    <?php elseif (isset($tool['block'])): ?>
                        @click="block(<?= js($tool['block']) ?>)"
                    <?php elseif (isset($tool['link'])): ?>
                        @click="link()"
                    <?php elseif (isset($tool['clear'])): ?>
                        @click="clear()"
                    <?php endif; ?>
                    class="<?= classes([
                        'text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 inline-flex size-8 cursor-pointer items-center justify-center rounded-md outline-none transition-colors focus-visible:ring-[3px]',
                        'aria-pressed:bg-accent aria-pressed:text-accent-foreground' => ! empty($tool['state']),
                    ]) ?>"
                >
                    <i data-lucide="<?= e((string) $tool['icon']) ?>" class="size-4" aria-hidden="true"></i>
                </button>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <div
        x-ref="editor"
        data-slot="rich-text-editor-content"
        contenteditable="true"
        role="textbox"
        aria-multiline="true"
        aria-labelledby="<?= e($labelId) ?>"
        id="<?= e($editorId) ?>"
        data-placeholder="<?= e($placeholder) ?>"
        dir="auto"
        @input="sync()"
        @keyup="refresh()"
        @mouseup="refresh()"
        @focus="refresh()"
        class="min-h-40 w-full max-w-none px-3 py-3 text-sm leading-7 outline-none empty:before:text-muted-foreground empty:before:pointer-events-none empty:before:content-[attr(data-placeholder)] [&_a]:text-primary [&_a]:underline [&_a]:underline-offset-4 [&_h1]:mb-2 [&_h1]:text-2xl [&_h1]:font-semibold [&_h2]:mb-2 [&_h2]:text-xl [&_h2]:font-semibold [&_li]:mt-1 [&_ol]:my-2 [&_ol]:list-decimal [&_ol]:ps-6 [&_p]:my-2 [&_ul]:my-2 [&_ul]:list-disc [&_ul]:ps-6"
    ><?= $value ?></div>

    <span id="<?= e($labelId) ?>" class="sr-only"><?= e($placeholder) ?></span>

    <?php if ($name || $hasModel): ?>
        <textarea x-ref="input" <?php if ($name): ?>name="<?= e($name) ?>"<?php endif; ?> <?php if ($hasModel): ?>data-hot-model="<?= e($modelPath) ?>"<?php endif; ?> class="hidden" aria-hidden="true" tabindex="-1"><?= $value ?></textarea>
    <?php endif; ?>
</div>
