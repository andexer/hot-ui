<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'value' => '#6366f1',
    'swatches' => null,
    'disabled' => false,
    'inline' => false,
    'id' => null,
]));

$palette = $swatches ?: [
    '#ef4444',
    '#f97316',
    '#eab308',
    '#22c55e',
    '#14b8a6',
    '#3b82f6',
    '#6366f1',
    '#a855f7',
    '#ec4899',
    '#111827',
];

$fieldId = $id ?: 'color-picker-'.bin2hex(random_bytes(3));
?>
<div
    data-slot="color-picker"
x-data="hotColorPicker({
        model: $hot.model(<?= js($value) ?>),
        disabled: <?= js((bool) $disabled) ?>,
        inline: <?= js((bool) $inline) ?>,
        input: <?= js($value) ?>,
        swatches: <?= js(array_values((array) $palette)) ?>,
    })"
    x-init="init()"
    <?= $attributes->twMerge('relative inline-block text-start') ?>
>
    <?php if ($name): ?>
        <input type="hidden" name="<?= e($name) ?>" :value="hex">
    <?php endif; ?>

    <template x-if="!inline">
        <button
            type="button"
            :disabled="disabled"
            aria-haspopup="dialog"
            :aria-expanded="open"
            @click="open = !open"
            class="border-input bg-background ring-offset-background hover:bg-accent hover:text-accent-foreground focus-visible:ring-ring inline-flex h-9 items-center gap-2 rounded-md border px-3 text-sm font-medium shadow-xs transition-colors outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
        >
            <span class="border-border/50 size-4 rounded-sm border" :style="`background-color: ${hex}`" aria-hidden="true"></span>
            <span class="font-mono" x-text="hex"></span>
        </button>
    </template>

    <div
        x-show="inline || open"
        x-cloak
        <?php if (! $inline): ?>
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        <?php endif; ?>
        role="dialog"
        aria-label="Choose colour"
        class="<?= classes([
            'bg-popover text-popover-foreground border-border z-50 w-64 rounded-lg border p-4 shadow-md',
            'absolute start-0 top-full mt-2' => ! $inline,
        ]) ?>"
        <?php if (! $inline): ?>style="display: none;"<?php endif; ?>
    >
        <div class="flex flex-col gap-4">
            <div
                class="border-border/50 h-16 w-full rounded-md border"
                :style="`background-color: ${hex}`"
                aria-hidden="true"
            ></div>

            <div class="flex flex-col gap-1.5">
                <input
                    type="range"
                    min="0"
                    max="360"
                    step="1"
                    :value="hue"
                    @input="setHue($event.target.value)"
                    aria-label="Hue"
                    class="focus-visible:ring-ring h-3 w-full cursor-pointer appearance-none rounded-full outline-none focus-visible:ring-2"
                    style="background: linear-gradient(to right, #ff0000 0%, #ffff00 17%, #00ff00 33%, #00ffff 50%, #0000ff 67%, #ff00ff 83%, #ff0000 100%);"
                />
            </div>

            <div class="flex flex-col gap-1.5">
                <label for="<?= e($fieldId) ?>" class="sr-only">Hex colour value</label>
                <input
                    id="<?= e($fieldId) ?>"
                    type="text"
                    inputmode="text"
                    autocomplete="off"
                    spellcheck="false"
                    maxlength="7"
                    x-model="input"
                    @input="commit($event.target.value)"
                    @blur="sync()"
                    :aria-invalid="!isValid"
                    placeholder="#rrggbb"
                    class="border-input bg-transparent dark:bg-input/30 focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-destructive/20 flex h-9 w-full min-w-0 rounded-md border px-3 py-1 font-mono text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:ring-[3px]"
                />
            </div>

            <div class="grid grid-cols-5 gap-2" role="group" aria-label="Preset colours">
                <template x-for="c in swatches" :key="c">
                    <button
                        type="button"
                        @click="pick(c)"
                        :aria-label="c"
                        :aria-pressed="hex === normalize(c)"
                        class="border-border/50 focus-visible:ring-ring aspect-square w-full rounded-md border outline-none focus-visible:ring-2 focus-visible:ring-offset-1"
                        :class="hex === normalize(c) ? 'ring-ring ring-2 ring-offset-1' : ''"
                        :style="`background-color: ${c}`"
                    ></button>
                </template>
            </div>
        </div>
    </div>
</div>
