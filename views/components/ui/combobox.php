<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'options' => [],
    'value' => '',
    'placeholder' => null,
    'searchPlaceholder' => null,
    'empty' => null,
    'width' => 'w-[200px]',
    'searchable' => true,
    'disabled' => false,
    'multiple' => false,
    'trigger' => 'button',
    'size' => 'default',
    'icon' => null,
    'indicator' => 'check',
]));

$trigger = in_array($trigger, ['button', 'input'], true) ? $trigger : 'button';
$isInput = $trigger === 'input';
$indicator = in_array($indicator, ['check', 'checkbox', 'radio'], true) ? $indicator : 'check';

$placeholder ??= $isInput ? 'Search...' : 'Select option...';
$searchPlaceholder ??= 'Search...';
$empty ??= 'No results found.';

$opts = [];
foreach ($options as $o) {
    $opts[] = is_array($o)
        ? ['value' => (string) ($o['value'] ?? ''), 'label' => (string) ($o['label'] ?? $o['value'] ?? '')]
        : ['value' => (string) $o, 'label' => (string) $o];
}

if ($multiple) {
    $seedItems = is_array($value) ? $value : (($value === '' || $value === null) ? [] : [$value]);
    $initialValue = array_map(static fn ($v): string => (string) $v, array_values($seedItems));
} else {
    $initialValue = (string) $value;
}

$selectedSingle = null;
if (! $multiple) {
    foreach ($opts as $opt) {
        if ($opt['value'] === (string) $value) {
            $selectedSingle = $opt;
            break;
        }
    }
}
$initialQuery = ($isInput && ! $multiple) ? (string) ($selectedSingle['label'] ?? '') : '';

$sizes = [
    'sm' => 'h-8 py-1 text-sm',
    'default' => 'h-9 py-2 text-sm',
    'lg' => 'h-10 py-2 text-base',
];
$sizeCls = $sizes[$size] ?? $sizes['default'];
$minH = ['sm' => 'min-h-8', 'default' => 'min-h-9', 'lg' => 'min-h-10'][$size] ?? 'min-h-9';

?>
<div
    data-slot="combobox"
    x-data="hotListbox({
        trigger: <?= js($trigger) ?>,
        multiple: <?= js((bool) $multiple) ?>,
        model: $hot.model(<?= js($initialValue) ?>),
        query: <?= js($initialQuery) ?>,
        options: <?= js($opts) ?>,
    })"
    x-id="['hot-combobox-list', 'hot-combobox-opt']"
    <?php if (! $isInput): ?>x-init="$watch('query', () => ensureActive())"<?php endif; ?>
    <?= $attributes->twMerge('relative '.$width) ?>
