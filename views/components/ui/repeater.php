<?php

declare(strict_types=1);

extract(props($__ctx, [
    'name' => 'items',
    'fields' => [],
    'value' => [],
    'min' => 1,
    'max' => null,
    'addLabel' => 'Add row',
]));

$headline = static function (string $value): string {
    $words = preg_split('/[\s_-]+|(?<=[a-z0-9])(?=[A-Z])/', $value) ?: [];
    $words = array_values(array_filter(array_map('trim', $words), static fn (string $w): bool => $w !== ''));

    return implode(' ', array_map(
        static fn (string $w): string => mb_strtoupper(mb_substr($w, 0, 1)).mb_substr($w, 1),
        $words,
    ));
};

$cols = [];
foreach ($fields as $f) {
    $cols[] = [
        'key' => $f['key'] ?? 'value',
        'label' => $f['label'] ?? $headline((string) ($f['key'] ?? 'Value')),
        'placeholder' => $f['placeholder'] ?? '',
        'type' => $f['type'] ?? 'text',
    ];
}

if ($cols === []) {
    $cols = [['key' => 'value', 'label' => $headline((string) $name), 'placeholder' => '', 'type' => 'text']];
}

$keys = array_column($cols, 'key');

$blank = array_fill_keys($keys, '');

$minRows = max(1, (int) $min);
$seed = [];
foreach (array_values($value) as $row) {
    $seed[] = array_merge($blank, is_array($row) ? array_intersect_key($row, $blank) : ['value' => $row]);
}

while (count($seed) < $minRows) {
    $seed[] = $blank;
}

$multi = count($cols) > 1;

$fieldClasses = "file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none md:text-sm disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]";

$nameJs = js($name);
?>
<div
    data-slot="repeater"
    x-data="{
        rows: <?= js($seed) ?>,
        keys: <?= js($keys) ?>,
        min: <?= js($minRows) ?>,
        max: <?= js($max !== null ? (int) $max : null) ?>,
        blank() {
            const r = {};
            this.keys.forEach((k) => (r[k] = ''));
            return r;
        },
        get canAdd() {
            return this.max === null || this.rows.length < this.max;
        },
        get canRemove() {
            return this.rows.length > this.min;
        },
        add() {
            if (!this.canAdd) return;
            this.rows.push(this.blank());
            
            this.$nextTick(() => {
                const fields = this.$refs.rows?.querySelectorAll('[data-repeater-row]');
                fields?.[fields.length - 1]?.querySelector('input, textarea, select')?.focus();
            });
        },
        remove(index) {
            if (!this.canRemove) return;
            this.rows.splice(index, 1);
        },
        rowLabel(index) {
            return <?= $nameJs ?> + ' row ' + (index + 1);
        },
        fieldId(index, key) {
            
            return (<?= $nameJs ?> + '-' + index + '-' + key).replace(/[^a-zA-Z0-9_-]/g, '-');
        },
    }"
    <?= $attributes->twMerge('flex w-full flex-col gap-3') ?>
>
    <div x-ref="rows" class="flex flex-col gap-2">
        <template x-for="(row, index) in rows" :key="index">
            <div
                data-repeater-row
                class="<?= classes([
                    'flex gap-2',
                    'flex-col sm:flex-row sm:items-end' => $multi,
                    'items-center' => ! $multi,
                ]) ?>"
            >
            <?php foreach ($cols as $col): ?>
                <div class="flex flex-1 flex-col gap-1.5">
                    <?php if ($multi): ?>
                        <label
                            data-slot="repeater-label"
                            x-show="index === 0"
                            class="text-sm leading-none font-medium select-none"
                            :for="fieldId(index, '<?= e($col['key']) ?>')"
                        ><?= e($col['label']) ?></label>
                    <?php endif; ?>

                    <?php if ($col['type'] === 'textarea'): ?>
                        <textarea
                            data-slot="repeater-input"
                            x-bind:name="<?= $nameJs ?> + '[' + index + '][<?= e($col['key']) ?>]'"
                            x-model="row['<?= e($col['key']) ?>']"
                            rows="2"
                            placeholder="<?= e($col['placeholder']) ?>"
                            :aria-label="rowLabel(index) + ' <?= e($col['label']) ?>'"
                            :id="fieldId(index, '<?= e($col['key']) ?>')"
                            class="<?= e($fieldClasses) ?> field-sizing-content min-h-16 py-2"
                        ></textarea>
                    <?php else: ?>
                        <input
                            type="<?= e($col['type']) ?>"
                            data-slot="repeater-input"
                            x-bind:name="<?= $nameJs ?> + '[' + index + '][<?= e($col['key']) ?>]'"
                            x-model="row['<?= e($col['key']) ?>']"
                            placeholder="<?= e($col['placeholder']) ?>"
                            :aria-label="rowLabel(index) + ' <?= e($col['label']) ?>'"
                            :id="fieldId(index, '<?= e($col['key']) ?>')"
                            class="<?= e($fieldClasses) ?>"
                        />
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

                <button
                    type="button"
                    data-slot="repeater-remove"
                    @click="remove(index)"
                    :disabled="!canRemove"
                    :aria-label="'Remove ' + rowLabel(index)"
                    class="<?= classes([
                        'inline-flex size-9 shrink-0 items-center justify-center rounded-md border bg-background text-muted-foreground shadow-xs outline-none transition-colors not-disabled:cursor-pointer hover:bg-accent hover:text-accent-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50 dark:bg-input/30 dark:border-input dark:hover:bg-input/50',
                        'sm:mb-0' => $multi,
                    ]) ?>"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>

    <div class="flex items-center gap-3">
        <button
            type="button"
            data-slot="repeater-add"
            @click="add()"
            :disabled="!canAdd"
            class="inline-flex h-8 items-center justify-center gap-1.5 rounded-md border bg-background px-3 text-sm font-medium shadow-xs outline-none transition-colors not-disabled:cursor-pointer hover:bg-accent hover:text-accent-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:pointer-events-none disabled:opacity-50 dark:bg-input/30 dark:border-input dark:hover:bg-input/50"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14" />
                <path d="M12 5v14" />
            </svg>
            <?= e($addLabel) ?>
        </button>

        <span x-show="max !== null" class="text-muted-foreground text-xs" aria-hidden="true">
            <span x-text="rows.length"></span> / <span x-text="max"></span>
        </span>
    </div>
</div>
