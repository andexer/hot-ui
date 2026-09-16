<?php

declare(strict_types=1);

extract(props($__ctx, [
    'mode' => 'single',
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'hourCycle' => 'auto',
    'timeVariant' => 'input',
    'seconds' => false,
    'minuteStep' => 1,
    'captionLayout' => 'dropdown',
    'min' => null,
    'max' => null,
    'minNights' => null,
    'maxNights' => null,
    'outOfRange' => 'disable',
    'weekStart' => 0,
    'numberOfMonths' => null,
    'defaultMonth' => null,
    'showOutsideDays' => true,
    'width' => null,
]));

$parseDT = static function ($v): array {
    if (! $v) {
        return [null, null];
    }
    $v = str_replace(' ', 'T', trim((string) $v));
    $p = explode('T', $v, 2);

    return [$p[0] ?? null, $p[1] ?? null];
};

$isRange = $mode === 'range';

$months = $numberOfMonths !== null ? max(1, (int) $numberOfMonths) : ($isRange ? 2 : 1);

[$initDate, $initTime] = $parseDT($isRange ? null : $value);

$fromDate = $fromTime = $toDate = $toTime = null;
if ($isRange && is_array($value)) {
    [$fromDate, $fromTime] = $parseDT($value['from'] ?? null);
    [$toDate, $toTime] = $parseDT($value['to'] ?? null);
}
$calRange = array_filter(['from' => $fromDate, 'to' => $toDate], static fn ($x) => $x !== null) ?: null;

[$minDate, $minTime] = $parseDT($min);
[$maxDate, $maxTime] = $parseDT($max);

$placeholder ??= $isRange ? 'Pick a date range' : 'Pick a date & time';
$width ??= $isRange ? 'w-[320px]' : 'w-[280px]';

$triggerCls = 'border-input dark:bg-input/30 dark:hover:bg-input/50 inline-flex h-9 items-center justify-start gap-2 rounded-md border bg-transparent px-3 py-2 text-start text-sm font-normal whitespace-nowrap shadow-xs transition-[color,box-shadow] outline-none hover:bg-transparent focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:border-destructive aria-invalid:ring-destructive/20';

$combine = static fn ($d, $t): string => $d ? $d.'T'.($t ?: '00:00') : '';
$modelInit = $isRange
    ? ['from' => $combine($fromDate, $fromTime), 'to' => $combine($toDate, $toTime)]
    : $combine($initDate, $initTime);
?>
<div
    data-slot="datetime-picker"
x-data="hotDatetimePicker({
        mode: <?= js($mode) ?>,
        cycle: <?= js($hourCycle) ?>,
        seconds: <?= js((bool) $seconds) ?>,
        minDate: <?= js($minDate) ?>, minTime: <?= js($minTime) ?>,
        maxDate: <?= js($maxDate) ?>, maxTime: <?= js($maxTime) ?>,
        minNights: <?= js($minNights !== null ? (int) $minNights : null) ?>,
        maxNights: <?= js($maxNights !== null ? (int) $maxNights : null) ?>,
        model: $hot.model(<?= js($modelInit) ?>),
    })"
    x-id="['hot-datetimepicker']"
    <?= $attributes->twMerge('relative '.$width) ?>
>
    <?php if ($name): ?>
        <?php if ($isRange): ?>
            <input type="hidden" name="<?= e($name) ?>[from]" :value="combined(from, timeFrom)" :aria-invalid="invalid ? 'true' : null">
            <input type="hidden" name="<?= e($name) ?>[to]" :value="combined(to, timeTo)" :aria-invalid="invalid ? 'true' : null">
        <?php else: ?>
            <input type="hidden" name="<?= e($name) ?>" :value="combined(date, time)" :aria-invalid="invalid ? 'true' : null">
        <?php endif; ?>
    <?php endif; ?>

    <button
        type="button"
        x-ref="trigger"
        @click="open = !open"
        aria-haspopup="dialog"
        :aria-expanded="open"
        :aria-controls="$id('hot-datetimepicker')"
        :aria-invalid="invalid ? 'true' : null"
        :class="{ 'text-muted-foreground': !label }"
        class="<?= $width ?> <?= $triggerCls ?>"
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
            ? setRange(combined($event.detail.value.from, timeFrom), combined($event.detail.value.to, timeTo))
            : (date = $event.detail.value)"
        @time-change="onTime($event.detail)"
        x-trap="open"
        :id="$id('hot-datetimepicker')"
        role="dialog"
        aria-label="<?= e($isRange ? 'Choose a date and time range' : 'Choose date and time') ?>"
        tabindex="-1"
        class="bg-popover text-popover-foreground z-50 flex w-auto origin-top flex-col overflow-y-auto overscroll-contain rounded-md border shadow-md"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
    >
        <div x-ref="cal" class="contents">
        <?= $this->uiCalendar([
            'mode' => $mode,
            'value' => $isRange ? $calRange : $initDate,
            'captionLayout' => $captionLayout,
            'weekStart' => $weekStart,
            'numberOfMonths' => $months,
            'defaultMonth' => $defaultMonth,
            'showOutsideDays' => $showOutsideDays,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
            'outOfRange' => $outOfRange,
            'class' => 'rounded-none border-0',
        ]) ?>
        </div>

        <div class="flex flex-col gap-3 border-t p-3">
            <?php if ($isRange): ?>
                <div class="flex items-center justify-between gap-3" x-ref="tFrom">
                    <span class="text-sm font-medium">Start</span>
                    <?= $this->uiTimeField(['part' => 'from', 'value' => $fromTime, 'variant' => $timeVariant, 'hourCycle' => $hourCycle, 'seconds' => $seconds, 'minuteStep' => $minuteStep]) ?>
                </div>
                <div class="flex items-center justify-between gap-3" x-ref="tTo">
                    <span class="text-sm font-medium">End</span>
                    <?= $this->uiTimeField(['part' => 'to', 'value' => $toTime, 'variant' => $timeVariant, 'hourCycle' => $hourCycle, 'seconds' => $seconds, 'minuteStep' => $minuteStep]) ?>
                </div>
            <?php else: ?>
                <div class="flex items-center justify-between gap-3" x-ref="tOne">
                    <span class="text-sm font-medium">Time</span>
                    <?= $this->uiTimeField(['value' => $initTime, 'variant' => $timeVariant, 'hourCycle' => $hourCycle, 'seconds' => $seconds, 'minuteStep' => $minuteStep]) ?>
                </div>
            <?php endif; ?>

            <template x-if="errors.length">
                <ul class="flex flex-col gap-0.5" role="alert">
                    <template x-for="msg in errors" :key="msg">
                        <li class="text-destructive flex items-start gap-1 text-xs">
                            <i data-lucide="circle-alert" class="mt-0.5 size-3 shrink-0" aria-hidden="true"></i>
                            <span x-text="msg"></span>
                        </li>
                    </template>
                </ul>
            </template>
        </div>

        <div class="flex justify-end border-t p-3">
            <?= $this->uiButton(['type' => 'button', 'size' => 'sm', '::disabled' => 'invalid', '@click' => 'open = false'], 'Done') ?>
        </div>
    </div>
    </template>
</div>