>
    <?php if ($name): ?>
        <?php if ($multiple): ?>
            <template x-for="v in value" :key="v">
                <input type="hidden" name="<?= e($name) ?>[]" :value="v">
            </template>
        <?php else: ?>
            <input type="hidden" name="<?= e($name) ?>" :value="value">
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($isInput): ?>
        <?php if ($multiple): ?>
            <div
                x-ref="control"
                @click="!$refs.input.contains($event.target) && $refs.input.focus()"
                class="border-input dark:bg-input/30 <?= e($minH) ?> flex w-full flex-wrap items-center gap-1 rounded-md border bg-transparent px-2 py-1 shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px] <?= e($disabled ? 'pointer-events-none opacity-50' : '') ?>"
            >
                <?php if ($icon): ?>
                    <i data-lucide="<?= e($icon) ?>" class="text-muted-foreground pointer-events-none ms-1 size-4 shrink-0" aria-hidden="true"></i>
                <?php endif; ?>
                <template x-for="o in selected" :key="o.value">
                    <span class="bg-secondary text-secondary-foreground inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-xs font-medium">
                        <span x-text="o.label"></span>
                        <span role="button" tabindex="-1" :aria-label="'Remove ' + o.label" @click.stop.prevent="remove(o.value)"
                            class="hover:text-foreground/70 inline-flex cursor-pointer items-center rounded-sm outline-none">
                            <i data-lucide="x" class="size-3" aria-hidden="true"></i>
                        </span>
                    </span>
                </template>
                <input
                    x-ref="input"
                    x-model="query"
                    type="text"
                    role="combobox"
                    aria-autocomplete="list"
                    autocomplete="off"
                    :aria-expanded="open"
                    :aria-controls="$id('hot-combobox-list')"
                    :aria-activedescendant="activeValue != null ? $id('hot-combobox-opt', activeValue) : null"
                    @focus="openList()"
                    @click="openList()"
                    @input="onInput()"
                    @keydown.down.prevent="move(1)"
                    @keydown.up.prevent="move(-1)"
                    @keydown.enter.prevent="selectActive()"
                    @keydown.escape.prevent.stop="close()"
                    @keydown.backspace="backspace()"
                    placeholder="<?= e($placeholder) ?>"
                    <?php if ($disabled): ?>disabled<?php endif; ?>
                    class="placeholder:text-muted-foreground min-w-[6rem] flex-1 bg-transparent text-sm outline-none disabled:cursor-not-allowed"
                >
                <i
                    data-lucide="chevron-down"
                    class="text-muted-foreground pointer-events-none ms-auto size-4 shrink-0 self-center opacity-50 transition-transform"
                    ::class="open && 'rotate-180'"
                    aria-hidden="true"
                ></i>
            </div>
        <?php else: ?>
            <div class="relative">
                <?php if ($icon): ?>
                    <i data-lucide="<?= e($icon) ?>" class="text-muted-foreground pointer-events-none absolute top-1/2 start-3 size-4 -translate-y-1/2" aria-hidden="true"></i>
                <?php endif; ?>
                <input
                    x-ref="control"
                    x-model="query"
                    type="text"
                    role="combobox"
                    aria-autocomplete="list"
                    autocomplete="off"
                    :aria-expanded="open"
                    :aria-controls="$id('hot-combobox-list')"
                    :aria-activedescendant="activeValue != null ? $id('hot-combobox-opt', activeValue) : null"
                    @focus="openList()"
                    @click="openList()"
                    @input="onInput()"
                    @keydown.down.prevent="move(1)"
                    @keydown.up.prevent="move(-1)"
                    @keydown.enter.prevent="selectActive()"
                    @keydown.escape.prevent.stop="close()"
                    placeholder="<?= e($placeholder) ?>"
                    <?php if ($disabled): ?>disabled<?php endif; ?>
                    class="border-input dark:bg-input/30 placeholder:text-muted-foreground flex w-full rounded-md border bg-transparent pe-9 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 <?= e($icon ? 'ps-9' : 'ps-3') ?> <?= e($sizeCls) ?>"
                >
                <i
                    data-lucide="chevron-down"
                    class="text-muted-foreground pointer-events-none absolute top-1/2 end-3 size-4 -translate-y-1/2 opacity-50 transition-transform"
                    ::class="open && 'rotate-180'"
                    aria-hidden="true"
                ></i>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <button
            type="button"
            x-ref="trigger"
            @click="toggle()"
            @keydown.down.prevent.stop="openList()"
            @keydown.up.prevent.stop="openList()"
            @keydown.enter.prevent.stop="openList()"
            @keydown.space.prevent.stop="openList()"
            role="combobox"
            aria-haspopup="listbox"
            aria-label="<?= e($placeholder) ?>"
            :aria-expanded="open"
            :aria-controls="$id('hot-combobox-list')"
            <?php if ($disabled): ?>disabled<?php endif; ?>
            class="<?= e($width) ?> border-input dark:bg-input/30 dark:hover:bg-input/50 inline-flex min-h-9 items-center justify-between gap-2 rounded-md border bg-transparent px-3 py-1.5 text-sm font-normal whitespace-nowrap shadow-xs transition-[color,box-shadow] outline-none hover:bg-transparent focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
        >
            <span x-show="!multiple" x-text="label || <?= js($placeholder) ?>" :class="{ 'text-muted-foreground': !label }"></span>

            <span x-show="multiple && !selected.length" class="text-muted-foreground"><?= e($placeholder) ?></span>
            <span x-show="multiple && selected.length" class="flex flex-1 flex-wrap items-center gap-1">
                <template x-for="o in selected" :key="o.value">
                    <span class="bg-secondary text-secondary-foreground inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-xs font-medium">
                        <span x-text="o.label"></span>
                        <span role="button" tabindex="-1" :aria-label="'Remove ' + o.label" @click.stop.prevent="remove(o.value)"
                            class="hover:text-foreground/70 inline-flex cursor-pointer items-center rounded-sm outline-none">
                            <i data-lucide="x" class="size-3" aria-hidden="true"></i>
                        </span>
                    </span>
                </template>
            </span>

            <i data-lucide="chevrons-up-down" class="size-4 shrink-0 self-center opacity-50" aria-hidden="true"></i>
        </button>
    <?php endif; ?>

    <template x-teleport="body">
    <div
        x-hot-dialog-layer
        x-show="open"
        x-cloak
        data-slot="combobox-content"
