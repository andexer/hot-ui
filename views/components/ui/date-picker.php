<?php

declare(strict_types=1);

extract(props($__ctx, [
    'mode' => 'single',
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'numberOfMonths' => null,
    'captionLayout' => 'label',
    'weekStart' => 0,
    'defaultMonth' => null,
    'min' => null,
    'max' => null,
    'minNights' => null,
    'maxNights' => null,
    'outOfRange' => 'disable',
    'showOutsideDays' => true,
    'width' => null,
    'presets' => null,

]));

$isRange = $mode === 'range';
$months = $numberOfMonths !== null ? max(1, (int) $numberOfMonths) : ($isRange ? 2 : 1);

$fromDate = $toDate = null;
if ($isRange && is_array($value)) {
    $fromDate = $value['from'] ?? null;
    $toDate = $value['to'] ?? null;
}
$calValue = $isRange
    ? (array_filter(['from' => $fromDate, 'to' => $toDate], static fn ($x) => $x !== null) ?: null)
    : $value;

$placeholder ??= $isRange ? 'Pick a date range' : 'Pick a date';
$width ??= $isRange ? 'w-[300px]' : 'w-[240px]';

$weekStartNum = is_numeric($weekStart)
    ? (((int) $weekStart % 7) + 7) % 7
    : (['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6][strtolower(trim((string) $weekStart))] ?? 0);

$headline = static function (string $v): string {
    $spaced = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', str_replace(['-', '_'], ' ', trim($v))) ?? $v;

    return ucwords(strtolower($spaced));
};

$presetDefaults = $isRange
    ? ['today', 'yesterday', 'thisWeek', 'last7Days', 'thisMonth', 'yearToDate', 'allTime']
    : ['today', 'yesterday', 'tomorrow'];
$presetLabels = [
    'today' => 'Today', 'yesterday' => 'Yesterday', 'tomorrow' => 'Tomorrow',
    'thisWeek' => 'This week', 'lastWeek' => 'Last week',
    'last7Days' => 'Last 7 days', 'last14Days' => 'Last 14 days', 'last30Days' => 'Last 30 days',
    'thisMonth' => 'This month', 'lastMonth' => 'Last month',
    'thisYear' => 'This year', 'yearToDate' => 'Year to date', 'allTime' => 'All time',
];
$rawPresets = $presets === true ? $presetDefaults : (is_array($presets) ? $presets : []);
$presetList = [];
foreach ($rawPresets as $k => $v) {
    if (is_string($v)) {
        $presetList[] = ['label' => $presetLabels[$v] ?? $headline($v), 'key' => $v];
    } elseif (is_array($v)) {
        $v['label'] = is_string($k) ? $k : ($v['label'] ?? '');
        $presetList[] = $v;
    }
}
$hasPresets = count($presetList) > 0;

?>
<div
    data-slot="date-picker"
x-data="hotDatePicker({
        mode: <?= js($mode) ?>,
        model: $hot.model(<?= js($isRange ? ['from' => $fromDate, 'to' => $toDate] : $value) ?>),
        minNights: <?= js($minNights !== null ? (int) $minNights : null) ?>,
        maxNights: <?= js($maxNights !== null ? (int) $maxNights : null) ?>,
        minDate: <?= js($min) ?>, maxDate: <?= js($max) ?>,
        weekStart: <?= js($weekStartNum) ?>,
    })"
    x-id="['hot-datepicker']"
    <?= $attributes->twMerge('relative '.$width) ?>
>
    <?php if ($name): ?>
        <?php if ($isRange): ?>
            <input type="hidden" name="<?= e($name) ?>[from]" :value="from" :aria-invalid="invalid ? 'true' : null">
            <input type="hidden" name="<?= e($name) ?>[to]" :value="to" :aria-invalid="invalid ? 'true' : null">
        <?php else: ?>
            <input type="hidden" name="<?= e($name) ?>" :value="value">
        <?php endif; ?>
    <?php endif; ?>

    <button
        type="button"
        x-ref="trigger"
        @click="open = !open"
        aria-haspopup="dialog"
        :aria-expanded="open"
        :aria-controls="$id('hot-datepicker')"
        :class="{ 'text-muted-foreground': !label }"
        :aria-invalid="invalid ? 'true' : null"
        class="<?= $width ?> border-input dark:bg-input/30 dark:hover:bg-input/50 inline-flex h-9 items-center justify-start gap-2 rounded-md border bg-transparent px-3 py-2 text-start text-sm font-normal whitespace-nowrap shadow-xs transition-[color,box-shadow] outline-none hover:bg-transparent focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:border-destructive aria-invalid:ring-destructive/20"
    >
        <i data-lucide="calendar" class="size-4 opacity-50" aria-hidden="true"></i>
        <span class="truncate" x-text="label || <?= js($placeholder) ?>"></span>
    </button>

    <template x-teleport="body">
    <div
        x-hot-dialog-layer
        x-show="open"
        x-cloak
        x-hot-anchor.bottom-start.offset.4="$refs.trigger"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        @calendar:updated="mode === 'range'
            ? (setRange($event.detail.value.from, $event.detail.value.to), ($event.detail.source === 'select' && from && to && !invalid) && (open = false))
            : (value = $event.detail.value, $event.detail.source === 'select' && (open = false))"
        x-trap="open"
        :id="$id('hot-datepicker')"
        role="dialog"
        aria-label="<?= e($isRange ? 'Choose a date range' : 'Choose date') ?>"
        tabindex="-1"
        class="bg-popover text-popover-foreground z-50 flex w-auto origin-top flex-col overflow-y-auto overscroll-contain rounded-md border p-0 shadow-md"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
    >
        <div x-ref="cal" class="flex flex-col sm:flex-row">
            <?php if ($hasPresets): ?>
                <div class="flex shrink-0 flex-row gap-1 overflow-x-auto border-b p-2 sm:max-w-[9rem] sm:flex-col sm:gap-0.5 sm:overflow-visible sm:border-e sm:border-b-0"
                    role="group" aria-label="Presets">
                    <?php foreach ($presetList as $p): ?>
                        <button
                            type="button"
                            @click="applyPreset(<?= js($p) ?>)"
                            :data-active="isActivePreset(<?= js($p) ?>)"
                            class="hover:bg-accent hover:text-accent-foreground data-[active=true]:bg-accent data-[active=true]:text-accent-foreground focus-visible:ring-ring/50 inline-flex shrink-0 cursor-pointer items-center rounded-md px-2.5 py-1.5 text-start text-sm whitespace-nowrap transition-colors outline-none focus-visible:ring-[2px] sm:w-full"
                        ><?= e($p['label']) ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= $this->uiCalendar([
                'mode' => $mode,
                'value' => $calValue,
                'captionLayout' => $captionLayout,
                'weekStart' => $weekStart,
                'numberOfMonths' => $months,
                'defaultMonth' => $defaultMonth,
                'showOutsideDays' => $showOutsideDays,
                'minDate' => $min,
                'maxDate' => $max,
                'outOfRange' => $outOfRange,
                'class' => 'border-0',
            ]) ?>
        </div>

        <template x-if="errors.length">
            <ul class="border-t px-3 py-2" role="alert">
                <template x-for="msg in errors" :key="msg">
                    <li class="text-destructive flex items-start gap-1 text-xs">
                        <i data-lucide="circle-alert" class="mt-0.5 size-3 shrink-0" aria-hidden="true"></i>
                        <span x-text="msg"></span>
                    </li>
                </template>
            </ul>
        </template>
    </div>
    </template>
</div>
