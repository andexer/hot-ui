<?php

declare(strict_types=1);

extract(props($__ctx, [
    'mode' => 'single',
    'value' => null,
    'name' => null,
    'locale' => null,
    'numberOfMonths' => 1,
    'defaultMonth' => null,
    'weekStart' => 0,
    'captionLayout' => 'label',
    'showWeekNumber' => false,
    'disabled' => null,
    'min' => null,
    'max' => null,
    'minDays' => null,
    'maxDays' => null,
    'required' => false,
    'startMonth' => null,
    'endMonth' => null,
    'disableNavigation' => false,
    'modifiers' => [],
    'modifiersClass' => [],
    'buttonVariant' => 'ghost',
    'showOutsideDays' => true,
    'minDate' => null,
    'maxDate' => null,
    'outOfRange' => 'disable',
    'prevMonthLabel' => null,
    'nextMonthLabel' => null,
    'todayLabel' => null,
    'selectedLabel' => null,
    'calendarId' => null,
]));

$prevMonthLabel ??= 'Go to the previous month';
$nextMonthLabel ??= 'Go to the next month';
$todayLabel ??= 'Today, :date';
$selectedLabel ??= 'selected';

$calendarId ??= $attributes->get('id');

$weekStartNum = is_numeric($weekStart)
    ? (((int) $weekStart % 7) + 7) % 7
    : (['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6][strtolower(trim((string) $weekStart))] ?? 0);

$cfg = array_filter([
    'mode' => $mode,
    'value' => $value,
    'calendarId' => $calendarId,
    'todayLabel' => $todayLabel,
    'selectedLabel' => $selectedLabel,
    'locale' => $locale,
    'numberOfMonths' => (int) $numberOfMonths,
    'weekStart' => $weekStartNum,
    'captionLayout' => $captionLayout,
    'showWeekNumber' => (bool) $showWeekNumber,
    'disableNavigation' => (bool) $disableNavigation,
    'min' => $min,
    'max' => $max,
    'minDays' => $minDays,
    'maxDays' => $maxDays,
    'minDate' => $minDate,
    'maxDate' => $maxDate,
    'outOfRange' => $outOfRange,
    'disabled' => $disabled,
    'defaultMonth' => $defaultMonth,
    'startMonth' => $startMonth,
    'endMonth' => $endMonth,
    'required' => (bool) $required,
    'modifiers' => $modifiers,
    'modifiersClass' => $modifiersClass,
], fn ($v) => $v !== null && $v !== false && $v !== []);

$navBtn = 'inline-flex size-(--cell-size) items-center justify-center rounded-md p-0 text-sm transition-colors hover:bg-accent hover:text-accent-foreground aria-disabled:opacity-40 aria-disabled:pointer-events-none select-none';
if ($buttonVariant === 'outline') {
    $navBtn = 'border-input hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:border-input '.$navBtn.' border bg-transparent shadow-xs';
}

$dayBtn = implode(' ', [
    'relative inline-flex size-(--cell-size) select-none items-center justify-center rounded-md p-0 text-sm font-normal transition-colors',
    'hover:bg-accent hover:text-accent-foreground',
    'data-[today]:bg-accent data-[today]:text-accent-foreground',
    'data-[outside]:text-muted-foreground',
    'data-[disabled]:text-muted-foreground data-[disabled]:opacity-40 data-[disabled]:line-through data-[disabled]:pointer-events-none',

    'data-[out-of-range]:text-destructive data-[out-of-range]:hover:bg-destructive/10 data-[out-of-range]:hover:text-destructive',
    'data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[selected]:hover:bg-primary data-[selected]:hover:text-primary-foreground',
    'data-[range-start]:bg-primary data-[range-start]:text-primary-foreground data-[range-start]:rounded-e-none',
    'data-[range-end]:bg-primary data-[range-end]:text-primary-foreground data-[range-end]:rounded-s-none',
    'data-[range-middle]:bg-accent data-[range-middle]:text-accent-foreground data-[range-middle]:rounded-none',
]);
?>
<div
    data-slot="calendar"
    x-data="hotCalendar(<?= js($cfg) ?>)"
x-modelable="value"
style="--cell-size: calc(var(--spacing) * 8);"
    <?php if ($calendarId): ?>data-calendar-id="<?= e($calendarId) ?>"
    <?php endif; ?>
    <?php if (! $showOutsideDays): ?>data-hide-outside-days<?php endif; ?>
    <?= $attributes->twMerge('group/calendar bg-background w-fit rounded-md border p-3') ?>
>
    <?php if ($name): ?>
        <?php if ($mode === 'single'): ?>
            <input type="hidden" name="<?= e($name) ?>" :value="fmt(single)">
        <?php elseif ($mode === 'multiple'): ?>
            <input type="hidden" name="<?= e($name) ?>" :value="multipleValue">
        <?php else: ?>
            <input type="hidden" name="<?= e($name) ?>[from]" :value="fmt(rangeFrom)">
            <input type="hidden" name="<?= e($name) ?>[to]" :value="fmt(rangeTo)">
        <?php endif; ?>
    <?php endif; ?>

    <div class="relative flex flex-col gap-4 md:flex-row">
        <button type="button" @click="prev()" :aria-disabled="!canPrev" aria-label="<?= e($prevMonthLabel) ?>" class="<?= e($navBtn) ?> absolute start-0 top-0 z-10">
            <i data-lucide="chevron-left" class="size-4 rtl:rotate-180" aria-hidden="true"></i>
            <span class="sr-only"><?= e($prevMonthLabel) ?></span>
        </button>
        <button type="button" @click="next()" :aria-disabled="!canNext" aria-label="<?= e($nextMonthLabel) ?>" class="<?= e($navBtn) ?> absolute end-0 top-0 z-10">
            <i data-lucide="chevron-right" class="size-4 rtl:rotate-180" aria-hidden="true"></i>
            <span class="sr-only"><?= e($nextMonthLabel) ?></span>
        </button>

        <span role="status" aria-live="polite" class="sr-only" x-text="months.map(m => monthLabel(m)).join(', ')"></span>

        <template x-for="(m, mi) in months" :key="fmt(m)">
            <div class="flex w-full flex-col gap-4">
                <div class="flex h-(--cell-size) items-center justify-center">
                    <template x-if="captionLayout === 'dropdown'">
                        <div class="flex items-center gap-1.5 text-sm font-medium">
                            <div class="relative rounded-md border border-input bg-background px-2 py-1 shadow-xs hover:bg-accent">
                                <select aria-label="Month" class="absolute inset-0 cursor-pointer opacity-0" @change="setMonth($event.target.value)">
                                    <template x-for="(mn, idx) in monthNames" :key="idx">
                                        <option :value="idx" :selected="idx === m.getMonth()" x-text="mn"></option>
                                    </template>
                                </select>
                                <span x-text="m.toLocaleString(locale, { month: 'long' })"></span>
                            </div>
                            <div class="relative rounded-md border border-input bg-background px-2 py-1 shadow-xs hover:bg-accent">
                                <select aria-label="Year" class="absolute inset-0 cursor-pointer opacity-0" @change="setYear($event.target.value)">
                                    <template x-for="y in years" :key="y">
                                        <option :value="y" :selected="y === m.getFullYear()" x-text="y"></option>
                                    </template>
                                </select>
                                <span x-text="m.getFullYear()"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="captionLayout !== 'dropdown'">
                        <div class="text-sm font-medium" x-text="monthLabel(m)"></div>
                    </template>
                </div>

                <table role="grid" aria-multiselectable="false" :aria-label="monthLabel(m)" class="w-full border-collapse">
                    <thead aria-hidden="true">
                        <tr class="flex">
                            <?php if ($showWeekNumber): ?>
                                <th class="text-muted-foreground size-(--cell-size) text-[0.8rem] font-normal" scope="col"></th>
                            <?php endif; ?>
                            <template x-for="(wd, i) in weekdays" :key="i">
                                <th class="text-muted-foreground flex size-(--cell-size) flex-1 items-center justify-center text-[0.8rem] font-normal" scope="col" x-text="wd"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(week, wi) in weeksFor(m)" :key="fmt(week[0])">
                            <tr class="mt-1 flex w-full">
                                <?php if ($showWeekNumber): ?>
                                    <td class="text-muted-foreground flex size-(--cell-size) items-center justify-center text-[0.8rem]" x-text="weekNumber(week)"></td>
                                <?php endif; ?>
                                <template x-for="(day, di) in week" :key="fmt(day)">
                                    <td role="gridcell" :aria-selected="isSelected(day)" class="relative flex-1 p-0 text-center">
                                        <button
                                            type="button"
                                            @click="select(day)"
                                            @keydown="onDayKeydown($event, day)"
                                            @mouseenter="if (mode === 'range') hover = day"
                                            x-text="day.getDate()"
                                            :data-day="fmt(day)"
                                            :tabindex="isFocused(day) ? 0 : -1"
                                            :aria-label="dayLabel(day)"
                                            :aria-disabled="isDisabled(day)"
                                            :data-selected="(mode !== 'range' && isSelected(day)) ? true : null"
                                            :data-range-start="(mode === 'range' && rangeIs(day).start) ? true : null"
                                            :data-range-middle="(mode === 'range' && rangeIs(day).middle) ? true : null"
                                            :data-range-end="(mode === 'range' && rangeIs(day).end) ? true : null"
                                            :data-today="isToday(day) ? true : null"
                                            :data-outside="day.__outside ? true : null"
                                            :data-disabled="isDisabled(day) ? true : null"
                                            :data-out-of-range="(outOfRange === 'flag' && isOutOfRange(day)) ? true : null"
                                            :class="modifierClass(day)"
                                            class="<?= e($dayBtn) ?>"
                                        ></button>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</div>