<?php if ($isInput): ?>
            x-hot-anchor.bottom-start.offset.4.match-width="$refs.control"
            @click.outside="open && !$refs.control.contains($event.target) && close()"
        <?php else: ?>
            x-hot-anchor.bottom-start.offset.4.match-width="$refs.trigger"
            @click.outside="close(false)"
            @keydown.escape.prevent.stop="close()"
        <?php endif; ?>
class="bg-popover text-popover-foreground z-50 flex w-fit flex-col origin-top overflow-hidden rounded-md border <?= e($isInput ? 'p-1' : 'p-0') ?> shadow-md"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
    >
        <div class="flex h-full w-full min-h-0 flex-col overflow-hidden rounded-md">
            <?php if (! $isInput && $searchable): ?>
            <div class="flex h-9 shrink-0 items-center gap-2 border-b px-3">
                <i data-lucide="search" class="size-4 shrink-0 opacity-50" aria-hidden="true"></i>
                <input
                    x-ref="search"
                    x-model="query"
                    type="text"
                    role="combobox"
                    aria-expanded="true"
                    aria-autocomplete="list"
                    autocomplete="off"
                    aria-label="<?= e($searchPlaceholder) ?>"
                    :aria-controls="$id('hot-combobox-list')"
                    :aria-activedescendant="activeValue != null ? $id('hot-combobox-opt', activeValue) : null"
                    @keydown.down.prevent="move(1)"
                    @keydown.up.prevent="move(-1)"
                    @keydown.home.prevent="edge('first')"
                    @keydown.end.prevent="edge('last')"
                    @keydown.enter.prevent="selectActive()"
                    placeholder="<?= e($searchPlaceholder) ?>"
                    class="placeholder:text-muted-foreground flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-hidden"
                >
            </div>
            <?php endif; ?>
            <div
                role="listbox"
                x-ref="list"
                tabindex="-1"
                :aria-multiselectable="multiple"
                :id="$id('hot-combobox-list')"
                <?php if (! $isInput && ! $searchable): ?>
                    @keydown.down.prevent="move(1)"
                    @keydown.up.prevent="move(-1)"
                    @keydown.home.prevent="edge('first')"
                    @keydown.end.prevent="edge('last')"
                    @keydown.enter.prevent="selectActive()"
                <?php endif; ?>
                class="max-h-[300px] min-h-0 scroll-py-1 overflow-x-hidden overflow-y-auto <?= e($isInput ? '' : 'p-1') ?> outline-hidden"
            >
                <div x-show="visibleCount === 0" class="py-6 text-center text-sm"><?= e($empty) ?></div>
                <template x-for="option in options" :key="option.value">
                    <div
                        role="option"
                        :id="$id('hot-combobox-opt', option.value)"
                        x-show="visible.some(o => o.value === option.value)"
                        @click="select(option.value)"
                        @mouseenter="activeValue = option.value"
                        :aria-selected="isSelected(option.value)"
                        :data-active="activeValue === option.value"
                        class="data-[active=true]:bg-accent data-[active=true]:text-accent-foreground relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-hidden select-none"
                    >
                        <?php if ($indicator === 'checkbox'): ?>
                            <span class="border-input flex size-4 shrink-0 items-center justify-center rounded-[4px] border transition-colors" :class="isSelected(option.value) && 'bg-primary border-primary text-primary-foreground'">
                                <i data-lucide="check" class="size-3" x-bind:class="isSelected(option.value) ? 'opacity-100' : 'opacity-0'" aria-hidden="true"></i>
                            </span>
                        <?php elseif ($indicator === 'radio'): ?>
                            <span class="border-input flex size-4 shrink-0 items-center justify-center rounded-full border transition-colors" :class="isSelected(option.value) && 'border-primary'">
                                <span class="bg-primary size-2 rounded-full transition-opacity" :class="isSelected(option.value) ? 'opacity-100' : 'opacity-0'"></span>
                            </span>
                        <?php else: ?>
                            <i data-lucide="check" class="size-4" x-bind:class="isSelected(option.value) ? 'opacity-100' : 'opacity-0'" aria-hidden="true"></i>
                        <?php endif; ?>
                        <span x-text="option.label"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
    </template>
</div>
