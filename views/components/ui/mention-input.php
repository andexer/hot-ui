<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => null,
    'mentions' => [],
    'trigger' => '@',
    'placeholder' => 'Type @ to mention…',
    'rows' => 3,
    'disabled' => false,
    'id' => null,
]));

$items = [];
foreach ((array) $mentions as $m) {
    if (is_array($m)) {
        $items[] = [
            'value' => (string) ($m['value'] ?? $m['label'] ?? ''),
            'label' => (string) ($m['label'] ?? $m['value'] ?? ''),
            'avatar' => isset($m['avatar']) ? (string) $m['avatar'] : null,
            'sub' => isset($m['sub']) ? (string) $m['sub'] : null,
        ];
        continue;
    }
    $items[] = ['value' => (string) $m, 'label' => (string) $m, 'avatar' => null, 'sub' => null];
}

$fieldId = $id ?? 'hot-mention-'.bin2hex(random_bytes(3));

$initial = (string) ($attributes->get('value') ?? $slot ?? '');

$xModel = $attributes->get('x-model');
$hasModel = is_string($xModel) && $xModel !== '';
$attributes = $attributes->except('value', 'x-model');
?>
<div
    data-slot="mention-input"
    x-data="hotMentionInput({
        trigger: <?= js((string) $trigger) ?>,
        mentions: <?= js($items) ?>,
    })"
    x-id="['hot-mention-list', 'hot-mention-opt']"
    <?= $attributes->twMerge('relative w-full') ?>
>
    <span class="contents" data-slot="mention-input-field">
    <?= $this->uiTextarea([
        'x-ref' => 'field',
        'id' => $fieldId,
        'name' => $name,
        'rows' => (int) $rows,
        'placeholder' => $placeholder,
        'disabled' => $disabled,

        'x-model' => $hasModel ? $xModel : null,
        'role' => 'combobox',
        'aria-label' => $name !== null && $name !== '' ? $name : 'Mention input',
        'aria-autocomplete' => 'list',
        'x-bind:aria-expanded' => 'open',
        'x-bind:aria-controls' => "open ? \$id('hot-mention-list') : null",
        'x-bind:aria-activedescendant' => 'open ? activeId : null',
        '@input' => 'scan()',
        '@click' => 'scan()',
        '@keyup.arrow-left' => 'scan()',
        '@keyup.arrow-right' => 'scan()',
        '@keydown.arrow-down' => 'if (open) { $event.preventDefault(); move(1); }',
        '@keydown.arrow-up' => 'if (open) { $event.preventDefault(); move(-1); }',
        '@keydown.enter' => 'if (open && insertActive()) $event.preventDefault();',
        '@keydown.tab' => 'if (open && insertActive()) $event.preventDefault();',
        '@keydown.escape' => 'if (open) { $event.preventDefault(); $event.stopPropagation(); close(); }',
        '@blur' => 'close()',
    ], e($initial)) ?>
    </span>

    <div
        x-show="open"
        x-cloak
        @mousedown.prevent
        :id="$id('hot-mention-list')"
        role="listbox"
        aria-label="Mention suggestions"
        class="bg-popover text-popover-foreground absolute start-0 top-full z-50 mt-1 max-h-60 w-64 max-w-full overflow-y-auto rounded-md border p-1 shadow-md"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
    >
        <template x-for="(m, i) in matches" :key="m.value">
            <div
                role="option"
                :id="$id('hot-mention-opt', m.value)"
                :aria-selected="i === activeIndex"
                :data-active="i === activeIndex"
                @click="activeIndex = i; insertActive()"
                @mouseenter="activeIndex = i"
                class="data-[active=true]:bg-accent data-[active=true]:text-accent-foreground relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-hidden select-none"
            >
                <template x-if="m.avatar">
                    <img :src="m.avatar" :alt="''" aria-hidden="true" class="size-6 shrink-0 rounded-full object-cover" />
                </template>
                <template x-if="!m.avatar">
                    <span class="bg-muted text-muted-foreground flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-medium" aria-hidden="true" x-text="m.label.slice(0, 1).toUpperCase()"></span>
                </template>
                <span class="flex min-w-0 flex-col">
                    <span class="truncate font-medium" x-text="m.label"></span>
                    <span x-show="m.sub" class="text-muted-foreground truncate text-xs" x-text="m.sub"></span>
                </span>
            </div>
        </template>
    </div>
</div>
