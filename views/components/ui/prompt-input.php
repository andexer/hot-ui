<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'placeholder' => 'Send a message…',
    'attachable' => false,
    'disabled' => false,
    'id' => null,
    'rows' => 1,
    'maxRows' => 6,
]));

$textareaLabel = $attributes->get('aria-label') ?: 'Message';
$attributes = $attributes->except('aria-label');
?>
<div
    data-slot="prompt-input"
    x-data="{
        value: '',
        disabled: <?= js((bool) $disabled) ?>,
        maxRows: <?= js((int) $maxRows) ?>,
        get empty() { return this.value.trim().length === 0; },
        resize(el) {
            
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
        submit() {
            if (this.disabled || this.empty) return;

            const form = this.$root.closest('form');
            if (form) {
                form.requestSubmit ? form.requestSubmit() : form.submit();
            } else {
                this.$root.dispatchEvent(new CustomEvent('submit', {
                    detail: { value: this.value },
                    bubbles: true,
                }));
            }
        },
    }"
    <?= $attributes->twMerge('border-input focus-within:border-ring focus-within:ring-ring/50 dark:bg-input/30 bg-background flex w-full flex-col gap-2 rounded-xl border p-2 shadow-xs transition-[color,box-shadow] focus-within:ring-[3px]') ?>
>
    <textarea
        data-slot="prompt-input-textarea"
        x-model="value"
        x-init="$nextTick(() => resize($el))"
        @input="resize($el)"
        @keydown.enter="if ($event.metaKey || $event.ctrlKey) { $event.preventDefault(); submit(); }"
        rows="<?= (int) $rows ?>"
        aria-label="<?= e($textareaLabel) ?>"
        placeholder="<?= e($placeholder) ?>"
        <?php if ($name): ?>name="<?= e($name) ?>"
        <?php endif; ?>
        <?php if ($id): ?>id="<?= e($id) ?>"
        <?php endif; ?>
        <?php if ($disabled): ?>disabled<?php endif; ?>
        class="placeholder:text-muted-foreground text-foreground flex w-full resize-none overflow-hidden bg-transparent px-2 pt-1 text-base outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
    ></textarea>

    <div data-slot="prompt-input-toolbar" class="flex items-center gap-2">
        <?php if ($attachable): ?>
            <button
                type="button"
                aria-label="Attach file"
                <?php if ($disabled): ?>disabled<?php endif; ?>
                class="text-muted-foreground hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring/50 inline-flex size-8 shrink-0 items-center justify-center rounded-lg outline-none transition-colors focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
            >
                <i data-lucide="paperclip" class="size-4" aria-hidden="true"></i>
            </button>
        <?php endif; ?>

        <div class="flex-1"></div>

        <span data-slot="prompt-input-hint" class="text-muted-foreground hidden text-xs select-none sm:inline" aria-hidden="true">
            <kbd class="font-sans">⌘↵</kbd> to send
        </span>

        <button
            type="button"
            aria-label="Send"
            @click="submit()"
            :disabled="disabled || empty"
            class="bg-primary text-primary-foreground hover:bg-primary/90 focus-visible:ring-ring/50 inline-flex size-8 shrink-0 items-center justify-center rounded-full shadow-xs outline-none transition-all focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50"
        >
            <i data-lucide="arrow-up" class="size-4" aria-hidden="true"></i>
        </button>
    </div>
</div>
